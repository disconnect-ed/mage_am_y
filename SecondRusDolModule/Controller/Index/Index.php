<?php
declare(strict_types=1);

namespace Amasty\SecondRusDolModule\Controller\Index;

use Amasty\RusDolModule\Controller\Index\Index as BasicIndex;
use Amasty\RusDolModule\Model\Config\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;

class Index extends BasicIndex
{
    /**
     * @var Session
     */
    protected $customerSession;

    public function __construct(
        Context $context,
        ConfigProvider $configProvider,
        Session $customerSession
    )
    {
        parent::__construct($context, $configProvider);
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            return parent::execute();
        } else {
            die('Site login required!');
        }
    }
}