<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\Block\Widget;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\FileSystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;
use MediaLounge\Storyblok\Block\Container;
use MediaLounge\Storyblok\Model\Config;
use Storyblok\ClientFactory as StoryblokClientFactory;

class Storyblok extends Container implements BlockInterface
{
    public function __construct(
        FileSystem $viewFileSystem,
        StoryblokClientFactory $storyblokClient,
        ScopeConfigInterface $scopeConfig,
        Context $context,
        private readonly StoreManagerInterface $storeManager,
        private readonly Config $config,
        array $data = []
    ) {
        parent::__construct($viewFileSystem, $storyblokClient, $scopeConfig, $context, $data);
    }

    public function getSlug(): string
    {
        $slug = $this->getData('slug');

        if (!$slug) {
            $uriParts = explode('/', trim(explode('?', $this->getRequest()->getRequestUri() ?? '')[0], '/'));
            $slug = array_pop($uriParts);
            if ($this->config->useStoreAsSlug()) {
                $slug = $this->storeManager->getStore()->getCode() . '/' . $slug;
            }
        }

        return trim($slug, '/');
    }
}
