<?php
namespace MediaLounge\Storyblok\Test\Unit\Model\ItemProvider;

use PHPUnit\Framework\TestCase;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use MediaLounge\Storyblok\Model\ItemProvider\StoryConfigReader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StoryConfigReaderTest extends TestCase
{
    const STORE_ID = 1;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoryConfigReader
     */
    private $storyConfigReader;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->storyConfigReader = $objectManagerHelper->getObject(StoryConfigReader::class, [
            'scopeConfig' => $this->scopeConfigMock,
        ]);
    }

    public function testGetPriority()
    {
        $this->scopeConfigMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->with(
                StoryConfigReader::XML_PATH_PRIORITY,
                ScopeInterface::SCOPE_STORE,
                self::STORE_ID
            );

        $this->storyConfigReader->getPriority(self::STORE_ID);
    }

    public function testGetChangeFrequency()
    {
        $this->scopeConfigMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->with(
                StoryConfigReader::XML_PATH_CHANGE_FREQUENCY,
                ScopeInterface::SCOPE_STORE,
                self::STORE_ID
            );

        $this->storyConfigReader->getChangeFrequency(self::STORE_ID);
    }
}
