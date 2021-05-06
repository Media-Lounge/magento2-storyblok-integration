<?php
/**
 * Copyright © Media Lounge. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace MediaLounge\Storyblok\Block;

use Magento\Framework\View\FileSystem;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

use Storyblok\ApiException;
use Storyblok\Client as StoryblokClient;
use Storyblok\ClientFactory as StoryblokClientFactory;

use MediaLounge\Storyblok\Block\Container\Element;
use MediaLounge\Storyblok\Model\ConfigInterface;

class Container extends Template implements IdentityInterface
{
    /**
     * @var StoryblokClient
     */
    private $storyblokClient;

    /**
     * @var FileSystem
     */
    private $viewFileSystem;

    /**
     * @var ConfigInterface
     */
    private $storyblokConfig;

    public function __construct(
        FileSystem $viewFileSystem,
        StoryblokClientFactory $storyblokClient,
        Template\Context $context,
        ConfigInterface $storyblokConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->viewFileSystem = $viewFileSystem;
        $this->storyblokConfig = $storyblokConfig;
        $this->storyblokClient = $storyblokClient->create([
            'apiKey' => $this->storyblokConfig->getApiKey(),
        ]);
    }

    public function getCacheLifetime(): int
    {
        return parent::getCacheLifetime() ?: 3600;
    }

    public function getIdentities(): array
    {
        if (!empty($this->getData('story')['id'])) {
            return ["storyblok_{$this->getData('story')['id']}"];
        }

        return [];
    }

    public function getCacheKeyInfo(): array
    {
        $info = parent::getCacheKeyInfo();

        if (!empty($this->getData('story')['id'])) {
            $info[] = "storyblok_{$this->getData('story')['id']}";
        }

        return $info;
    }

    private function getStory(): array
    {
        if (!$this->getData('story')) {
            try {
                $storyblokClient = $this->storyblokClient->getStoryBySlug($this->getSlug());
                $data = $storyblokClient->getBody();

                $this->setData('story', $data['story']);
            } catch (ApiException $e) {
                return [];
            }
        }

        return $this->getData('story');
    }

    private function isArrayOfBlocks(array $data): bool
    {
        return count($data) !== count($data, COUNT_RECURSIVE);
    }

    private function createBlockFromData(array $blockData): Element
    {
        $block = $this->getLayout()
            ->createBlock(Element::class, $blockData['_uid'])
            ->setData($blockData);

        $templatePath = $this->viewFileSystem->getTemplateFileName(
            "MediaLounge_Storyblok::story/{$blockData['component']}.phtml"
        );

        if ($templatePath) {
            $block->setTemplate("MediaLounge_Storyblok::story/{$blockData['component']}.phtml");
        } else {
            $block->setTemplate('MediaLounge_Storyblok::story/debug.phtml')->addData([
                'original_template' => "MediaLounge_Storyblok::story/{$blockData['component']}.phtml",
            ]);
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
