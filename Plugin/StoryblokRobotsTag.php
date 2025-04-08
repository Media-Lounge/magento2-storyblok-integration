<?php

declare(strict_types=1);

namespace MediaLounge\Storyblok\Plugin;

use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Result\Page;
use MediaLounge\Storyblok\Controller\Index\Index as StoryblokIndex;

class StoryblokRobotsTag
{
    public function __construct(
        private readonly Config $pageConfig
    ) {}

    public function afterExecute(
        StoryblokIndex $subject,
                       $result
    ) {
        if ($result instanceof Page) {
            $currentRobots = $this->pageConfig->getRobots();

            $story = $subject->getRequest()->getParam('story');

            // Your logic to determine the robots value
            $newRobots = $this->determineRobotsValue($story);

            if ($newRobots !== $currentRobots) {
                $this->pageConfig->setRobots($newRobots);
            }
        }

        return $result;
    }

    private function determineRobotsValue(array $story): string
    {
        $index = 'INDEX';
        $follow = 'FOLLOW';

        // Example logic - customize this according to your needs
        if (array_key_exists('no_index', $story['content']) && $story['content']['no_index']) {
            $index = 'NOINDEX';
        }

        if (array_key_exists('no_follow', $story['content']) && $story['content']['no_follow']) {
            $follow = 'NOFOLLOW';
        }

        // Default to current robots value if no conditions match
        return "$index,$follow";
    }
}
