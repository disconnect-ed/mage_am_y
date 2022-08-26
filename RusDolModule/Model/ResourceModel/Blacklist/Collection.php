<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Model\ResourceModel\Blacklist;

use Amasty\RusDolModule\Model\Blacklist;
use Amasty\RusDolModule\Model\ResourceModel\Blacklist as BlacklistResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            Blacklist::class,
            BlacklistResource::class
        );
    }
}