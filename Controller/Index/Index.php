<?php
namespace MediaLounge\Storyblok\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;
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

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->pageFactory->create();
        $resultPage
            ->getConfig()
            ->getTitle()
            ->set($story['name']);

        $resultPage
            ->getLayout()
            ->getBlock('storyblok.page')
            ->setStory($story);

        return $resultPage;
    }
}
