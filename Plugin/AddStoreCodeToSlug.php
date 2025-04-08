<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\Plugin;

use MediaLounge\Storyblok\Model\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use MediaLounge\Storyblok\Controller\Router;

class AddStoreCodeToSlug
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly Config $config,
    ) {}

    /**
     * @see Router::match()
     * @return RequestInterface[]
     */
    public function beforeMatch(Router $subject, RequestInterface $request): array
    {
        if ($this->config->useStoreAsSlug()) {
            $storeCode = $_SERVER['MAPPED_STORE_CODE'] ?? $this->storeManager->getStore()->getCode();
            $slugPrefix = trim($this->config->getSlugPrefix()) ?: $storeCode;
            $identifier = trim($request->getPathInfo(), '/');

            if (!str_starts_with($identifier, $slugPrefix . '/')) {
                $identifier = $slugPrefix . '/' . $identifier;
                $request->setPathInfo($identifier);
            }
        }

        return [$request];
    }
}
