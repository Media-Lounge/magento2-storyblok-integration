<?php
namespace MediaLounge\Storyblok\Block;

use Storyblok\ApiException;
use Magento\Framework\View\FileSystem;
use Storyblok\Client as StoryblokClient;
use Magento\Framework\View\Element\AbstractBlock;
use MediaLounge\Storyblok\Block\Container\Element;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template\Context;

class Container extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    /**
     * @var StoryblokClient
     */
    private $storyblokClient;

    /**
     * @var FileSystem
     */
    private $viewFileSystem;

    public function __construct(FileSystem $viewFileSystem, Context $context, array $data = [])
    {
        parent::__construct($context, $data);

        $this->viewFileSystem = $viewFileSystem;
    }

    public function getCacheLifetime(): int
    {
        return parent::getCacheLifetime() ?: 3600;
    }

    public function getIdentities(): array
    {
        if ($this->getStory()['id']) {
            return ['storyblok_' . $this->getStory()['id']];
        }

        return [];
    }

    public function getCacheKeyInfo(): array
    {
        $info = parent::getCacheKeyInfo();
        $info[] = 'storyblok_' . $this->getData('story')['id'];

        return $info;
    }

    private function getStory(): array
    {
        if (!$this->getData('story')) {
            try {
                $storyblokClient = $this->getClient()->getStoryBySlug($this->getSlug());
                $data = $storyblokClient->getBody();

                $this->setData('story', $data['story']);
            } catch (ApiException $e) {
                return [];
            }
        }

        return $this->getData('story');
    }

    private function getClient(): StoryblokClient
    {
        if (!$this->storyblokClient) {
            $this->storyblokClient = new StoryblokClient(
                $this->_scopeConfig->getValue('storyblok/general/api_key')
            );
        }

        return $this->storyblokClient;
    }

    private function isArrayOfBlocks(array $data): bool
    {
        return count($data) !== count($data, COUNT_RECURSIVE);
    }

    private function createBlockFromData(array $blockData): Element
    {
        $block = $this->getLayout()
            ->createBlock('MediaLounge\Storyblok\Block\Container\Element', $blockData['_uid'])
            ->setData($blockData);

        $templatePath = $this->viewFileSystem->getTemplateFileName(
            "MediaLounge_Storyblok::story/{$blockData['component']}.phtml"
        );

        if (file_exists($templatePath)) {
            $block->setTemplate("MediaLounge_Storyblok::story/{$blockData['component']}.phtml");
        } else {
            $block
                ->setTemplate('MediaLounge_Storyblok::story/debug.phtml')
                ->setOriginalTemplate(
                    "MediaLounge_Storyblok::story/{$blockData['component']}.phtml"
                );
        }

        $this->appendChildBlocks($block, $blockData);

        return $block;
    }

    private function appendChildBlocks(AbstractBlock $parentBlock, array $blockData): void
    {
        foreach ($blockData as $data) {
            if (is_array($data) && $this->isArrayOfBlocks($data)) {
                foreach ($data as $childData) {
                    // Ignore if rich text editor block
                    if (empty($childData['_uid'])) {
                        continue;
                    }

                    $childBlock = $this->createBlockFromData($childData);

                    $parentBlock->append($childBlock);
                }
            }
        }
    }

    protected function _toHtml(): string
    {
        $storyData = $this->getStory();

        if ($storyData) {
            $blockData = $storyData['content'] ?? [];
            $parentBlock = $this->createBlockFromData($blockData);

            return $parentBlock->toHtml();
        }

        return '';
    }
}
