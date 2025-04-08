<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use MediaLounge\Storyblok\Model\ItemProvider\Story;
use Storyblok\{Client, ClientFactory};

class Listing implements ArgumentInterface
{
    private Client $storyblokClient;

    public function __construct(
        ScopeConfigInterface           $scopeConfig,
        StoreManagerInterface          $storeManager,
        ClientFactory                  $storyblokClient
    )
    {
        $this->storyblokClient = $storyblokClient->create([
            'apiKey' => $scopeConfig->getValue(
                'storyblok/general/api_key',
                ScopeInterface::SCOPE_STORE,
                $storeManager->getStore()->getId()
            )
        ]);

        $this->storyblokClient->language(substr($scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORES,
            $storeManager->getStore()->getId()
        ), 0, 2));
    }

    public function listStoriesByFolder(?string $folderSlug): array
    {
        if (!$folderSlug) {
            $folderSlug = '';
        }

        $response = $this->getStoryblokStories($folderSlug);

        $stories = $response->getBody()['stories'];

        $totalPages = $response->getHeaders()['Total'][0] / Story::STORIES_PER_PAGE;
        $totalPages = ceil($totalPages);

        if ($totalPages > 1) {
            $paginatedStories = [];

            for ($page = 2; $page <= $totalPages; $page++) {
                $pageResponse = $this->getStoryblokStories($folderSlug, $page);
                $paginatedStories = $pageResponse->getBody()['stories'];
            }

            $stories = array_merge($stories, $paginatedStories);
        }

        return $stories;
    }

    private function getStoryblokStories(string $folderSlug, int $page = 1): \Storyblok\Client
    {
        return $this->storyblokClient->getStories([
            'page' => $page,
            'per_page' => Story::STORIES_PER_PAGE,
            'starts_with' => $folderSlug,
            'is_startpage' => false
        ]);
    }
}
