<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const SLUG_PREFIX_CONFIG_PATH = 'storyblok/general/slug_prefix';
    const HOME_SLUG_CONFIG_PATH = 'storyblok/home_page/home_slug';

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
}
