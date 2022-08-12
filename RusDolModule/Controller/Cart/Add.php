<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Controller\Cart;

use Amasty\RusDolModule\Model\Config\ConfigProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
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

    public function __construct(
        Context $context,
        Session $session,
        ProductRepositoryInterface $productRepository,
        ConfigProvider $configProvider
    )
    {
        parent::__construct($context);
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->configProvider = $configProvider;
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
        $product = $this->getProduct($sku);
        $validate = $this->productValidation($product, $qty);
        if ($validate) {
            $this->addToCart($quote, $product, $qty);
        }
        return $redirect->setPath("ruslan/index/index");
    }

    protected function getParam($param = null)
    {
        if ($param) {
            $paramValue = $this->getRequest()->getParam($param);
            if (!$paramValue && $paramValue != 0) {
                $this->messageManager->addErrorMessage("Параметр под названием $param не найден!");
                return null;
            }
            return $paramValue;
        }
        return $param;
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
            if (!$param) return false;
            $product = $this->productRepository->get($param);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addWarningMessage($e->getMessage());
            return false;
        }
        return $product;
    }

    protected function productValidation($product, int $qty)
    {
        if (!$product) {
            $this->messageManager->addErrorMessage('Продукт не найден!');
            return false;
        } elseif ($product->getTypeId() !== 'simple') {
            $this->messageManager->addErrorMessage('Доступный тип продукта: simple.');
            return false;
        } elseif ($qty <= 0) {
            $this->messageManager->addErrorMessage("Невозможно добавить товар в количестве $qty!");
            return false;
        } elseif ($qty > $product->getExtensionAttributes()->getStockItem()->getQty()) {
            $this->messageManager->addErrorMessage('Слишком большой параметр Qty!');
            return false;
        } else {
            return true;
        }
    }

    protected function addToCart($quote, $product, $qty)
    {
        $quote->addProduct($product, $qty);
        $quote->save();
        $this->messageManager->addSuccessMessage("Товар добавлен в корзину!");
    }
}