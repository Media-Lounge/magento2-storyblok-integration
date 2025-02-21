<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\Model;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class HomeSlug implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
    ) {}

    public function __toString()
    {
        $slug = $this->config->homeSlug();

        return $slug;
    }
}
