<?php
namespace MediaLounge\Storyblok\App\Cache;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\DeploymentConfig\Writer;

class State extends \Magento\Framework\App\Cache\State
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RequestInterface $request,
        DeploymentConfig $config,
        Writer $writer,
        $banAll = false
    ) {
        parent::__construct($config, $writer, $banAll);

        $this->request = $request;
    }

    public function isEnabled($cacheType): bool
    {
        if (
            in_array($cacheType, ['full_page', 'block_html']) &&
            $this->request->getParam('_storyblok')
        ) {
            return false;
        }

        return parent::isEnabled($cacheType);
    }
}
