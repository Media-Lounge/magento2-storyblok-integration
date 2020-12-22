<?php
namespace MediaLounge\Storyblok\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\CsrfValidator;

class CsrfValidatorSkip
{
    public function aroundValidate(
        CsrfValidator $subject,
        \Closure $proceed,
        RequestInterface $request,
        ActionInterface $action
    ): void {
        if ($request->getModuleName() === 'storyblok') {
            return;
        }

        $proceed($request, $action);
    }
}
