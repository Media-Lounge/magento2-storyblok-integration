<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MediaLounge\Storyblok\ViewModel;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use MediaLounge\Storyblok\Model\Config;
use Storyblok\Client;
use Storyblok\ClientFactory;

/**
 * Provides target store redirect url.
 */
class SwitcherUrlProvider implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    private RequestInterface $request;

    private EncoderInterface $encoder;

    private ScopeConfigInterface $scopeConfig;

    private StoreManagerInterface $storeManager;

    private UrlInterface $urlBuilder;

    private Client $storyblokClient;

    private Config $config;

    /**
     * @param EncoderInterface $encoder
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        RequestInterface      $request,
        EncoderInterface      $encoder,
        StoreManagerInterface $storeManager,
        UrlInterface          $urlBuilder,
        ScopeConfigInterface  $scopeConfig,
        Config                $config,
        ClientFactory         $storyblokClient
    )
    {
        $this->request = $request;
        $this->encoder = $encoder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;

        $this->storyblokClient = $storyblokClient->create([
            'apiKey' => $scopeConfig->getValue(
                'storyblok/general/api_key',
                ScopeInterface::SCOPE_STORE,
                $storeManager->getStore()->getId()
            )
        ]);
    }

    /**
     * Returns target store redirect url.
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTargetStoreRedirectUrl(): string
    {
        try {
            $slugPrefix = $this->config->slugPrefix();
            $slug = $slugPrefix . '/' . ltrim($this->request->getOriginalPathInfo(), '/');
            return $this->getStoryBlokSwitchUrls($slug);
        } catch (\Throwable $e) {
            return $this->getMagentoSwitchUrls();
        }
    }

    private function getStoryBlokSwitchUrls(string $slug): string
    {
        try {
            $story = $this->storyblokClient->getStoryBySlug($slug);
        } catch (\Exception $exception){
            throw new \Exception('No story found');
        }

        $story = $story->getBody()['story'];
        $hreflangs = array_key_exists('hreflangs', $story['content']) ? $story['content']['hreflangs'] : [];
        $currentStore = $this->storeManager->getStore();
        $stores = $this->storeManager->getStores();

        $html = '';
        foreach ($hreflangs as $relatedSlug) {
            try {
                $relatedStory = $this->storyblokClient->getStoryByUuid($relatedSlug);
            } catch (\Exception $exception){
                continue;
            }

            $this->generateSwitchItem($html, $relatedStory, $story, $currentStore, $stores);
        }

        if (empty($html)) {
            throw new \Exception('No related stories found');
        }

        return $html;
    }
    private function getMagentoSwitchUrls(): string
    {
        $html = '';
        $currentWebsiteId = $this->storeManager->getStore()->getWebsiteId();
        foreach ($this->storeManager->getStores() as $store ) {
            if (
                !$store->isActive() ||
                $store->getId() == $this->storeManager->getStore()->getId() ||
                $currentWebsiteId !== $store->getWebsiteId()
            ) {
                continue;
            }

            $storeName = $store->getName();
            $url = $this->encoder->encode($store->getCurrentUrl());
            $url = $this->urlBuilder->getUrl(
                'stores/store/redirect',
                [
                    '___store' => $store->getCode(),
                    '___from_store' => $this->storeManager->getStore()->getCode(),
                    ActionInterface::PARAM_NAME_URL_ENCODED => $url,
                ]
            );
            $html .= $this->generateSwitchButton($url, $storeName);
        }

        return $html;
    }

    private function generateSwitchItem(string &$html, Client $relatedStory, array $story, $currentStore, $stores): void
    {
        if ($this->relatedStoryExists($relatedStory, $story)) {
            $slug = $this->generateStoryblokSlug($relatedStory);
            /** @phpstan-ignore-next-line */
            $url = 'https://' . $this->request->getServer()->get('HTTP_HOST') . '/' . $slug . (str_ends_with($slug, '/') ? '' : '/');
            foreach ($stores as $store) {
                if (
                    str_starts_with($url, $store->getBaseUrl()) &&
                    $store->getWebsiteId() === $currentStore->getWebsiteId()
                ) {
                    $storeName = $store->getName();
                    $html .= $this->generateSwitchButton($url, $storeName);
                }
            }
        }
    }

    private function generateSwitchButton(string $url, string $storeName): string
    {
        return '<a href="' . $url . '" class="block px-4 py-2 rounded-md lg:px-5 lg:py-2 hover:bg-tertiary hover:underline underline-offset-2">
                        ' . $storeName . '
                    </a>';
    }

    private function generateStoryblokSlug(Client $relatedStory): string
    {
        $isDefault = $this->storeManager->getGroup()->getCode() === $this->config->getDefaultGroupCode();
        $country = $this->getCountryFromSlugPrefix();

        return !$isDefault &&
        str_starts_with($relatedStory->getBody()['story']['full_slug'], $this->config->defaultFolderName() . '/') ?
            str_replace($this->config->defaultFolderName() .'/', $this->config->defaultFolderName() .'-' . $country, $relatedStory->getBody()['story']['full_slug']) :
            $relatedStory->getBody()['story']['full_slug'];
    }

    private function relatedStoryExists(Client $relatedStory, array $story): bool
    {
        return $relatedStory->getBody()['story']['published_at'] &&
        strtotime($relatedStory->getBody()['story']['published_at']) < strtotime('now') &&
        $story['full_slug'] !== $relatedStory->getBody()['story']['full_slug'];
    }

    private function getCountryFromSlugPrefix(): string
    {
        $slugPrefix = $this->config->slugPrefix();
        return str_contains($slugPrefix, '-') ? explode('-', $slugPrefix)[1] : $slugPrefix;
    }
}
