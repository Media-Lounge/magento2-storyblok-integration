<?php
namespace MediaLounge\Storyblok\Test\Unit\Model\ItemProvider;

use PHPUnit\Framework\TestCase;
use Magento\Sitemap\Model\SitemapItem;
use Storyblok\Client as StoryblokClient;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Storyblok\ClientFactory as StoryblokClientFactory;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use MediaLounge\Storyblok\Model\ItemProvider\Story as StoryItemResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StoryTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ConfigReaderInterface|MockObject
     */
    private $configReaderMock;

    /**
     * @var SitemapItemInterfaceFactory|MockObject
     */
    private $itemFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var string
     */
    private $apiKey;

    protected function setUp(): void
    {
        $this->apiKey = 'test-api-key';

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->configReaderMock = $this->getMockForAbstractClass(ConfigReaderInterface::class);

        $this->itemFactoryMock = $this->getMockBuilder(SitemapItemInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with('storyblok/general/api_key')
            ->willReturn($this->apiKey);

        $storeInterfaceMock = $this->getMockForAbstractClass(StoreInterface::class);

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn($storeInterfaceMock);
    }

    public function testGetItemsEmpty()
    {
        $storybookClientMock = $this->createMock(StoryblokClient::class);
        $storybookClientMock
            ->expects($this->any())
            ->method('getStories')
            ->with(['page' => 1, 'per_page' => 100, 'filter_query[component][like]' => 'page'])
            ->willReturn($this->returnSelf());

        $storybookClientMock
            ->expects($this->atLeastOnce())
            ->method('getHeaders')
            ->willReturn([
                'Total' => [0],
            ]);

        $storybookClientMock
            ->expects($this->any())
            ->method('getBody')
            ->willReturn([
                'stories' => [],
            ]);

        $storybookClientFactoryMock = $this->getMockBuilder(StoryblokClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $storybookClientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->with(['apiKey' => $this->apiKey])
            ->willReturn($storybookClientMock);

        $this->storyItemResolver = $this->objectManagerHelper->getObject(StoryItemResolver::class, [
            'configReader' => $this->configReaderMock,
            'itemFactory' => $this->itemFactoryMock,
            'storyblokClient' => $storybookClientFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'storeManager' => $this->storeManagerMock,
        ]);

        $this->assertEmpty($this->storyItemResolver->getItems(1));
    }

    public function testGetItems()
    {
        $testStories = [
            [
                'full_slug' => 'test-story',
                'published_at' => '2021-01-16T08:55:16+00:00',
            ],
            [
                'full_slug' => 'test-story-2',
                'published_at' => '2021-01-06T08:42:02+00:00',
            ],
        ];

        $this->configReaderMock
            ->expects($this->atLeastOnce())
            ->method('getPriority')
            ->willReturn('0.25');
        $this->configReaderMock
            ->expects($this->atLeastOnce())
            ->method('getChangeFrequency')
            ->willReturn('daily');

        $storybookClientMock = $this->createMock(StoryblokClient::class);
        $storybookClientMock
            ->expects($this->exactly(2))
            ->method('getStories')
            ->withConsecutive(
                [['page' => 1, 'per_page' => 100, 'filter_query[component][like]' => 'page']],
                [['page' => 2, 'per_page' => 100, 'filter_query[component][like]' => 'page']]
            )
            ->willReturn($this->returnSelf());

        $storybookClientMock
            ->expects($this->atLeastOnce())
            ->method('getHeaders')
            ->willReturn([
                'Total' => [150],
            ]);

        $storybookClientMock
            ->expects($this->any())
            ->method('getBody')
            ->willReturn([
                'stories' => $testStories,
            ]);

        $sitemapItem = $this->createMock(SitemapItem::class);
        $this->itemFactoryMock
            ->expects($this->any())
            ->method('create')
            ->withConsecutive(
                [
                    [
                        'url' => $testStories[0]['full_slug'],
                        'updatedAt' => $testStories[0]['published_at'],
                        'priority' => '0.25',
                        'changeFrequency' => 'daily',
                    ],
                ],
                [
                    [
                        'url' => $testStories[1]['full_slug'],
                        'updatedAt' => $testStories[1]['published_at'],
                        'priority' => '0.25',
                        'changeFrequency' => 'daily',
                    ],
                ],
                [
                    [
                        'url' => $testStories[0]['full_slug'],
                        'updatedAt' => $testStories[0]['published_at'],
                        'priority' => '0.25',
                        'changeFrequency' => 'daily',
                    ],
                ],
                [
                    [
                        'url' => $testStories[1]['full_slug'],
                        'updatedAt' => $testStories[1]['published_at'],
                        'priority' => '0.25',
                        'changeFrequency' => 'daily',
                    ],
                ]
            )
            ->willReturn($sitemapItem);

        $storybookClientFactoryMock = $this->getMockBuilder(StoryblokClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $storybookClientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->with(['apiKey' => $this->apiKey])
            ->willReturn($storybookClientMock);

        $this->storyItemResolver = $this->objectManagerHelper->getObject(StoryItemResolver::class, [
            'configReader' => $this->configReaderMock,
            'itemFactory' => $this->itemFactoryMock,
            'storyblokClient' => $storybookClientFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'storeManager' => $this->storeManagerMock,
        ]);

        $this->assertCount(4, $this->storyItemResolver->getItems(1));
    }
}
