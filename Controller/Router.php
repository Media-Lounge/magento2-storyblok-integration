<?php
namespace MediaLounge\Storyblok\Controller;

use Storyblok\ApiException;
use Storyblok\ClientFactory;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var Storyblok\Client
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

    public function __construct(
        ActionFactory $actionFactory,
        ScopeConfigInterface $scopeConfig,
        ClientFactory $storyblokClient,
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->actionFactory = $actionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storyblokClient = $storyblokClient->create([
            'apiKey' => $this->scopeConfig->getValue('storyblok/general/api_key'),
        ]);
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    public function match(RequestInterface $request): ?ActionInterface
    {
        $identifier = trim($request->getPathInfo(), '/');

        try {
            $data = $this->cache->load($identifier);

            if (!$data || $request->getParam('_storyblok')) {
                $response = $this->storyblokClient->getStoryBySlug($identifier);
                $data = $this->serializer->serialize($response->getBody());

                if (!$request->getParam('_storyblok') && !empty($response->getBody()['story'])) {
                    $this->cache->save($data, $identifier, [
                        "storyblok_{$response->getBody()['story']['id']}",
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
                        'story' => $data['story'],
                    ]);

                return $this->actionFactory->create(Forward::class, ['request' => $request]);
            }
        } catch (ApiException $e) {
            return null;
        }

        return null;
    }
}
