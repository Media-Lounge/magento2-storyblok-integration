<?php
namespace MediaLounge\Storyblok\Test\Unit\Block;

use PHPUnit\Framework\TestCase;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Store\Model\ScopeInterface;
use MediaLounge\Storyblok\Block\Script;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\TemplateEngineInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Framework\View\Element\Template\File\Validator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ScriptTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Script
     */
    private $block;

    /**
     * @var string
     */
    private $moduleName = 'MediaLounge_Storyblok';

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $appStateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $appStateMock->method('getAreaCode')->willReturn('frontend');

        $fileResolverMock = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileResolverMock->method('getTemplateFileName')->willReturn('template.phtml');

        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryReadInterface = $this->getMockForAbstractClass(ReadInterface::class);

        $filesystemMock->method('getDirectoryRead')->willReturn($directoryReadInterface);

        $storeInterfaceMock = $this->getMockForAbstractClass(StoreInterface::class);

        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock->method('getStore')->willReturn($storeInterfaceMock);

        $validatorMock = $this->createMock(Validator::class);
        $validatorMock->method('isValid')->willReturn(true);

        $templateEnginePoolMock = $this->createMock(TemplateEnginePool::class);
        $templateEngineMock = $this->getMockForAbstractClass(TemplateEngineInterface::class);
        $templateEngineMock->method('render')->willReturn('html');
        $templateEnginePoolMock->method('get')->willReturn($templateEngineMock);

        $this->contextMock->method('getAppState')->willReturn($appStateMock);
        $this->contextMock->method('getEventManager')->willReturn($eventManagerMock);
        $this->contextMock->method('getFilesystem')->willReturn($filesystemMock);
        $this->contextMock->method('getResolver')->willReturn($fileResolverMock);
        $this->contextMock->method('getValidator')->willReturn($validatorMock);
        $this->contextMock->method('getEnginePool')->willReturn($templateEnginePoolMock);
        $this->contextMock->method('getStoreManager')->willReturn($storeManagerMock);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getScopeConfig')->willReturn($this->scopeConfigMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testGetApiKey()
    {
        $apiKey = 'test-api-key';

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('storyblok/general/api_key')
            ->willReturn($apiKey);

        $this->block = $this->objectManagerHelper->getObject(Script::class, [
            'context' => $this->contextMock
        ]);

        $this->block->setTemplate("{$this->moduleName}::script.phtml");

        $this->assertSame($apiKey, $this->block->getApiKey());
    }

    public function testDoesNotRenderWithoutApiKey()
    {
        $apiKey = null;

        $this->scopeConfigMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->withConsecutive(
                [
                    "advanced/modules_disable_output/{$this->moduleName}",
                    ScopeInterface::SCOPE_STORE
                ],
                ['storyblok/general/api_key']
            )
            ->willReturnOnConsecutiveCalls(false, $apiKey);

        $this->block = $this->objectManagerHelper->getObject(Script::class, [
            'context' => $this->contextMock
        ]);

        $this->block->setTemplate("{$this->moduleName}::script.phtml");

        $this->assertEmpty($this->block->toHtml());
    }

    public function testDoesNotRenderOutsideStoryblok()
    {
        $apiKey = 'test-api-key';

        $this->scopeConfigMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->withConsecutive(
                [
                    "advanced/modules_disable_output/{$this->moduleName}",
                    ScopeInterface::SCOPE_STORE
                ],
                ['storyblok/general/api_key']
            )
            ->willReturnOnConsecutiveCalls(false, $apiKey);

        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('_storyblok')
            ->willReturn(null);

        $this->block = $this->objectManagerHelper->getObject(Script::class, [
            'context' => $this->contextMock
        ]);

        $this->block->setTemplate("{$this->moduleName}::script.phtml");

        $this->assertEmpty($this->block->toHtml());
    }

    public function testRendersWithApiKeyInStoryblok()
    {
        $apiKey = 'test-api-key';
        $storyId = '123456';

        $this->scopeConfigMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->withConsecutive(
                [
                    "advanced/modules_disable_output/{$this->moduleName}",
                    ScopeInterface::SCOPE_STORE
                ],
                ['storyblok/general/api_key']
            )
            ->willReturnOnConsecutiveCalls(false, $apiKey);

        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('_storyblok')
            ->willReturn($storyId);

        $this->block = $this->objectManagerHelper->getObject(Script::class, [
            'context' => $this->contextMock
        ]);

        $this->block->setTemplate("{$this->moduleName}::script.phtml");

        $this->assertNotEmpty($this->block->toHtml());
    }
}
