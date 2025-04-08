<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\Plugin;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Breadcrumbs;
use MediaLounge\Storyblok\Model\Config;

class StoryblokBreadcrumbs
{
    private const EXCLUDED_SEGMENTS = ['newsletter', 'customer'];
    private const BREAK_SEGMENTS = ['referer'];

    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly Config $config
    ) {}

    public function afterToHtml(
        Breadcrumbs $original,
        $html
    ): string
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $path = trim($original->getRequest()->getPathInfo(), '/');
        $segments = explode('/', $path);

        if (empty(trim($html))  && !empty($path)) {

            $crumb = [
                'label' => __('Home'),
                'link' => $baseUrl,
                'first' => true,
                'last' => false,
                'readonly' => false,
            ];
            $original->addCrumb('home', $crumb);

            if (str_starts_with($path, $this->config->slugPrefix())) {
                array_shift($segments);
            }

            $uri = '';
            foreach ($segments as $segment) {
                $crumb['first'] = false;
                $crumb['last'] = false;
                $crumb['readonly'] = false;

                if ($segment === end($segments)) {
                    $crumb['last'] = true;
                    $crumb['readonly'] = true;
                }

                $uri .= (empty($uri) ? '' : '/') . $segment;
                $crumb['link'] = $baseUrl . $uri;
                $crumb['label'] = str_replace('-', ' ', ucfirst($segment));

                // Remove segment from breadcrumbs
                if (in_array($segment, self::EXCLUDED_SEGMENTS)) {
                    continue;
                }

                // Remove segment and any following segments from breadcrumbs
                if (in_array($segment, self::BREAK_SEGMENTS)) {
                    break;
                }

                $original->addCrumb($segment, $crumb);
            }

            $html = $original->toHtml();
        }

        return $html;
    }
}
