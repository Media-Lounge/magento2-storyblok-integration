<?php
/**
 * Copyright © Media Lounge. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MediaLounge\Storyblok\Model\ItemProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;

use Storyblok\ClientFactory;

use MediaLounge\Storyblok\Model\ConfigInterface;


class Story implements ItemProviderInterface
{
    const STORIES_PER_PAGE = 100;

    /**
     * @var SitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * @var \Storyblok\Client
     */
    private $storyblokClient;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigInterface
     */
    private $storyblokConfig;

    public function __construct(
        ConfigReaderInterface $configReader,
        SitemapItemInterfaceFactory $itemFactory,
        ScopeConfigInterface $scopeConfig,
        ClientFactory $storyblokClient,
        ConfigInterface $storyblokConfig
    ) {
        $this->itemFactory = $itemFactory;
        $this->configReader = $configReader;
        $this->scopeConfig = $scopeConfig;
        $this->storyblokConfig = $storyblokConfig;
        $this->storyblokClient = $storyblokClient->create([
            'apiKey' => $this->storyblokConfig->getApiKey(),
        ]);
    }

    public function getItems($storeId)
    {
        $response = $this->getStories();
        $stories = $response->getBody()['stories'];

        $totalPages = $response->getHeaders()['Total'][0] / self::STORIES_PER_PAGE;
        $totalPages = ceil($totalPages);

        if ($totalPages > 1) {
            $paginatedStories = [];

            for ($page = 2; $page <= $totalPages; $page++) {
                $pageResponse = $this->getStories($page);
                $paginatedStories = $pageResponse->getBody()['stories'];
            }

            $stories = array_merge($stories, $paginatedStories);
        }

        $items = array_map(function ($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item['full_slug'],
                'updatedAt' => $item['published_at'],
                'priority' => $this->configReader->getPriority($storeId),
                'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
            ]);
        }, $stories);

        return $items;
    }

    private function getStories(int $page = 1): \Storyblok\Client
    {
        $response = $this->storyblokClient->getStories([
            'page' => $page,
            'per_page' => self::STORIES_PER_PAGE,
            'filter_query[component][like]' => 'page',
        ]);

        return $response;
    }
}
