<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Block\Email;

use Magento\Framework\View\Element\Template;
use Amasty\RusDolModule\Model\Blacklist as BlacklistModel;
use Amasty\RusDolModule\Model\BlacklistRepository;

class Blacklist extends Template
{
    /**
     * @var BlacklistRepository
     */
    private $blacklistRepository;

    public function __construct(
        Template\Context $context,
        BlacklistRepository $blacklistRepository,
        array $data = []
    )
    {
        $this->blacklistRepository = $blacklistRepository;
        parent::__construct($context, $data);
    }

    public function getBlacklistProduct(): BlacklistModel {
        $blacklistSku = $this->getData('sku');
        return $this->blacklistRepository->getBySku($blacklistSku);
    }
}