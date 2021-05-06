<?php
/**
 * Copyright © Media Lounge. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MediaLounge\Storyblok\Block;

use Magento\Framework\View\Element\Template;

use MediaLounge\Storyblok\Model\ConfigInterface;

class Script extends Template
{
    /**
     * Storyblok module configuration
     *
     * @var ConfigInterface
     */
    private $storyblokConfig;

    /**
     * @param Template\Context $context
     */
    public function __construct(
        Template\Context $context,
        ConfigInterface $storyblokConfig,
        array $data = []
    ) {
        $this->storyblokConfig = $storyblokConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get API key
     *
     * @return null|string
     */
    public function getApiKey(): ?string
    {
        return $this->storyblokConfig->getApiKey();
    }

    protected function _toHtml(): ?string
    {
        if ($this->getApiKey() && $this->getRequest()->getParam('_storyblok')) {
            return parent::_toHtml();
        }

        return '';
    }
}
