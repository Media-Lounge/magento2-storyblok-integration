<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use MediaLounge\Storyblok\Model\Config;
use Storyblok\Client;
use Storyblok\ClientFactory;

class Content implements ArgumentInterface
{
    private Client $storyblokClient;

    private string $dimension;

    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Config $config,
        ClientFactory $storyblokClient
    ) {
        $this->storyblokClient = $storyblokClient->create([
            'apiKey' => $this->scopeConfig->getValue(
                'storyblok/general/api_key',
                ScopeInterface::SCOPE_STORE,
                $storeManager->getStore()->getId()
            )
        ]);

        $this->dimension = substr($this->scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORES,
            $storeManager->getStore()->getId()
        ), 0, 2);

        $this->storyblokClient->language($this->dimension);
    }

    public function getFormattedLink($link): string
    {
        if (is_string($link)) {
            $link = !str_starts_with($link, '/')  && !str_starts_with($link, 'http') ? '/' . $link : $link;
        }

        if (is_array($link)) {
            $link = $link['cached_url'] ?? '';
            $link = !str_starts_with($link, 'http') && !str_starts_with($link, '/') ?
                '/' . $link :
                $link;
        }

        if (
            $this->storeManager->getStore()->getCode() !== $this->config->getDefaultStoreCode() &&
            str_starts_with($link, '/' . $this->config->getDefaultStoreName() .'/')
        ) {
            $link = str_replace('/' . $this->config->getDefaultStoreName() .'/', $this->storeManager->getStore()->getBaseUrl(), $link);
        }

        if (str_contains($link, '@')) {
            $link = 'mailto:' . $link;
        }

        return $link;
    }

    public function getFormattedCustomUrl($content): string
    {
        if (str_starts_with($content, 'http')) {
            return $content;
        }

        return $this->storeManager->getStore()->getBaseUrl() . $content;
    }

    public function getPhoneLink(string $link): string
    {
        if (!str_contains($link, 'tel:')) {
            $link = 'tel:' . $link;
        }

        return $link;
    }

    public function getAssetLink(mixed $asset): string
    {
        if (is_array($asset) && !empty($asset['filename'])) {
            return (string)$asset['filename'];
        }

        return '';
    }

    public function getTranslatedLabel(string $tag, string $attributeSlug = 'tags'): string
    {
        $label = '';
        $response = $this->storyblokClient->get(
            'datasource_entries/',
            [
                'datasource' => $attributeSlug,
                'dimension' => $this->dimension,
                ...$this->storyblokClient->getApiParameters()
            ]
        );

        if (!$response) {
            return $label;
        }

        foreach ($response->getBody()['datasource_entries'] as $datasource) {
            if (strtolower($datasource['name']) === strtolower($tag)) {
                $label = $datasource['dimension_value'] ?? $datasource['value'] ?? '';
            }
        }

        return $label;
    }
}
