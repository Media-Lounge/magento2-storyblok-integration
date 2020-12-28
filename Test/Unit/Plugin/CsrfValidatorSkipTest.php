<?php
namespace MediaLounge\Storyblok\Test\Unit\Plugin;

use PHPUnit\Framework\TestCase;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\CsrfValidator;
use MediaLounge\Storyblok\Plugin\CsrfValidatorSkip;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CsrfValidatorSkipTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \stdClass|MockObject
     */
    private $closureMock;

    /**
     * @var ActionInterface|MockObject
     */
    private $actionMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var CsrfValidator|MockObject
     */
    private $csrfValidatorMock;

    /**
     * @var CsrfValidatorSkip|MockObject
     */
    private $csrfValidatorSkip;

    protected function setUp(): void
    {
        $this->closureMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
        $this->actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->csrfValidatorMock = $this->getMockBuilder(CsrfValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->csrfValidatorSkip = $this->objectManagerHelper->getObject(CsrfValidatorSkip::class);
    }

    public function testCsrfWorksForNonStoryblokModules()
    {
        $this->closureMock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->requestMock, $this->actionMock);

        $this->csrfValidatorSkip->aroundValidate(
            $this->csrfValidatorMock,
            \Closure::fromCallable($this->closureMock),
            $this->requestMock,
            $this->actionMock
        );
    }

    public function testCsrfIsDisabledForStoryblokModule()
    {
        $this->closureMock->expects($this->never())->method('__invoke');

        $this->requestMock
            ->expects($this->once())
            ->method('getModuleName')
            ->willReturn('storyblok');

        $this->csrfValidatorSkip->aroundValidate(
            $this->csrfValidatorMock,
            \Closure::fromCallable($this->closureMock),
            $this->requestMock,
            $this->actionMock
        );
    }
}
