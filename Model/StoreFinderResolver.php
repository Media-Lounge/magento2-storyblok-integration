<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\Model;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class StoreFinderResolver implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
    ) {}

    public function __toString()
    {
        $slug = $this->config->storeFinderSlug();
        $prefix = $this->config->slugPrefix();

        if ($prefix) {
            $slug = trim("{$prefix}/{$slug}", '/');
        }

        return $slug;
    }
}
