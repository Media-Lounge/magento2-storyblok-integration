<?php
namespace MediaLounge\Storyblok\Test\Unit\Block\Container;

use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use MediaLounge\Storyblok\Block\Container\Element;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ElementTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testRenderIndividualBlock()
    {
        $block = $this->objectManagerHelper->getObject(Element::class, [
            'data' => ['_editable' => '<!-- editable -->'],
        ]);

        $this->assertEquals('<!-- editable -->', $block->toHtml());
    }

    public function testTransformImage()
    {
        $block = $this->objectManagerHelper->getObject(Element::class);

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
        $block = $this->objectManagerHelper->getObject(Element::class);
        $fixtureStoryArray = require __DIR__ . '../../../_files/story_with_richtext_field.php';
        $fixtureStoryRendered = file_get_contents(
            __DIR__ . '../../../_files/story_with_richtext_field_rendered.html'
        );

        $this->assertEquals(
            $fixtureStoryRendered,
            $block->renderWysiwyg($fixtureStoryArray['story']['content']['body'][0]['content'])
        );
    }
}
