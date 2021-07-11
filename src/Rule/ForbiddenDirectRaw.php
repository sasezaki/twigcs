<?php

namespace FriendsOfTwig\Twigcs\Rule;


use FriendsOfTwig\Twigcs\TwigPort\Token;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class ForbiddenDirectRaw extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(TokenStream $tokens)
    {
        $violations = [];

        $punctuation = false;
        $escape = false;

        do {
            $token = $tokens->getCurrent();

            if (Token::PUNCTUATION_TYPE === $token->getType()) {
                $punctuation = true;
                continue;
            }

            if (Token::BLOCK_END_TYPE === $token->getType()) {
                $punctuation = false;
                $escape = false;
                continue;
            }

            if (!$punctuation) {
                continue;
            }

            if (Token::NAME_TYPE !== $token->getType()) {
                continue;
            }

            if (in_array($token->getValue(), ['escape', 'e'], true)) {
                $escape = true;
                continue;
            }

            if ($escape) {
                continue;
            }

            if ('raw' === $token->getValue()) {
                $violations[] = $this->createViolation(
                    $tokens->getSourceContext()->getPath(),
                    $token->getLine(),
                    $token->getColumn(),
                    'raw filter is used without escape'
                );
            }
        } while (!$tokens->isEOF() && $tokens->next());

        return $violations;
    }
}