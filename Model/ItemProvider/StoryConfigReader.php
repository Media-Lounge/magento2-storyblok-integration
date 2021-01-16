<?php
namespace MediaLounge\Storyblok\Model\ItemProvider;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;

class StoryConfigReader implements ConfigReaderInterface
{
    const XML_PATH_CHANGE_FREQUENCY = 'sitemap/storyblok/changefreq';
    const XML_PATH_PRIORITY = 'sitemap/storyblok/priority';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getPriority($storeId): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getChangeFrequency($storeId): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CHANGE_FREQUENCY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
