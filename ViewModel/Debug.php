<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class Debug implements ArgumentInterface
{
    const DEBUG_CONFIG_PATH = 'storyblok/general/data_debug';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    public function isDataDebugEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::DEBUG_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }
}
