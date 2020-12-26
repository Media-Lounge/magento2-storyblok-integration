<?php
namespace MediaLounge\Storyblok\App\Cache;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\DeploymentConfig\Writer;

class State extends \Magento\Framework\App\Cache\State
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Json $json,
        RequestInterface $request,
        DeploymentConfig $config,
        Writer $writer,
        $banAll = false
    ) {
        parent::__construct($config, $writer, $banAll);

        $this->json = $json;
        $this->request = $request;
    }

    public function isEnabled($cacheType): bool
    {
        $postContent = [];

        if ($this->request->getContent()) {
            $postContent = $this->json->unserialize($this->request->getContent());
        }

        if (
            in_array($cacheType, ['block_html']) &&
            ($this->request->getParam('_storyblok') || !empty($postContent['_storyblok']))
        ) {
            return false;
        }

        return parent::isEnabled($cacheType);
    }
}
