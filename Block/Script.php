<?php
namespace MediaLounge\Storyblok\Block;

use Magento\Store\Model\ScopeInterface;

class Script extends \Magento\Framework\View\Element\Template
{
    public function getApiKey(): ?string
    {
        return $this->_scopeConfig->getValue(
            'storyblok/general/api_key',
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }

    protected function _toHtml(): ?string
    {
        if ($this->getApiKey() && $this->getRequest()->getParam('_storyblok')) {
            return parent::_toHtml();
        }

        return '';
    }
}
