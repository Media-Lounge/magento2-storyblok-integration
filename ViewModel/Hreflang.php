<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use MediaLounge\Storyblok\Model\Config;
use Storyblok\Client;
use Storyblok\ClientFactory;

class Hreflang implements ArgumentInterface
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
     */
    public function getHreflangs(): string
    {
        $html = '';
        try {
            $slugPrefix = $this->config->slugPrefix();
            $slug = $slugPrefix . '/' . ltrim($this->request->getOriginalPathInfo(), '/');
            $story = $this->storyblokClient->getStoryBySlug($slug);
            $html .= '<link rel="'. ($slugPrefix === 'nl-nl' ? 'canonical' : 'alternate') . '" hreflang="' . $slugPrefix . '" href="' . $this->storeManager->getStore()->getBaseUrl() . ltrim($this->request->getOriginalPathInfo(), '/') . '" />';
            $html .= $this->getStoryBlokTargetRedirectUrl($story);
        } catch (\Throwable $e) {}

        return $html;
    }

    private function getStoryBlokTargetRedirectUrl(Client $story): string
    {
        $html = '';
        $story = $story->getBody()['story'];
        $hreflangs = array_key_exists('hreflangs', $story['content']) ? $story['content']['hreflangs'] : [];
        foreach ($hreflangs as $relatedSlug) {
            try {
                $relatedStory = $this->storyblokClient->getStoryByUuid($relatedSlug);
            } catch (\Exception $exception) {
                continue;
            }

            if (
                $story !== $relatedStory->getBody()['story'] &&
                $relatedStory->getBody()['story']['published_at'] &&
                strtotime($relatedStory->getBody()['story']['published_at']) < strtotime('now')
            ) {
                /** @phpstan-ignore-next-line */
                $url = 'https://' . $this->request->getServer()->get('HTTP_HOST') . '/' . $relatedStory->getBody()['story']['full_slug'];

                $isCanonical = str_starts_with($relatedStory->getBody()['story']['full_slug'], 'nl-nl');
                $lang = explode('/', $relatedStory->getBody()['story']['full_slug'])[0] ?? 'x-default';

                $html .= '<link rel="' . ($isCanonical ? 'canonical' : 'alternate') . '" hreflang="' . $lang . '" href="' . $url . '" />';
            }
        }

        if (empty($html)) {
            throw new \Exception('No related stories found');
        }

        return $html;
    }
}
