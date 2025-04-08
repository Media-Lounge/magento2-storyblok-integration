<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\Plugin;

use Magento\Framework\App\RequestInterface;
use MediaLounge\Storyblok\Controller\Router;
use MediaLounge\Storyblok\Model\Config;

class PrefixSlug
{
    public function __construct(
        private readonly Config $config,
    ) {}

    /**
     * @see Router::match()
     * @return RequestInterface[]
     */
    public function beforeMatch(Router $subject, RequestInterface $request): array
    {
        $prefix = $this->config->slugPrefix();

        if ($prefix) {
            $identifier = trim($request->getPathInfo(), '/');
            $identifier = "{$prefix}/{$identifier}";
            $request->setPathInfo($identifier);
        }

        return [$request];
    }
}
