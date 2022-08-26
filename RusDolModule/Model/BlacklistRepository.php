<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Model;

use Amasty\RusDolModule\Model\BlacklistFactory;
use Amasty\RusDolModule\Model\ResourceModel\Blacklist as BlacklistResource;

class BlacklistRepository
{
    /**
     * @var BlacklistFactory
     */
    protected $blacklistFactory;

    /**
     * @var BlacklistResource
     */
    protected $blacklistResource;

    public function __construct(
        BlacklistFactory $blacklistFactory,
        BlacklistResource $blacklistResource
    )
    {
        $this->blacklistFactory = $blacklistFactory;
        $this->blacklistResource = $blacklistResource;
    }

    public function getBySku(string $sku): Blacklist
    {
        $blacklist = $this->blacklistFactory->create();
        $this->blacklistResource->load($blacklist, $sku, 'sku');
        return $blacklist;
    }
}