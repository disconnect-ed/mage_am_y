<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Controller\Cart;

use Amasty\RusDolModule\Model\BlacklistRepository;
use Amasty\RusDolModule\Model\Config\ConfigProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\NoSuchEntityException;

class Add extends Action
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var BlacklistRepository
     */
    protected $blacklistRepository;

    const REDIRECT_PATH = 'ruslan/index/index';

    public function __construct(
        Context $context,
        Session $session,
        ProductRepositoryInterface $productRepository,
        ConfigProvider $configProvider,
        EventManager $eventManager,
        BlacklistRepository $blacklistRepository
    )
    {
        parent::__construct($context);
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->configProvider = $configProvider;
        $this->eventManager = $eventManager;
        $this->blacklistRepository = $blacklistRepository;
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        //если модуль выключен, то не получится обработать get запрос
        if (!$this->configProvider->moduleIsEnable()) {
            return $redirect->setPath(self::REDIRECT_PATH);
        } elseif (!$this->configProvider->qtyIsEnabled()) {
            $this->messageManager->addWarningMessage('Поле qty отключено, невозможно добавить товар в корзину.');
            return $redirect->setPath(self::REDIRECT_PATH);
        }
        $sku = (string)$this->getParam('sku');
        $qty = (int)$this->getParam('qty');
        if (!$sku || !$qty) {
            return $redirect->setPath(self::REDIRECT_PATH);
        }
        $product = $this->getProduct($sku);
        if ($product && $this->validateProduct($product, $qty)) {
            $quote = $this->getQuote();
            $blacklistProduct = $this->blacklistRepository->getBySku($sku);
            if ($blacklistProduct->getSku()) {
                $this->addBlacklistProduct($product, $blacklistProduct, $quote, $qty);
            } else {
                $this->addToCart($quote, $product, $qty);
            }
        }
        return $redirect->setPath(self::REDIRECT_PATH);
    }

    protected function getParam($param = null)
    {
        if ($param) {
            $paramValue = $this->getRequest()->getParam($param);
            if (!$paramValue) {
                $this->messageManager->addErrorMessage("Некорректное значение для поля $param!");
                return null;
            }
            return $paramValue;
        }
        return false;
    }

    protected function getQuote()
    {
        $quote = $this->session->getQuote();
        if (!$quote->getId()) {
            $quote->save();
        }
        return $quote;
    }

    protected function getProduct($param)
    {
        try {
            $product = $this->productRepository->get($param);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage('Продукт не найден!');
            return false;
        }
        return $product;
    }

    protected function validateProduct($product, int $qty): bool
    {
        $isValid = true;
        if ($product->getTypeId() !== 'simple') {
            $this->messageManager->addErrorMessage('Доступный тип продукта: simple.');
            $isValid = false;
        } elseif ($qty <= 0) {
            $this->messageManager->addErrorMessage("Невозможно добавить товар в количестве $qty!");
            $isValid = false;
        } elseif ($qty > $product->getExtensionAttributes()->getStockItem()->getQty()) {
            $this->messageManager->addErrorMessage('Слишком большой параметр Qty!');
            $isValid = false;
        }
        return $isValid;
    }

    protected function addToCart($quote, $product, int $qty): void
    {
        $quote->addProduct($product, $qty);
        $quote->save();
        $this->eventManager->dispatch(
            'amasty_rusdolmodule_add_promo',
            ['productSku' => $product->getSku()]
        );
        $this->messageManager->addSuccessMessage("Товар добавлен в корзину!");
    }

    protected function addBlacklistProduct($product, $blacklistProduct, $quote, $qty): void
    {
        $qtyInCart = $this->getQtyInCart($quote, $product->getSku());
        $blacklistQty = (int)$blacklistProduct->getQty();
        $allowedQty = $blacklistQty - $qtyInCart;
        if ($allowedQty <= 0) {
            $this->messageManager
                ->addErrorMessage('Вы не можете добавить больше товаров с таким SKU!');
        } elseif ($allowedQty >= $qty) {
            $this->addToCart($quote, $product, $qty);
        } else {
            $this->messageManager
                ->addWarningMessage("Вы пытались добавить $qty товаров, добавлено $allowedQty!");
            $this->addToCart($quote, $product, $allowedQty);
        }

    }

    protected function getQtyInCart($quote, string $sku): int
    {
        $productsInCart = $quote->getAllVisibleItems();
        $productQtyInCart = 0;
        foreach ($productsInCart as $product) {
            if ($product->getSku() === $sku) {
                $productQtyInCart = $product->getQty();
                break;
            }
        }
        return (int)$productQtyInCart;
    }
}