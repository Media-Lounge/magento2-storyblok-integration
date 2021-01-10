<?php
namespace MediaLounge\Storyblok\Block\Container;

use Storyblok\RichtextRender\Resolver;
use Magento\Framework\View\Element\Template\Context;
use Storyblok\RichtextRender\ResolverFactory as StoryblokResolver;

class Element extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Resolver
     */
    private $storyblokResolver;

    public function __construct(
        StoryblokResolver $storyblokResolver,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->storyblokResolver = $storyblokResolver->create();
    }

    protected function _toHtml(): string
    {
        $editable = $this->getData('_editable') ?? '';

        return $editable . parent::_toHtml();
    }

    public function renderWysiwyg(array $arrContent): string
    {
        return $this->storyblokResolver->render($arrContent);
    }

    public function transformImage(string $image, string $param = ''): string
    {
        $imageService = '//img2.storyblok.com/';
        $resource = preg_replace('/(https?:)?\/\/a.storyblok.com/', '', $image);

        return $imageService . $param . $resource;
    }
}
