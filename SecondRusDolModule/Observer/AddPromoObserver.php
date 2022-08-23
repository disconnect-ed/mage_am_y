<?php
declare(strict_types=1);

namespace Amasty\SecondRusDolModule\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;

class AddPromoObserver implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Session $session,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    public function execute(Observer $observer)
    {
        $promoSku = trim((string)$this->scopeConfig->getValue('second_ruslan_config/general/promo_sku'));
        $forSku = (string)$this->scopeConfig->getValue('second_ruslan_config/general/for_sku');
        if ($promoSku && $forSku) {
            $forSkuArr = array_map('trim', explode(",", $forSku));
            $observerSku = $observer->getData('productSku');
            if (in_array($observerSku, $forSkuArr)) {
                $product = $this->productRepository->get($promoSku);
                if ($product->getTypeId() === 'simple') {
                    $quote = $this->getQuote();
                    $quote->addProduct($product, 1);
                    $quote->save();
                }
            }
        }
    }

    protected function getQuote () {
        $quote = $this->session->getQuote();
        if(!$quote->getId()) {
            $quote->save();
        }
        return $quote;
    }
}