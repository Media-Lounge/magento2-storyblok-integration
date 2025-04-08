<?php

namespace MediaLounge\Storyblok\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface as LocalResolverInterface;
use Magento\Store\Model\{ScopeInterface, StoreManagerInterface};
use Storyblok\{Client, ClientFactory};

trait ClientTrait
{
    private Client $storyblokClient;
    private string $dimension;

    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface  $scopeConfig,
        private readonly LocalResolverInterface $localeResolver,
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
}
