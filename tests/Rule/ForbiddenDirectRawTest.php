<?php

namespace FriendsOfTwig\Twigcs\Tests\Rule;

use FriendsOfTwig\Twigcs\Lexer;
use FriendsOfTwig\Twigcs\Rule\ForbiddenDirectRaw;
use FriendsOfTwig\Twigcs\TwigPort\Source;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;

class ForbiddenDirectRawTest extends TestCase
{
    /**
     * @dataProvider provideSourcePatterns
     */
    public function testCheck(TokenStream $tokenStream, int $expectedCount) : void
    {
        $rule = new ForbiddenDirectRaw(Violation::SEVERITY_WARNING);
        $violations = $rule->check($tokenStream);

        $this->assertCount($expectedCount, $violations);
    }

    public static function provideSourcePatterns() : array
    {
        $lexer = new Lexer();

        return [
            'without escape' => [
                $lexer->tokenize(
                    new Source(
                        "<script type=\"text/javascript\"> var foo = {{ json | raw }}; </script> <span title={{ bar | raw }}></span>",
                        'my/path/file.html.twig',
                        'my/path/file.html.twig'
                    )
                ),
                2
            ],
            'escaped - not double-escaped' => [
                $lexer->tokenize(
                    new Source(
                        "<script type=\"text/javascript\"> var foo = {{ json | escape('js')|raw }}; </script> <span title={{ bar | e('html_attr') | raw  }}></span>",
                        'my/path/file.html.twig',
                        'my/path/file.html.twig'
                    )
                ),
                0
            ]
        ];
    }
}
