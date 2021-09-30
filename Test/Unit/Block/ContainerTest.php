<?php
namespace MediaLounge\Storyblok\Test\Unit\Block;

use Storyblok\ApiException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\FileSystem;
use Storyblok\Client as StoryblokClient;
use Magento\Store\Api\Data\StoreInterface;
use MediaLounge\Storyblok\Block\Container;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Cache\StateInterface;
use MediaLounge\Storyblok\Block\Container\Element;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Storyblok\ClientFactory as StoryblokClientFactory;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ContainerTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var FileSystem|MockObject
     */
    private $viewFileSystemMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @var StorybookClient|MockObject
     */
    private $storybookClientMock;

    /**
     * @var StorybookClientFactory|MockObject
     */
    private $storybookClientFactoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $apiKey = 'test-api-key';

        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->with('storyblok/general/api_key')
            ->willReturn($apiKey);

        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $cacheStateMock = $this->getMockForAbstractClass(StateInterface::class);

        $appStateMock = $this->createMock(State::class);
        $appStateMock->method('getAreaCode')->willReturn('frontend');

        $fileResolverMock = $this->createMock(Resolver::class);

        $urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->viewFileSystemMock = $this->getMockBuilder(FileSystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->method('getLayout')->willReturn($this->layoutMock);
        $this->contextMock->method('getEventManager')->willReturn($eventManagerMock);
        $this->contextMock->method('getResolver')->willReturn($fileResolverMock);
        $this->contextMock->method('getScopeConfig')->willReturn($scopeConfigMock);
        $this->contextMock->method('getCacheState')->willReturn($cacheStateMock);
        $this->contextMock->method('getStoreManager')->willReturn($storeManagerMock);
        $this->contextMock->method('getAppState')->willReturn($appStateMock);
        $this->contextMock->method('getUrlBuilder')->willReturn($urlBuilderMock);

        $this->storybookClientMock = $this->createMock(StoryblokClient::class);
        $this->storybookClientFactoryMock = $this->getMockBuilder(StoryblokClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->storybookClientFactoryMock
            ->expects($this->any())
            ->method('create')
            ->with(['apiKey' => $apiKey])
            ->willReturn($this->storybookClientMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testRenderBlocksFromStory()
    {
        $fixtureStoryArray = require __DIR__ . '../../_files/story_with_richtext_field.php';

        $blockMock = $this->createMock(Element::class);
        $blockMock
            ->expects($this->exactly(2))
            ->method('setData')
            ->withConsecutive(
                [$fixtureStoryArray['story']['content']],
                [$fixtureStoryArray['story']['content']['body'][0]]
            )
            ->willReturn($this->returnSelf());
        $blockMock
            ->expects($this->once())
            ->method('toHtml')
            ->willReturn('html');

        $this->layoutMock
            ->expects($this->exactly(2))
            ->method('createBlock')
            ->withConsecutive(
                [Element::class, $fixtureStoryArray['story']['content']['_uid']],
                [Element::class, $fixtureStoryArray['story']['content']['body'][0]['_uid']]
            )
            ->willReturn($blockMock);

        $this->viewFileSystemMock
            ->expects($this->exactly(2))
            ->method('getTemplateFileName')
            ->withConsecutive(
                [
                    "MediaLounge_Storyblok::story/{$fixtureStoryArray['story']['content']['component']}.phtml"
                ],
                [
                    "MediaLounge_Storyblok::story/{$fixtureStoryArray['story']['content']['body'][0]['component']}.phtml"
                ]
            )
            ->willReturnOnConsecutiveCalls(
                "{$fixtureStoryArray['story']['content']['component']}.phtml",
                "{$fixtureStoryArray['story']['content']['body'][0]['component']}.phtml"
            );

        $this->storybookClientMock->expects($this->never())->method('getStoryBySlug');

        $block = $this->objectManagerHelper->getObject(Container::class, [
            'viewFileSystem' => $this->viewFileSystemMock,
            'context' => $this->contextMock,
            'storyblokClient' => $this->storybookClientFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'data' => ['story' => $fixtureStoryArray['story']]
        ]);

        $this->assertEquals('html', $block->toHtml());
        $this->assertContains(
            "storyblok_{$fixtureStoryArray['story']['id']}",
            $block->getIdentities()
        );
        $this->assertContains(
            "storyblok_{$fixtureStoryArray['story']['id']}",
            $block->getCacheKeyInfo()
        );
    }

    public function testRenderStoryBySlug()
    {
        $storySlug = 'test-story';
        $fixtureStoryArray = require __DIR__ . '../../_files/story_with_richtext_field.php';

        $blockMock = $this->createMock(Element::class);
        $blockMock
            ->expects($this->exactly(2))
            ->method('setData')
            ->withConsecutive(
                [$fixtureStoryArray['story']['content']],
                [$fixtureStoryArray['story']['content']['body'][0]]
            )
            ->willReturn($this->returnSelf());
        $blockMock
            ->expects($this->once())
            ->method('toHtml')
            ->willReturn('html');

        $this->layoutMock
            ->expects($this->exactly(2))
            ->method('createBlock')
            ->withConsecutive(
                [Element::class, $fixtureStoryArray['story']['content']['_uid']],
                [Element::class, $fixtureStoryArray['story']['content']['body'][0]['_uid']]
            )
            ->willReturn($blockMock);

        $this->viewFileSystemMock
            ->expects($this->exactly(2))
            ->method('getTemplateFileName')
            ->withConsecutive(
                [
                    "MediaLounge_Storyblok::story/{$fixtureStoryArray['story']['content']['component']}.phtml"
                ],
                [
                    "MediaLounge_Storyblok::story/{$fixtureStoryArray['story']['content']['body'][0]['component']}.phtml"
                ]
            )
            ->willReturnOnConsecutiveCalls(
                "{$fixtureStoryArray['story']['content']['component']}.phtml",
                "{$fixtureStoryArray['story']['content']['body'][0]['component']}.phtml"
            );

        $this->storybookClientMock
            ->expects($this->once())
            ->method('getStoryBySlug')
            ->with($storySlug)
            ->willReturn($this->returnSelf());

        $this->storybookClientMock
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($fixtureStoryArray);

        $block = $this->objectManagerHelper->getObject(Container::class, [
            'viewFileSystem' => $this->viewFileSystemMock,
            'context' => $this->contextMock,
            'storyblokClient' => $this->storybookClientFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'data' => ['slug' => $storySlug]
        ]);

        $this->assertEquals('html', $block->toHtml());
        $this->assertContains("storyblok_slug_{$storySlug}", $block->getIdentities());
        $this->assertContains(
            "storyblok_{$fixtureStoryArray['story']['id']}",
            $block->getCacheKeyInfo()
        );
    }

    public function testDoesNotRenderStoryIfNotFound()
    {
        $storySlug = 'non-existent-story';

        $this->storybookClientMock
            ->expects($this->atLeastOnce())
            ->method('getStoryBySlug')
            ->with($storySlug)
            ->willThrowException(new ApiException());

        $block = $this->objectManagerHelper->getObject(Container::class, [
            'viewFileSystem' => $this->viewFileSystemMock,
            'context' => $this->contextMock,
            'storyblokClient' => $this->storybookClientFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'data' => ['slug' => $storySlug]
        ]);

        $this->assertEmpty($block->toHtml());
        $this->assertContains("storyblok_slug_{$storySlug}", $block->getIdentities());
        $this->assertContains("storyblok_slug_{$storySlug}", $block->getCacheKeyInfo());
    }

    public function testRendersDebugBlockIfTemplateNotFound()
    {
        $fixtureStoryArray = require __DIR__ . '../../_files/story_with_richtext_field.php';

        $blockMock = $this->createMock(Element::class);
        $blockMock
            ->expects($this->exactly(2))
            ->method('setData')
            ->withConsecutive(
                [$fixtureStoryArray['story']['content']],
                [$fixtureStoryArray['story']['content']['body'][0]]
            )
            ->willReturn($this->returnSelf());
        $blockMock
            ->expects($this->once())
            ->method('toHtml')
            ->willReturn('debug');
        $blockMock
            ->expects($this->exactly(2))
            ->method('setTemplate')
            ->with('MediaLounge_Storyblok::story/debug.phtml')
            ->willReturn($this->returnSelf());
        $blockMock
            ->expects($this->exactly(2))
            ->method('addData')
            ->withConsecutive(
                [
                    [
                        'original_template' => "MediaLounge_Storyblok::story/{$fixtureStoryArray['story']['content']['component']}.phtml"
                    ]
                ],
                [
                    [
                        'original_template' => "MediaLounge_Storyblok::story/{$fixtureStoryArray['story']['content']['body'][0]['component']}.phtml"
                    ]
                ]
            )
            ->willReturn($this->returnSelf());

        $this->layoutMock
            ->expects($this->exactly(2))
            ->method('createBlock')
            ->withConsecutive(
                [Element::class, $fixtureStoryArray['story']['content']['_uid']],
                [Element::class, $fixtureStoryArray['story']['content']['body'][0]['_uid']]
            )
            ->willReturn($blockMock);

        $this->viewFileSystemMock
            ->expects($this->exactly(2))
            ->method('getTemplateFileName')
            ->withConsecutive(
                [
                    "MediaLounge_Storyblok::story/{$fixtureStoryArray['story']['content']['component']}.phtml"
                ],
                [
                    "MediaLounge_Storyblok::story/{$fixtureStoryArray['story']['content']['body'][0]['component']}.phtml"
                ]
            )
            ->willReturn(false);

        $this->storybookClientMock->expects($this->never())->method('getStoryBySlug');

        $block = $this->objectManagerHelper->getObject(Container::class, [
            'viewFileSystem' => $this->viewFileSystemMock,
            'context' => $this->contextMock,
            'storyblokClient' => $this->storybookClientFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'data' => ['story' => $fixtureStoryArray['story']]
        ]);

        $this->assertEquals('debug', $block->toHtml());
        $this->assertContains(
            "storyblok_{$fixtureStoryArray['story']['id']}",
            $block->getIdentities()
        );
        $this->assertContains(
            "storyblok_{$fixtureStoryArray['story']['id']}",
            $block->getCacheKeyInfo()
        );
    }
}
