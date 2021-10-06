<?php

namespace MediaLounge\Storyblok\Controller\Cache;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\PageCache\Model\Cache\Type as CacheType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Clean extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CacheInterface
     */
    private $cacheInterface;

    /**
     * @var CacheType
     */
    private $cacheType;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CacheInterface $cacheInterface,
        CacheType $cacheType,
        Json $json,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TypeListInterface $cacheTypeList
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->cacheInterface = $cacheInterface;
        $this->cacheType = $cacheType;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->cacheTypeList = $cacheTypeList;
    }

    public function execute(): ResultInterface
    {
        $success = false;
        $postContent = $this->json->unserialize($this->getRequest()->getContent());

        if ($this->isSignatureValid($this->getRequest())) {
            if (isset($postContent['story_id'])) {
                preg_match('#\((.*?)\)#', $postContent['text'], $slug);

                $tags = ["storyblok_slug_{$slug[1]}", "storyblok_{$postContent['story_id']}"];
                $this->cacheInterface->clean($tags);
                $this->cacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);

                $success = true;
            } elseif (
                isset($postContent['action']) &&
                $postContent['action'] === 'release_merged'
            ) {
                $this->cleanPageCache();
                $success = true;
            }
        }

        $result = $this->resultJsonFactory->create();
        $result->setData(['success' => $success]);

        return $result;
    }

    /**
     * Clean page cache
     */
    private function cleanPageCache()
    {
        $types = ['layout', 'full_page', 'block_html'];

        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
    }

    /**
     * Verify that the request is actually coming from Storyblok
     */
    private function isSignatureValid(RequestInterface $request): bool
    {
        $webhookSecret = $this->scopeConfig->getValue(
            'storyblok/general/webhook_secret',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
        $signature = hash_hmac('sha1', $request->getContent(), $webhookSecret);
        $webhookSignature = $request
            ->getHeaders()
            ->get('Webhook-Signature')
            ->getFieldValue();

        return $signature === $webhookSignature;
    }
}
