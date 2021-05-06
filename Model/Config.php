<?php
/**
 * Copyright © Media Lounge. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MediaLounge\Storyblok\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Storyblok module configuration
 */
class Config implements ConfigInterface
{
    /**
     * Magento scope configuration
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Magento manager configuration
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Configuration constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Gets store ID
     *
     * @return string
     */
    private function getStoreId(): string
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Gets API key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_STORYBLOK_API,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get webhook secret
     *
     * @return string
     */
    public function getWebhookSecret(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_STORYBLOK_WEBHOOK,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
}
