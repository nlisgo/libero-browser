<?php

declare(strict_types=1);

namespace tests\Libero\JatsContentBundle\ViewConverter;

use FluentDOM;
use FluentDOM\DOM\Element;
use Libero\JatsContentBundle\ViewConverter\ArticleTitleHeadingVisitor;
use Libero\ViewsBundle\Views\View;
use PHPUnit\Framework\TestCase;
use tests\Libero\ContentPageBundle\ViewConvertingTestCase;

final class ArticleTitleHeadingVisitorTest extends TestCase
{
    use ViewConvertingTestCase;

    /**
     * @test
     * @dataProvider nodeProvider
     */
    public function it_does_nothing_if_it_is_not_a_jats_article_title_element(string $xml) : void
    {
        $visitor = new ArticleTitleHeadingVisitor($this->createFailingConverter());

        $xml = FluentDOM::load("<foo>${xml}</foo>");
        /** @var Element $element */
        $element = $xml->documentElement->firstChild;

        $newContext = [];
        $view = $visitor->visit($element, new View('@LiberoPatterns/heading.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    public function nodeProvider() : iterable
    {
        yield 'different namespace' => ['<article-title xmlns="http://example.com">foo</article-title>'];
        yield 'different element' => ['<foo xmlns="http://jats.nlm.nih.gov">foo</foo>'];
    }

    /**
     * @test
     */
    public function it_does_nothing_if_is_not_the_heading_template() : void
    {
        $visitor = new ArticleTitleHeadingVisitor($this->createFailingConverter());

        $xml = FluentDOM::load('<article-title xmlns="http://jats.nlm.nih.gov">foo</article-title>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit($element, new View('template'), $newContext);

        $this->assertSame('template', $view->getTemplate());
        $this->assertEmpty($view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_there_is_already_text_set() : void
    {
        $visitor = new ArticleTitleHeadingVisitor($this->createFailingConverter());

        $xml = FluentDOM::load('<article-title xmlns="http://jats.nlm.nih.gov">foo</article-title>');
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = [];
        $view = $visitor->visit(
            $element,
            new View('@LiberoPatterns/heading.html.twig', ['text' => 'bar']),
            $newContext
        );

        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertSame(['text' => 'bar'], $view->getArguments());
        $this->assertEmpty($newContext);
    }

    /**
     * @test
     */
    public function it_sets_the_text_argument() : void
    {
        $visitor = new ArticleTitleHeadingVisitor($this->createDumpingConverter());

        $xml = FluentDOM::load(
            <<<XML
<jats:article-title xmlns:jats="http://jats.nlm.nih.gov">
    foo <jats:italic>bar</jats:italic> baz <jats:bold>bold</jats:bold>
</jats:article-title>
XML
        );
        /** @var Element $element */
        $element = $xml->documentElement;

        $newContext = ['qux' => 'quux'];
        $view = $visitor->visit($element, new View('@LiberoPatterns/heading.html.twig'), $newContext);

        $this->assertSame('@LiberoPatterns/heading.html.twig', $view->getTemplate());
        $this->assertEquals(
            [
                'text' => [
                    new View(
                        null,
                        [
                            'node' => '/jats:article-title/text()[1]',
                            'template' => null,
                            'context' => ['qux' => 'quux'],
                        ]
                    ),
                    new View(
                        null,
                        [
                            'node' => '/jats:article-title/jats:italic',
                            'template' => null,
                            'context' => ['qux' => 'quux'],
                        ]
                    ),
                    new View(
                        null,
                        [
                            'node' => '/jats:article-title/text()[2]',
                            'template' => null,
                            'context' => ['qux' => 'quux'],
                        ]
                    ),
                    new View(
                        null,
                        [
                            'node' => '/jats:article-title/jats:bold',
                            'template' => null,
                            'context' => ['qux' => 'quux'],
                        ]
                    ),
                    new View(
                        null,
                        [
                            'node' => '/jats:article-title/text()[3]',
                            'template' => null,
                            'context' => ['qux' => 'quux'],
                        ]
                    ),
                ],
            ],
            $view->getArguments()
        );
        $this->assertSame(['qux' => 'quux'], $newContext);
    }
}
