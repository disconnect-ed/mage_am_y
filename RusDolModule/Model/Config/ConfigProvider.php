<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    protected $pathPrefix = 'ruslan_config/';

    const GENERAL_GROUP = 'general/';
    const MODULE_IS_ENABLED = 'module_enabled';
    const GREETING_TEXT = 'greeting_text';
    const QTY_IS_ENABLED = 'qty_enabled';
    const QTY_VALUE = 'qty';

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        parent::__construct($scopeConfig);
    }


    protected function getValue(string $path, string $scope = 'store')
    {
        return $this->scopeConfig->getValue($this->pathPrefix . $path, $scope);
    }

    protected function isSetFlag(string $path, string $scope = 'store'): bool
    {
        return (bool) $this->scopeConfig->isSetFlag($this->pathPrefix . $path, $scope);
    }

    public function moduleIsEnable(): bool {
        return (bool) $this->isSetFlag(self::GENERAL_GROUP . self::MODULE_IS_ENABLED);
    }

    public function getGreetingText(): string {
        return (string) $this->getValue(self::GENERAL_GROUP . self::GREETING_TEXT);
    }

    public function qtyIsEnabled(): bool {
        return (bool) $this->isSetFlag(self::GENERAL_GROUP . self::QTY_IS_ENABLED);
    }

    public function getQtyValue(): int {
        return (int) $this->getValue(self::GENERAL_GROUP . self::QTY_VALUE);
    }
}