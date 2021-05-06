<?php
/**
 * Copyright © Media Lounge. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MediaLounge\Storyblok\Model;

/**
 * Storyblok module configuration inteface
 */
interface ConfigInterface
{
    /**
     * @var string
     */
    public const CONFIG_STORYBLOK_API = 'storyblok/general/api_key';

    /**
     * @var string
     */
    public const CONFIG_STORYBLOK_WEBHOOK = 'storyblok/general/webhook_secret';

    /**
     * Gets API key
     *
     * @return string
     */
    public function getApiKey(): string;

    /**
     * Get webhook secret
     *
     * @return string
     */
    public function getWebhookSecret(): string;
}
