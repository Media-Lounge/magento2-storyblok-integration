<?php
namespace MediaLounge\Storyblok\Test\Unit\Controller\Index;

use PHPUnit\Framework\TestCase;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\App\Action\Context;
use MediaLounge\Storyblok\Block\Container;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Controller\Result\Json;
use MediaLounge\Storyblok\Controller\Index\Ajax;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AjaxTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var JsonSerializer|MockObject
     */
    private $jsonSerializerMock;

    protected function setUp(): void
    {
        $fixtureStory = file_get_contents(__DIR__ . '../../../_files/story_with_blocks.json');
        $fixtureStoryArray = require __DIR__ . '../../../_files/story_with_blocks.php';

        $blockMock = $this->getMockBuilder(BlockInterface::class)
            ->addMethods(['setStory'])
            ->getMockForAbstractClass();
        $blockMock
            ->expects($this->once())
            ->method('setStory')
            ->with($fixtureStoryArray['story'])
            ->willReturn($this->returnSelf());
        $blockMock
            ->expects($this->once())
            ->method('toHtml')
            ->willReturn('html');

        $updateMock = $this->getMockForAbstractClass(ProcessorInterface::class);
        $updateMock
            ->expects($this->once())
            ->method('addHandle')
            ->with('storyblok_index_ajax')
            ->willReturn($this->returnSelf());

        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $layoutMock
            ->expects($this->once())
            ->method('createBlock')
            ->with(Container::class)
            ->willReturn($blockMock);
        $layoutMock
            ->expects($this->once())
            ->method('getUpdate')
            ->willReturn($updateMock);

        $viewMock = $this->getMockForAbstractClass(ViewInterface::class);
        $viewMock
            ->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->addMethods(['getContent'])
            ->getMockForAbstractClass();
        $requestMock
            ->expects($this->atLeastOnce())
            ->method('getContent')
            ->willReturn($fixtureStory);

        $jsonMock = $this->createMock(Json::class);
        $jsonMock
            ->expects($this->once())
            ->method('setData')
            ->with([$fixtureStoryArray['story']['content']['_uid'] => 'html'])
            ->willReturn($this->returnSelf());

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock
            ->expects($this->atLeastOnce())
            ->method('getRequest')
            ->will($this->returnValue($requestMock));
        $this->contextMock
            ->expects($this->atLeastOnce())
            ->method('getView')
            ->willReturn($viewMock);

        $this->resultJsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->resultJsonFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($jsonMock);

        $this->jsonSerializerMock = $this->getMockBuilder(JsonSerializer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($fixtureStory)
            ->willReturn($fixtureStoryArray);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testExecute()
    {
        $controller = $this->objectManagerHelper->getObject(Ajax::class, [
            'context' => $this->contextMock,
            'json' => $this->jsonSerializerMock,
            'resultJsonFactory' => $this->resultJsonFactoryMock,
        ]);

        $controller->execute();
    }
}
