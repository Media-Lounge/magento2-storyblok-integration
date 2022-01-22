<?php

namespace MediaLounge\Storyblok\Observer;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Log\LoggerInterface;

class DebugInfoRequest implements ObserverInterface
{

    const URI_BASE = 'https://debug.local';
    const URI_PATH = 'path-to-endpoind';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $magentoMeta;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $storeConfig;

    /**
     * @var \GuzzleHttp\ClientFactory
     */
    private $clientFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\ProductMetadataInterface $magentoMeta
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $storeConfig
     * @param \GuzzleHttp\ClientFactory $clientFactory
     */
    public function __construct(
        ProductMetadataInterface $magentoMeta,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ScopeConfigInterface $storeConfig,
        ClientFactory $clientFactory,
        LoggerInterface $logger

    )
    {
        $this->magentoMeta = $magentoMeta;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        /* todo: suggest: Add License check only send request if not license instead of admin setting value*/
        $isEnable = $this->storeConfig->isSetFlag('storyblok/dev/enabled_debug_request');
        if ($isEnable) {
            $debugInfo = [
                'host' => $this->storeManager->getStore()->getBaseUrl(),
                'version' => $this->magentoMeta->getVersion(),
                'edition' => $this->magentoMeta->getEdition(),
                'php' => phpversion()
            ];
            $this->doRequest($debugInfo);
        }
    }

    /**
     * @param array $params
     * @return void
     */
    private function doRequest(
        array $params = []
    )
    {
        $client = $this->clientFactory->create(
            ['config' => [
                'base_uri' => self::URI_BASE
            ]]
        );
        try {
            $client->request(
                "POST",
                self::URI_PATH,
                $params
            );
        } catch (GuzzleException $exception) {
            $this->logger->error("Storyblok Send Debug Error: " . $exception->getMessage());
        }
    }
}
