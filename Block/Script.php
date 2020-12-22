<?php
namespace MediaLounge\Storyblok\Block;

class Script extends \Magento\Framework\View\Element\Template
{
    public function getApiKey(): string
    {
        return $this->_scopeConfig->getValue('storyblok/general/api_key');
    }

    protected function _toHtml(): string
    {
        if ($this->getApiKey() && $this->getRequest()->getParam('_storyblok')) {
            return parent::_toHtml();
        }

        return '';
    }
}
