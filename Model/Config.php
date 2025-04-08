<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const SLUG_PREFIX_CONFIG_PATH = 'storyblok/general/slug_prefix';
    const HOME_SLUG_CONFIG_PATH = 'storyblok/home_page/home_slug';
    const DEFAULT_GROUP_CODE = 'storyblok/general/default_group_code';
    const DEFAULT_FOLDER_NAME = 'storyblok/general/default_folder_name';
    const DEFAULT_STORE_CODE = 'storyblok/general/default_store_code';
    const DEFAULT_STORE_NAME = 'storyblok/general/default_store_name';
    const STORE_FINDER_SLUG_CONFIG_PATH = 'mageworx_locations/product_page/store_locator_slug';
    const PRODUCT_LIST_SLUG_CONFIG_PATH = 'storyblok/general/product_list_slug';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    public function slugPrefix(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::SLUG_PREFIX_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function homeSlug(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::HOME_SLUG_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function storeFinderSlug(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::STORE_FINDER_SLUG_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDefaultGroupCode(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::DEFAULT_GROUP_CODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function defaultFolderName(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::DEFAULT_FOLDER_NAME,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDefaultStoreCode(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::DEFAULT_STORE_CODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDefaultStoreName(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::DEFAULT_STORE_NAME,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function productListSlug(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::PRODUCT_LIST_SLUG_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }
}
