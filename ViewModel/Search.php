<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use MediaLounge\Storyblok\Model\Config;
use Storyblok\Client;
use Storyblok\ClientFactory;
use Psr\Log\LoggerInterface;

class Search implements ArgumentInterface
{
    private Client $storyblokClient;

    public function __construct(
        private readonly Http $request,
        private readonly LoggerInterface $logger,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface  $scopeConfig,
        private readonly Config $config,
        ClientFactory $storyblokClient
    ) {
        $this->storyblokClient = $storyblokClient->create([
            'apiKey' => $this->scopeConfig->getValue(
                'storyblok/general/api_key',
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            )
        ]);

        $this->storyblokClient->language(substr($this->scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORES,
            $this->storeManager->getStore()->getId()
        ), 0, 2));
    }

    public function getSearchResults(): array
    {
        $searchTerm = $this->request->getParam('q');
        if (!$searchTerm) {
            return [];
        }

        try {
            $prefix = $this->config->slugPrefix();
            $productListSlug = $this->config->productListSlug();

            $options = [
                'page' => $this->request->getParam('page', 1),
                'per_page' => 50,
                'search_term' => $searchTerm,
                'starts_with' => $prefix
            ];

            if ($productListSlug) {
                $options['excluding_slugs'] = "*" . $productListSlug . "*";
            }

            $stories = $this->storyblokClient->get(
                'stories',
                array_merge($options, $this->storyblokClient->getApiParameters())
            );

            if (!empty($stories->getBody()['stories'])) {
                return array_values(
                    $stories->getBody()['stories'],
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return [];
    }
}
