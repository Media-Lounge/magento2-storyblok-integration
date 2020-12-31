<?php
namespace MediaLounge\Storyblok\Test\Unit\Controller;

use Storyblok\ApiException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\ActionFactory;
use Storyblok\Client as StoryblokClient;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use MediaLounge\Storyblok\Controller\Router;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Storyblok\ClientFactory as StoryblokClientFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RouterTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ActionFactory|MockObject
     */
    private $actionFactoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoryblokClient|MockObject
     */
    private $storybookClientMock;

    /**
     * @var StoryblokClientFactory|MockObject
     */
    private $storybookClientFactoryMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var string
     */
    private $storySlug;

    /**
     * @var string
     */
    private $storyId = '123456';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $fixtureStory;

    /**
     * @var array
     */
    private $fixtureStoryArray;

    protected function setUp(): void
    {
        $this->storySlug = 'test-story';
        $this->apiKey = 'test-api-key';
        $this->fixtureStory = file_get_contents(__DIR__ . '../../_files/story_with_blocks.json');
        $this->fixtureStoryArray = require __DIR__ . '../../_files/story_with_blocks.php';

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->addMethods(['getPathInfo', 'setControllerName'])
            ->getMockForAbstractClass();
        $this->requestMock
            ->expects($this->once())
            ->method('getPathInfo')
            ->willReturn($this->storySlug);

        $this->actionFactoryMock = $this->createMock(ActionFactory::class);

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('storyblok/general/api_key')
            ->willReturn($this->apiKey);

        $this->storybookClientFactoryMock = $this->getMockBuilder(StoryblokClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storybookClientMock = $this->getMockBuilder(StoryblokClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testMatch()
    {
        $this->cacheMock
            ->expects($this->once())
            ->method('load')
            ->with($this->storySlug)
            ->willReturn(null);

        $this->cacheMock
            ->expects($this->once())
            ->method('save')
            ->with($this->fixtureStory, $this->storySlug, [
                "storyblok_{$this->fixtureStoryArray['story']['id']}",
            ])
            ->willReturn(true);

        $this->storybookClientMock
            ->expects($this->once())
            ->method('getStoryBySlug')
            ->with($this->storySlug)
            ->willReturn($this->returnSelf());
        $this->storybookClientMock
            ->expects($this->atLeastOnce())
            ->method('getBody')
            ->willReturn($this->fixtureStoryArray);
        $this->storybookClientFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['apiKey' => $this->apiKey])
            ->willReturn($this->storybookClientMock);

        $this->serializerMock
            ->expects($this->once())
            ->method('serialize')
            ->with($this->fixtureStoryArray)
            ->willReturn($this->fixtureStory);
        $this->serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($this->fixtureStory)
            ->willReturn($this->fixtureStoryArray);

        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getParam')
            ->with('_storyblok')
            ->willReturn(false);
        $this->requestMock
            ->expects($this->once())
            ->method('setModuleName')
            ->with('storyblok')
            ->willReturn($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('setControllerName')
            ->with('index')
            ->willReturn($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('setActionName')
            ->with('index')
            ->willReturn($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('setParams')
            ->with([
                'story' => $this->fixtureStoryArray['story'],
            ])
            ->willReturn($this->returnSelf());

        $this->actionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(Forward::class, ['request' => $this->requestMock])
            ->willReturn($this->getMockForAbstractClass(ActionInterface::class));

        $router = $this->objectManagerHelper->getObject(Router::class, [
            'actionFactory' => $this->actionFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'storyblokClient' => $this->storybookClientFactoryMock,
            'cache' => $this->cacheMock,
            'serializer' => $this->serializerMock,
        ]);

        $this->assertInstanceOf(ActionInterface::class, $router->match($this->requestMock));
    }

    public function testDoesNotMatch()
    {
        $this->storybookClientMock
            ->expects($this->once())
            ->method('getStoryBySlug')
            ->with($this->storySlug)
            ->willThrowException(new ApiException());

        $this->storybookClientFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['apiKey' => $this->apiKey])
            ->willReturn($this->storybookClientMock);

        $router = $this->objectManagerHelper->getObject(Router::class, [
            'actionFactory' => $this->actionFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'storyblokClient' => $this->storybookClientFactoryMock,
            'cache' => $this->cacheMock,
            'serializer' => $this->serializerMock,
        ]);

        $this->assertNull($router->match($this->requestMock));
    }

    public function testStoryIsEmpty()
    {
        $this->storybookClientMock
            ->expects($this->once())
            ->method('getStoryBySlug')
            ->with($this->storySlug)
            ->willReturn($this->returnSelf());

        $this->storybookClientFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['apiKey' => $this->apiKey])
            ->willReturn($this->storybookClientMock);

        $router = $this->objectManagerHelper->getObject(Router::class, [
            'actionFactory' => $this->actionFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'storyblokClient' => $this->storybookClientFactoryMock,
            'cache' => $this->cacheMock,
            'serializer' => $this->serializerMock,
        ]);

        $this->assertNull($router->match($this->requestMock));
    }

    public function testAvoidFetchingStoryWhenCached()
    {
        $this->cacheMock
            ->expects($this->once())
            ->method('load')
            ->with($this->storySlug)
            ->willReturn($this->fixtureStory);

        $this->serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($this->fixtureStory)
            ->willReturn($this->fixtureStoryArray);

        $this->storybookClientMock->expects($this->never())->method('getStoryBySlug');

        $this->storybookClientFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['apiKey' => $this->apiKey])
            ->willReturn($this->storybookClientMock);

        $this->requestMock
            ->expects($this->once())
            ->method('setModuleName')
            ->with('storyblok')
            ->willReturn($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('setControllerName')
            ->with('index')
            ->willReturn($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('setActionName')
            ->with('index')
            ->willReturn($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('setParams')
            ->with([
                'story' => $this->fixtureStoryArray['story'],
            ])
            ->willReturn($this->returnSelf());

        $this->actionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(Forward::class, ['request' => $this->requestMock])
            ->willReturn($this->getMockForAbstractClass(ActionInterface::class));

        $router = $this->objectManagerHelper->getObject(Router::class, [
            'actionFactory' => $this->actionFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'storyblokClient' => $this->storybookClientFactoryMock,
            'cache' => $this->cacheMock,
            'serializer' => $this->serializerMock,
        ]);

        $this->assertInstanceOf(ActionInterface::class, $router->match($this->requestMock));
    }

    public function testAvoidCacheWhenInStoryblok()
    {
        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getParam')
            ->with('_storyblok')
            ->willReturn($this->storyId);

        $this->cacheMock
            ->expects($this->once())
            ->method('load')
            ->with($this->storySlug)
            ->willReturn($this->fixtureStory);

        $this->cacheMock
            ->expects($this->never())
            ->method('save')
            ->with($this->storySlug);

        $this->serializerMock
            ->expects($this->once())
            ->method('serialize')
            ->with($this->fixtureStoryArray)
            ->willReturn($this->fixtureStory);
        $this->serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($this->fixtureStory)
            ->willReturn($this->fixtureStoryArray);

        $this->storybookClientMock
            ->expects($this->once())
            ->method('getStoryBySlug')
            ->with($this->storySlug)
            ->willReturn($this->returnSelf());

        $this->storybookClientMock
            ->expects($this->atLeastOnce())
            ->method('getBody')
            ->willReturn($this->fixtureStoryArray);

        $this->storybookClientFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['apiKey' => $this->apiKey])
            ->willReturn($this->storybookClientMock);

        $this->requestMock
            ->expects($this->once())
            ->method('setModuleName')
            ->with('storyblok')
            ->willReturn($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('setControllerName')
            ->with('index')
            ->willReturn($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('setActionName')
            ->with('index')
            ->willReturn($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('setParams')
            ->with([
                'story' => $this->fixtureStoryArray['story'],
            ])
            ->willReturn($this->returnSelf());

        $this->actionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(Forward::class, ['request' => $this->requestMock])
            ->willReturn($this->getMockForAbstractClass(ActionInterface::class));

        $router = $this->objectManagerHelper->getObject(Router::class, [
            'actionFactory' => $this->actionFactoryMock,
            'scopeConfig' => $this->scopeConfigMock,
            'storyblokClient' => $this->storybookClientFactoryMock,
            'cache' => $this->cacheMock,
            'serializer' => $this->serializerMock,
        ]);

        $this->assertInstanceOf(ActionInterface::class, $router->match($this->requestMock));
    }
}
