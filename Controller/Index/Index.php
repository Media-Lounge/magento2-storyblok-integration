<?php
namespace MediaLounge\Storyblok\Controller\Index;

use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(Context $context, PageFactory $pageFactory)
    {
        parent::__construct($context);

        $this->pageFactory = $pageFactory;
    }

    public function execute(): ResultInterface
    {
        $story = $this->getRequest()->getParam('story', null);

        if (!$story) {
            throw new NotFoundException(__('Story parameter is missing.'));
        }

        /** @var Page $resultPage */
        $resultPage = $this->pageFactory->create();
        $resultPage = $this->setMetaFields($resultPage, $story);

        $resultPage
            ->getLayout()
            ->getBlock('storyblok.page')
            ->setStory($story);

        return $resultPage;
    }

    private function setMetaFields(Page $resultPage, array $story)
    {
        $metaTitle = '';
        $metaDescription = '';

        foreach ($story['content'] as $data) {
            if (is_array($data) && $this->isMetaFieldsBlock($data)) {
                $metaTitle = $data['title'];
                $metaDescription = $data['description'];
            }
        }

        if ($metaTitle) {
            $resultPage
                ->getConfig()
                ->getTitle()
                ->set($metaTitle);
        } else {
            $resultPage
                ->getConfig()
                ->getTitle()
                ->set($story['name']);
        }

        if ($metaDescription) {
            $resultPage->getConfig()->setDescription($metaDescription);
        }

        return $resultPage;
    }

    private function isMetaFieldsBlock(array $data)
    {
        return !empty($data['plugin']) && $data['plugin'] === 'meta-fields';
    }
}
