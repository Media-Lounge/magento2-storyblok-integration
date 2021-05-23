<?php
namespace MediaLounge\Storyblok\Test\Unit\Controller\Cache;

use Laminas\Http\Headers;
use PHPUnit\Framework\TestCase;
use Laminas\Http\Header\HeaderInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Controller\Result\Json;
use Magento\Store\Model\StoreManagerInterface;
use MediaLounge\Storyblok\Controller\Cache\Clean;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\PageCache\Model\Cache\Type as CacheType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CleanTest extends TestCase
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
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheInterfaceMock;

    /**
     * @var CacheType|MockObject
     */
    private $cacheTypeMock;

    /**
     * @var Json|MockObject
     */
    private $jsonMock;

    /**
     * @var JsonSerializer|MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var HeaderInterface|MockObject
     */
    private $headerMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var string
     */
    private $fixtureWebhook;

    /**
     * @var array
     */
    private $fixtureWebhookArray;

    protected function setUp(): void
    {
        $this->fixtureWebhook = file_get_contents(__DIR__ . '../../../_files/story_webhook.json');
        $this->fixtureWebhookArray = require __DIR__ . '../../../_files/story_webhook.php';

        $this->headerMock = $this->getMockForAbstractClass(HeaderInterface::class);

        $headersMock = $this->getMockBuilder(Headers::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headersMock
            ->expects($this->once())
            ->method('get')
            ->with('Webhook-Signature')
            ->willReturn($this->headerMock);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->addMethods(['getContent', 'getHeaders'])
            ->getMockForAbstractClass();
        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getContent')
            ->willReturn($this->fixtureWebhook);
        $this->requestMock
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn($headersMock);

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock
            ->expects($this->atLeastOnce())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->cacheTypeMock = $this->getMockBuilder(CacheType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonSerializerMock = $this->getMockBuilder(JsonSerializer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($this->fixtureWebhook)
            ->willReturn($this->fixtureWebhookArray);

        $this->jsonMock = $this->createMock(Json::class);
        $this->resultJsonFactoryMock = $this->createMock(JsonFactory::class);

        $this->resultJsonFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->jsonMock);

        $this->cacheInterfaceMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('storyblok/general/webhook_secret')
            ->willReturn('webhook-secret');

        $storeInterfaceMock = $this->getMockForAbstractClass(StoreInterface::class);

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn($storeInterfaceMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testExecute()
    {
        $fixtureHash = '7bdb5c5b4207cc55f46368aac89067bbd8c93afd';

        $this->headerMock
            ->expects($this->once())
            ->method('getFieldValue')
            ->willReturn($fixtureHash);

        $this->jsonMock
            ->expects($this->once())
            ->method('setData')
            ->with(['success' => true]);

        $this->cacheInterfaceMock
            ->expects($this->once())
            ->method('clean')
            ->with(["storyblok_{$this->fixtureWebhookArray['story_id']}"]);

        $this->cacheTypeMock
            ->expects($this->once())
            ->method('clean')
            ->with(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [
                "storyblok_{$this->fixtureWebhookArray['story_id']}"
            ]);

        $controller = $this->objectManagerHelper->getObject(Clean::class, [
            'context' => $this->contextMock,
            'resultJsonFactory' => $this->resultJsonFactoryMock,
            'cacheInterface' => $this->cacheInterfaceMock,
            'cacheType' => $this->cacheTypeMock,
            'json' => $this->jsonSerializerMock,
            'scopeConfig' => $this->scopeConfigMock,
            'storeManager' => $this->storeManagerMock
        ]);

        $controller->execute();
    }

    public function testDoesNotClearCacheIfRequestNotValid()
    {
        $fixtureHash = 'incorrect-fixture-hash';

        $this->headerMock
            ->expects($this->once())
            ->method('getFieldValue')
            ->willReturn($fixtureHash);

        $this->cacheInterfaceMock->expects($this->never())->method('clean');
        $this->cacheTypeMock->expects($this->never())->method('clean');

        $this->jsonMock
            ->expects($this->once())
            ->method('setData')
            ->with(['success' => false]);

        $controller = $this->objectManagerHelper->getObject(Clean::class, [
            'context' => $this->contextMock,
            'resultJsonFactory' => $this->resultJsonFactoryMock,
            'cacheInterface' => $this->cacheInterfaceMock,
            'cacheType' => $this->cacheTypeMock,
            'json' => $this->jsonSerializerMock,
            'scopeConfig' => $this->scopeConfigMock,
            'storeManager' => $this->storeManagerMock
        ]);

        $controller->execute();
    }
}
