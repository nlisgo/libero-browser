<?php

declare(strict_types=1);

namespace Libero\JatsContentBundle\ViewConverter;

use FluentDOM\DOM\Element;
use Libero\ViewsBundle\Views\ConvertsChildren;
use Libero\ViewsBundle\Views\SimplifiedVisitor;
use Libero\ViewsBundle\Views\View;
use Libero\ViewsBundle\Views\ViewConverter;
use Libero\ViewsBundle\Views\ViewConverterVisitor;

final class HeadingVisitor implements ViewConverterVisitor
{
    use ConvertsChildren;
    use SimplifiedVisitor;

    public function __construct(ViewConverter $converter)
    {
        $this->converter = $converter;
    }

    protected function doVisit(Element $object, View $view, array &$context = []) : View
    {
        if (isset($context['level'])) {
            $view = $view->withArgument('level', $context['level']);
        }

        return $view->withArgument('text', $this->convertChildren($object, $context));
    }

    protected function expectedTemplate() : string
    {
        return '@LiberoPatterns/heading.html.twig';
    }

    protected function expectedElement() : array
    {
        return [
            '{http://jats.nlm.nih.gov}article-title',
            '{http://jats.nlm.nih.gov}title',
        ];
    }

    protected function unexpectedArguments() : array
    {
        return ['text'];
    }
}
