<?php
namespace MediaLounge\Storyblok\Block\Container;

class Element extends \Magento\Framework\View\Element\Template
{
    protected function _toHtml(): string
    {
        $editable = $this->getData('_editable') ?? '';

        return $editable . parent::_toHtml();
    }

    protected function buildAttrFromArray(array $array): string
    {
        $attributes = [];

        foreach ($array as $attribute => $data) {
            $attributes[] = $attribute . '="' . htmlspecialchars($data) . '"';
        }

        return $attributes ? ' ' . implode(' ', $attributes) : '';
    }

    protected function textMarks(array $marks, string $content): string
    {
        foreach ($marks as $mark) {
            $attr = [];

            switch ($mark['type']) {
                case 'bold':
                    $content = '<strong>' . $content . '</strong>';
                    break;
                case 'italic':
                    $content = '<i>' . $content . '</i>';
                    break;
                case 'strike':
                    $content = '<s>' . $content . '</s>';
                    break;
                case 'underline':
                    $content = '<u>' . $content . '</u>';
                    break;
                case 'link':
                    $attr['href'] = $mark['attrs']['href'];
                    $attr['target'] = $mark['attrs']['target'];
                    $content = '<a' . $this->buildAttrFromArray($attr) . '>' . $content . '</a>';
                    break;
            }
        }

        return $content;
    }

    public function renderWysiwyg(array $arrContent): string
    {
        $content = '';
        $element = false;
        $type = isset($arrContent['type']) ? $arrContent['type'] : false;
        $innerContent = isset($arrContent['content']) ? $arrContent['content'] : false;
        $attr = [];
        $selfClose = false;

        switch ($type) {
            case 'doc':
                $attr['class'] = 'storyblok-wysiwyg';
                $element = 'div';
                break;

            case 'text':
                $content = $arrContent['text'];

                if (isset($arrContent['marks'])) {
                    $content = $this->textMarks($arrContent['marks'], $content);
                }
                break;

            case 'heading':
                $element = 'h' . $arrContent['attrs']['level'];
                break;

            case 'paragraph':
                $element = 'p';
                break;

            case 'bullet_list':
                $element = 'ul';
                break;

            case 'ordered_list':
                $element = 'ol';
                break;

            case 'list_item':
                $element = 'li';
                break;

            case 'code_block':
                $element = 'pre';
                break;

            case 'blockquote':
                $element = 'blockquote';
                break;

            case 'horizontal_rule':
                $element = 'hr';
                $selfClose = true;
                break;

            case 'image':
                $element = 'img';
                $attr['src'] = $arrContent['attrs']['src'];
                $attr['alt'] = isset($arrContent['attrs']['alt'])
                    ? $arrContent['attrs']['alt']
                    : '';
                $attr['title'] = isset($arrContent['attrs']['title'])
                    ? $arrContent['attrs']['title']
                    : '';
                $selfClose = true;
                break;

            default:
                $element = 'span';
        }

        if ($innerContent) {
            foreach ($innerContent as $innerContentArray) {
                $content .= $this->renderWysiwyg($innerContentArray);
            }
        }

        if ($element && !$selfClose) {
            $content =
                '<' .
                $element .
                $this->buildAttrFromArray($attr) .
                '>' .
                $content .
                '</' .
                $element .
                '>';
        } elseif ($element) {
            $content = '<' . $element . $this->buildAttrFromArray($attr) . '/>';
        }

        return $content;
    }

    public function transformImage(string $image, string $param = ''): string
    {
        $imageService = '//img2.storyblok.com/';
        $resource = preg_replace('/(https?:)?\/\/a.storyblok.com/', '', $image);

        return $imageService . $param . $resource;
    }
}
