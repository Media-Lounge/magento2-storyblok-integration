<?php

namespace MediaLounge\Storyblok\Plugin;

use Magento\Store\Api\Data\StoreInterface;

class RewriteUrl
{
    /**
     * Removes the target store's base URL from the redirect URL before switching.
     *
     * @param \Magento\UrlRewrite\Model\StoreSwitcher\RewriteUrl $subject
     * @param StoreInterface $targetStore
     * @param string $redirectUrl
     * @return array
     */
    public function beforeSwitch(\Magento\UrlRewrite\Model\StoreSwitcher\RewriteUrl $subject, StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): array
    {
        $baseUrl = $targetStore->getBaseUrl();
        if (strpos($redirectUrl, $baseUrl) === 0) {
            $redirectUrl = substr($redirectUrl, strlen($baseUrl));
        }
        return [$fromStore, $targetStore, $redirectUrl];
    }
}
