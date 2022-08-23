<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Controller\Cart;

use Amasty\RusDolModule\Model\Config\ConfigProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Event\ManagerInterface as EventManager;

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

    public function __construct(
        Context $context,
        Session $session,
        ProductRepositoryInterface $productRepository,
        ConfigProvider $configProvider,
        EventManager $eventManager
    )
    {
        parent::__construct($context);
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->configProvider = $configProvider;
        $this->eventManager = $eventManager;
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        //если модуль выключен, то не получится обработать get запрос
        if (!$this->configProvider->moduleIsEnable()) {
            return $redirect->setPath("ruslan/index/index");
        } elseif (!$this->configProvider->qtyIsEnabled()) {
            $this->messageManager->addWarningMessage('Поле qty отключено, невозможно добавить товар в корзину.');
            return $redirect->setPath("ruslan/index/index");
        }
        $quote = $this->getQuote();
        $sku = $this->getParam('sku');
        $qty = (int)$this->getParam('qty');
        if (!$sku || !$qty) {
            return $redirect->setPath("ruslan/index/index");
        }
        $product = $this->getProduct($sku);
        if (!$product) {
            return $redirect->setPath("ruslan/index/index");
        }
        $validate = $this->validateProduct($product, $qty);
        if ($validate) {
            $this->addToCart($quote, $product, $qty);
        }
        $this->eventManager->dispatch(
            'amasty_rusdolmodule_add_promo',
            ['productSku' => $sku]
        );
        return $redirect->setPath("ruslan/index/index");
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

    protected function validateProduct($product, int $qty)
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

    protected function addToCart($quote, $product, $qty)
    {
        $quote->addProduct($product, $qty);
        $quote->save();
        $this->messageManager->addSuccessMessage("Товар добавлен в корзину!");
    }
}