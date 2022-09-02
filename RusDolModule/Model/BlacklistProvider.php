<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Model;

use Amasty\RusDolModule\Model\ResourceModel\Blacklist\CollectionFactory as BlacklistCollectionFactory;

class BlacklistProvider
{
    /**
     * @var BlacklistCollectionFactory
     */
    private $blacklistCollectionFactory;

    public function __construct(
        BlacklistCollectionFactory $blacklistCollectionFactory
    )
    {
        $this->blacklistCollectionFactory = $blacklistCollectionFactory;
    }

    public function getFirstBlacklistProduct()
    {
        $blacklistCollection = $this->blacklistCollectionFactory->create();
        return $blacklistCollection->getFirstItem();
    }
}