<?php
namespace MediaLounge\Storyblok\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use MediaLounge\Storyblok\Block\Container;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Ajax extends Action implements HttpPostActionInterface
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    public function __construct(Context $context, Json $json, JsonFactory $resultJsonFactory)
    {
        parent::__construct($context);

        $this->json = $json;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute(): ResultInterface
    {
        $postContent = $this->json->unserialize($this->getRequest()->getContent());
        $story = $postContent['story'] ?? null;

        $layout = $this->_view->getLayout();
        $result = $this->resultJsonFactory->create();

        $layout->getUpdate()->addHandle('storyblok_index_ajax');

        $block = $layout
            ->createBlock(Container::class)
            ->setStory($story)
            ->toHtml();

        return $result->setData([$story['content']['_uid'] => $block]);
    }
}
