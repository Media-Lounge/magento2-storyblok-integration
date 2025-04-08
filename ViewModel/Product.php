<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\ViewModel;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Product implements ArgumentInterface
{

    public function __construct(
        private readonly CollectionFactory $collectionFactory
    ){}

    public function getProductsBySkus(?string $productSkus): array
    {
        if (!$productSkus) {
            return [];
        }

        $skus = explode(',', $productSkus);

        // Create a product collection
        $collection = $this->collectionFactory->create();

        // Add attributes to select
        $collection->addAttributeToSelect('*'); // Or specify attributes, e.g., ['name', 'price', 'sku']

        // Add filter for SKUs
        $collection->addFieldToFilter('sku', ['in' => $skus]);

        return $collection->getItems();
    }
}
