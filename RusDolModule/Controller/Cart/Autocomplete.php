<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Amasty\RusDolModule\Model\Config\ConfigProvider;

class Autocomplete extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var ImageBuilder
     */
    private $imageBuilder;
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    const SEARCH_PARAM = 'sku';

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        ImageBuilder $imageBuilder,
        ConfigProvider $configProvider
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
        $this->imageBuilder = $imageBuilder;
        $this->configProvider = $configProvider;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        if (!$this->configProvider->moduleIsEnable()) {
            return $result->setData(['message' => 'Module disabled'])
                ->setHttpResponseCode(500);
        }
        $sku = (string)$this->getRequest()->getParam(self::SEARCH_PARAM);
        if ($sku) {
            $collection = $this->collectionFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter(self::SEARCH_PARAM, ['like' => '%' . $sku . '%'])
                ->setPageSize(5)
                ->setCurPage(1);

            if ($collection->getSize() <= 0) {
                return $result->setData(['message' => 'Not Found'])
                    ->setHttpResponseCode(404);
            }

            $productList = [];
            $i = 0;

            foreach ($collection as $product) {
                $productList[$i]['name'] = $product->getName();
                $productList[$i]['sku'] = $product->getSku();
                $productList[$i]['price'] = $product->getFinalPrice();
                $productList[$i]['img'] = $this->getImage($product)->getImageUrl();
                $i++;
            }
            
            return $result->setData($productList);
        }
        return $result->setData(['message' => 'Missing parameter: ' . self::SEARCH_PARAM])
            ->setHttpResponseCode(418);
    }

    protected function getImage($product, $imageId = 'product_thumbnail_image')
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->create();
    }
}