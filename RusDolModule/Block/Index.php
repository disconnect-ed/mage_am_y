<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Block;

use Amasty\RusDolModule\Model\Config\ConfigProvider;
use Magento\Framework\View\Element\Template;

class Index extends Template {
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        parent::__construct($context, $data);
    }

    public function getGreetingText(): string {
        return $this->configProvider->getGreetingText();
    }

    public function isShowQty(): bool {
        return (bool) $this->configProvider->qtyIsEnabled();
    }

    public function getQtyValue(): int {
        return (int) $this->configProvider->getQtyValue();
    }

    public function getText(string $text): string {
        return $text;
    }
}