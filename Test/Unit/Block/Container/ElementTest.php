<?php
namespace MediaLounge\Storyblok\Test\Unit\Block\Container;

use Magento\Framework\Escaper;
use PHPUnit\Framework\TestCase;
use Storyblok\RichtextRender\Resolver;
use PHPUnit\Framework\MockObject\MockObject;
use Storyblok\RichtextRender\ResolverFactory;
use MediaLounge\Storyblok\Block\Container\Element;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ElementTest extends TestCase
{
    /**
     * @var Resolver|MockObject
     */
    private $storybookResolverMock;

    /**
     * @var ResolverFactory|MockObject
     */
    private $storybookResolverFactoryMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->storybookResolverMock = $this->createMock(Resolver::class);
        $this->storybookResolverFactoryMock = $this->getMockBuilder(ResolverFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->storybookResolverFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->storybookResolverMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testRenderIndividualBlock()
    {
        $block = $this->objectManagerHelper->getObject(Element::class, [
            'data' => ['_editable' => '<!-- editable -->'],
            'storyblokResolver' => $this->storybookResolverFactoryMock,
        ]);

        $this->assertEquals('<!-- editable -->', $block->toHtml());
    }

    public function testTransformImage()
    {
        $block = $this->objectManagerHelper->getObject(Element::class, [
            'storyblokResolver' => $this->storybookResolverFactoryMock,
        ]);

        $actual = 'https://a.storyblok.com/f/133456/800x600/21312312a123/image_800x600.jpg';
        $expected =
            '//img2.storyblok.com/750x0/filters:format(webp)/f/133456/800x600/21312312a123/image_800x600.jpg';

        $this->assertEquals(
            $expected,
            $block->transformImage($actual, '750x0/filters:format(webp)')
        );
    }

    public function testRenderWysiwyg()
    {
        $escaperMock = $this->createMock(Escaper::class);
        $escaperMock
            ->expects($this->any())
            ->method('escapeHtmlAttr')
            ->willReturnArgument(0);
        $contextMock = $this->createMock(Context::class);
        $contextMock
            ->expects($this->once())
            ->method('getEscaper')
            ->willReturn($escaperMock);

        $block = $this->objectManagerHelper->getObject(Element::class, [
            'context' => $contextMock,
            'storyblokResolver' => $this->storybookResolverFactoryMock,
        ]);

        $fixtureStoryArray = require __DIR__ . '../../../_files/story_with_richtext_field.php';
        $fixtureStoryRendered = file_get_contents(
            __DIR__ . '../../../_files/story_with_richtext_field_rendered.html'
        );

        $this->storybookResolverMock
            ->expects($this->once())
            ->method('render')
            ->with($fixtureStoryArray['story']['content']['body'][0]['content'])
            ->willReturn($fixtureStoryRendered);

        $this->assertEquals(
            $fixtureStoryRendered,
            $block->renderWysiwyg($fixtureStoryArray['story']['content']['body'][0]['content'])
        );
    }
}
