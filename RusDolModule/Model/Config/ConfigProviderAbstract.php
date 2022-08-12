<?php
declare(strict_types=1);

namespace Amasty\RusDolModule\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

abstract class ConfigProviderAbstract
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var String
     */
    protected $pathPrefix;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    abstract protected function getValue(string $path, string $scope);

    abstract protected function isSetFlag(string $path, string $scope): bool;

}