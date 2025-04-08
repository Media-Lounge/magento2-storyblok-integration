<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface as LocalResolverInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Storyblok\{ApiException, Client, ClientFactory};

class Stories implements ArgumentInterface
{
    private Client $storyblokClient;

    public function __construct(
        ScopeConfigInterface                    $scopeConfig,
        StoreManagerInterface                   $storeManager,
        ClientFactory                           $storyblokClient,
        private readonly LocalResolverInterface $localeResolver
    )
    {
        $this->storyblokClient = $storyblokClient->create([
            'apiKey' => $scopeConfig->getValue(
                'storyblok/general/api_key',
                ScopeInterface::SCOPE_STORE,
                $storeManager->getStore()->getId()
            )
        ]);
    }

    public function getLocaleJs()
    {
        return str_replace("_", "-", $this->localeResolver->getLocale());
    }

    public function getStoryByUuid(?string $uuid): ?array
    {
        if (!$uuid) {
            $uuid = '';
        }

        try {

            $response = $this->getStoryblokStories($uuid);

            return $response->getBody()['story'] ?? [];
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * @throws ApiException
     */
    private function getStoryblokStories(string $uuid): \Storyblok\Client
    {
        return $this->storyblokClient->getStoryByUuid($uuid);
    }
}
