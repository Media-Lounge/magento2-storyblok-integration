<?php
namespace MediaLounge\Storyblok\Model\ItemProvider;

use Storyblok\ClientFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ConfigReaderInterface $configReader,
        SitemapItemInterfaceFactory $itemFactory,
        ScopeConfigInterface $scopeConfig,
        ClientFactory $storyblokClient,
        StoreManagerInterface $storeManager
    ) {
        $this->itemFactory = $itemFactory;
        $this->configReader = $configReader;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->storyblokClient = $storyblokClient->create([
            'apiKey' => $scopeConfig->getValue(
                'storyblok/general/api_key',
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            )
        ]);
        
        // Todo: add config field to disable/ignore localization
        $this->storyblokClient->language(substr($scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORES,
            $storeManager->getStore()->getId()
        ), 0, 2));
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

        $store = $this->storeManager->getStore($storeId);
        $storeUrlParts = explode('/', trim($store->getBaseUrl(), '/'));
        $storeUrlCode = end($storeUrlParts);

        $items = array_map(function ($item) use ($storeId, $storeUrlCode) {
            $slug = $item['full_slug'];
            $slugParts = explode('/', trim($slug, '/'));
            if ($slugParts[0] === $storeUrlCode) {
                unset($slugParts[0]);
                $slug = implode('/', $slugParts);
            }

            return $this->itemFactory->create([
                'url' => $slug,
                'updatedAt' => $item['published_at'],
                'priority' => $this->configReader->getPriority($storeId),
                'changeFrequency' => $this->configReader->getChangeFrequency($storeId)
            ]);
        }, $stories);

        return $items;
    }

    private function getStories(int $page = 1): \Storyblok\Client
    {
        $response = $this->storyblokClient->getStories([
            'page' => $page,
            'per_page' => self::STORIES_PER_PAGE,
            'filter_query[component][like]' => 'page'
        ]);

        return $response;
    }
}
