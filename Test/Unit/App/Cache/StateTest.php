<?php
namespace MediaLounge\Storyblok\Test\Unit\App\Cache;

use PHPUnit\Framework\TestCase;
use MediaLounge\Storyblok\App\Cache\State;
use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Cache\Type as CacheType;
use Magento\Framework\App\Request\Http as Request;
use Magento\PageCache\Model\Cache\Type as FullPageCache;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StateTest extends TestCase
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var Json|MockObject
     */
    private $jsonMock;

    /**
     * @var Request|MockObject
     */
    private $requestMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var Writer|MockObject
     */
    private $writerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var array
     */
    private $cacheTypes = [CacheType\Block::TYPE_IDENTIFIER, FullPageCache::TYPE_IDENTIFIER];

    protected function setUp(): void
    {
        $this->jsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writerMock = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock->method('getConfigData')->willReturn([
            CacheType\Block::TYPE_IDENTIFIER => 1,
            FullPageCache::TYPE_IDENTIFIER => 1
        ]);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testCacheIsEnabledOutsideStoryblok()
    {
        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getContent')
            ->willReturn(false);

        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getParam')
            ->with('_storyblok')
            ->willReturn(false);

        $this->state = $this->objectManagerHelper->getObject(State::class, [
            'json' => $this->jsonMock,
            'request' => $this->requestMock,
            'config' => $this->deploymentConfigMock,
            'writer' => $this->writerMock
        ]);

        foreach ($this->cacheTypes as $cacheType) {
            $this->assertTrue($this->state->isEnabled($cacheType));
        }
    }

    public function testCacheIsDisabledInStoryblok()
    {
        $storyId = '123456';

        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getContent')
            ->willReturn(false);

        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getParam')
            ->with('_storyblok')
            ->willReturn($storyId);

        $this->state = $this->objectManagerHelper->getObject(State::class, [
            'json' => $this->jsonMock,
            'request' => $this->requestMock,
            'config' => $this->deploymentConfigMock,
            'writer' => $this->writerMock
        ]);

        foreach ($this->cacheTypes as $cacheType) {
            $this->assertFalse($this->state->isEnabled($cacheType));
        }
    }

    public function testCacheIsDisabledInStoryblokOnAjaxRequests()
    {
        $fixtureStory = file_get_contents(__DIR__ . '../../../_files/story_with_blocks.json');
        $fixtureStoryArray = require __DIR__ . '../../../_files/story_with_blocks.php';

        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getContent')
            ->willReturn($fixtureStory);

        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getHeader')
            ->with('Content-Type')
            ->willReturn('application/json');

        $this->jsonMock
            ->expects($this->atLeastOnce())
            ->method('unserialize')
            ->with($fixtureStory)
            ->willReturn($fixtureStoryArray);

        $this->state = $this->objectManagerHelper->getObject(State::class, [
            'json' => $this->jsonMock,
            'request' => $this->requestMock,
            'config' => $this->deploymentConfigMock,
            'writer' => $this->writerMock
        ]);

        foreach ($this->cacheTypes as $cacheType) {
            $this->assertFalse($this->state->isEnabled($cacheType));
        }
    }
}
