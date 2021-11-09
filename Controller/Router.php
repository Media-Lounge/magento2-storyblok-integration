<?php
namespace MediaLounge\Storyblok\Controller;

use Storyblok\ApiException;
use Storyblok\ClientFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var \Storyblok\Client
     */
    private $storyblokClient;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var StoreManagerInteface
     */
    private $storeManager;

    public function __construct(
        ActionFactory $actionFactory,
        ScopeConfigInterface $scopeConfig,
        ClientFactory $storyblokClient,
        CacheInterface $cache,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager
    ) {
        $this->actionFactory = $actionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->storyblokClient = $storyblokClient->create([
            'apiKey' => $this->scopeConfig->getValue(
                'storyblok/general/api_key',
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            )
        ]);
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    public function match(RequestInterface $request): ?ActionInterface
    {
        $identifier = trim($request->getPathInfo(), '/');

        $storeLanguage = $this->scopeConfig->getValue(
            \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        if ($storeLanguage !== 'en_GB') {
            $identifier =
                strpos($identifier, $storeLanguage) !== 0
                    ? strtolower(str_replace('_', '-', $storeLanguage)) . "/{$identifier}"
                    : $identifier;
        }

        try {
            $data = $this->cache->load($identifier);

            if (!$data || $request->getParam('_storyblok')) {
                $response = $this->storyblokClient->getStoryBySlug($identifier);
                $data = $this->serializer->serialize($response->getBody());

                if (!$request->getParam('_storyblok') && !empty($response->getBody()['story'])) {
                    $this->cache->save($data, $identifier, [
                        "storyblok_{$response->getBody()['story']['id']}"
                    ]);
                }
            }

            $data = $this->serializer->unserialize($data);

            if (!empty($data['story'])) {
                $request
                    ->setModuleName('storyblok')
                    ->setControllerName('index')
                    ->setActionName('index')
                    ->setParams([
                        'story' => $data['story']
                    ]);

                return $this->actionFactory->create(Forward::class, ['request' => $request]);
            }
        } catch (ApiException $e) {
            return null;
        }

        return null;
    }
}
