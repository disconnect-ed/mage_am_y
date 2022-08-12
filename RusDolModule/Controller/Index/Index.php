<?php

namespace Amasty\RusDolModule\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Amasty\RusDolModule\Model\Config\ConfigProvider;

class Index extends Action
{

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        ConfigProvider $configProvider
    )
    {
        $this->configProvider = $configProvider;
        parent::__construct($context);
    }

    public function execute() {
        if ($this->configProvider->moduleIsEnable()) {
            return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        } else {
            die('Sorry, dont worry!');
        };

    }

}