<?php
namespace MediaLounge\Storyblok\Controller\Cache;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\RequestInterface;
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

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CacheInterface $cacheInterface,
        CacheType $cacheType,
        Json $json,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->cacheInterface = $cacheInterface;
        $this->cacheType = $cacheType;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(): ResultInterface
    {
        $success = false;
        $postContent = $this->json->unserialize($this->getRequest()->getContent());

        if ($this->isSignatureValid($this->getRequest()) && isset($postContent['story_id'])) {
            $tag = "storyblok_{$postContent['story_id']}";
            $this->cacheInterface->clean([$tag]);
            $this->cacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [$tag]);

            $success = true;
        }

        $result = $this->resultJsonFactory->create();
        $result->setData(['success' => $success]);

        return $result;
    }

    /**
     * Verify that the request is actually coming from Storyblok
     */
    private function isSignatureValid(RequestInterface $request): bool
    {
        $webhookSecret = $this->scopeConfig->getValue('storyblok/general/webhook_secret');
        $signature = hash_hmac('sha1', $request->getContent(), $webhookSecret);
        $webhookSignature = $request
            ->getHeaders()
            ->get('Webhook-Signature')
            ->getFieldValue();

        return $signature === $webhookSignature;
    }
}
