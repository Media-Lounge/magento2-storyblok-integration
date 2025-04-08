<?php

namespace MediaLounge\Storyblok\ViewModel;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductSkuSlug implements ArgumentInterface
{
    public function __construct(
        private readonly Http $request,
        private readonly ProductRepository $productRepository,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager
    ) {}

    public function __toString(): string
    {;
        $url = "{$_SERVER['REQUEST_URI']}";

        if (
            $this->request->getControllerName() === 'product' &&
            $this->request->getParam('id')
        ) {
            $slug = $this->scopeConfig->getValue(
                'storyblok/general/product_list_slug',
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            );
            $slugPrefix = $this->scopeConfig->getValue(
                'storyblok/general/slug_prefix',
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            );
            $product = $this->productRepository->getById($this->request->getParam('id'));
            $url = "{$slugPrefix}/{$slug}/{$product->getUrlKey()}";
        }

        return $url;
    }
}
