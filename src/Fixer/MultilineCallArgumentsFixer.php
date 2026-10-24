<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class MultilineCallArgumentsFixer extends AbstractFixer
{
    use IndentationFixerTrait;

    /**
     * Methods for which the "multi-line array as second of two arguments" exception
     * does not apply.
     */
    private const METHODS_WITHOUT_ARRAY_EXCEPTION = [
        'with',
        'withConsecutive',
        'withAnyParameters',
    ];

    /**
     * Calls which are ignored completely.
     */
    private const IGNORED_METHODS = [
        'ArrayUtil::arrayInsert',
    ];

    /**
     * Method calls which are ignored completely, because they are handled by
     * the SingleLineConfigureCommandFixer.
     */
    private const IGNORED_OBJECT_METHODS = [
        'addArgument',
        'addOption',
    ];

    /**
     * Tokens whose parentheses are not an argument list.
     */
    private const SKIPPED_KINDS = [
        T_IF,
        T_ELSEIF,
        T_WHILE,
        T_FOR,
        T_FOREACH,
        T_SWITCH,
        T_MATCH,
        T_FUNCTION,
        T_FN,
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Multi-line calls with two or more arguments must have one argument per line and multi-line arrays must have one element per line.',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php

                        $factory = new DataContainerSchemaFactory($this->createContaoFrameworkStub([
                            Controller::class => $controller,
                            System::class => $this->createAdapterStub(['loadLanguageFile']),
                        ]), $this->converters, $this->relationResolver, $this->localeSwitcher, $this->createRelationDefinitionFactory($this->converters));
                        EOT,
                ),
                new CodeSample(
                    <<<'EOT'
                        <?php

                        $record = new DataContainerRecord('tl_content', ['rows' => [
                            (object) ['file' => new RelationReference('file-id', '/files/file-id')],
                            new \stdClass(),
                        ]], 17);
                        EOT,
                ),
                new CodeSample(
                    <<<'EOT'
                        <?php

                        $foo = ['foo' => [
                            'bar' => 'Bar',
                            'baz' => 'Baz',
                        ]];
                        EOT,
                ),
                new CodeSample(
                    <<<'EOT'
                        <?php

                        myFunction($foo, [
                            "foo" => "Foo",
                            "bar" => "Bar",
                        ]);
                        EOT,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_STRING, T_STATIC, T_VARIABLE, T_CLASS, T_FUNCTION, T_FN, T_CONSTANT_ENCAPSED_STRING, CT::T_ARRAY_BRACKET_OPEN]);
    }

    /**
     * Must run before MultiLineLambdaFunctionArgumentsFixer, StatementGroupingFixer
     * and StatementIndentationFixer.
     */
    public function getPriority(): int
    {
        return -1;
    }

    /**
     * The lists are processed from the outside in. A list is only changed if it
     * contains a line break, so the enclosing list has already been processed and
     * changing a list never changes whether a nested list is multi-line. Therefore,
     * one pass is enough.
     */
    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 0; $index < \count($tokens); ++$index) {
            $token = $tokens[$index];

            if ($token->isGivenKind(T_ATTRIBUTE)) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ATTRIBUTE, $index);
                continue;
            }

            if ($token->isGivenKind(T_START_HEREDOC)) {
                $index = (int) $tokens->getNextTokenOfKind($index, [[T_END_HEREDOC]]);
                continue;
            }

            if ($token->equals('"')) {
                $index = (int) $tokens->getNextTokenOfKind($index, ['"']);
                continue;
            }

            if ($token->isGivenKind(self::SKIPPED_KINDS)) {
                if ($end = $this->getSkippedParenthesesEnd($tokens, $index)) {
                    $index = $end;
                }

                continue;
            }

            if ($token->isGivenKind(CT::T_ARRAY_BRACKET_OPEN)) {
                $this->fixArray($tokens, $index, $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_BRACKET, $index));
                continue;
            }

            if ($token->equals('(') && $this->isCall($tokens, $index)) {
                $this->fixCall($tokens, $index, $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $index), $this->getCallName($tokens, $index));
            }
        }
    }

    /**
     * Returns the end of the parentheses following a control structure keyword or a
     * function declaration, so they can be skipped.
     */
    private function getSkippedParenthesesEnd(Tokens $tokens, int $index): int|null
    {
        $next = $tokens->getNextMeaningfulToken($index);

        if (null === $next) {
            return null;
        }

        if ($tokens[$index]->isGivenKind(T_FUNCTION)) {
            if ($tokens[$next]->isGivenKind(CT::T_RETURN_REF)) {
                $next = $tokens->getNextMeaningfulToken($next);

                if (null === $next) {
                    return null;
                }
            }

            if ($tokens[$next]->isGivenKind(T_STRING)) {
                $next = $tokens->getNextMeaningfulToken($next);
            }
        }

        if (null === $next || !$tokens[$next]->equals('(')) {
            return null;
        }

        return $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $next);
    }

    /**
     * Determines whether the parenthesis opens a call, including calls whose target
     * is a dynamic property, an array element or a parenthesized expression.
     */
    private function isCall(Tokens $tokens, int $index): bool
    {
        $prev = $tokens->getPrevMeaningfulToken($index);

        if (null === $prev) {
            return false;
        }

        $prevPrev = $tokens->getPrevMeaningfulToken($prev);

        if ($tokens[$prev]->isGivenKind(T_STATIC)) {
            return null !== $prevPrev && $tokens[$prevPrev]->isGivenKind(T_NEW);
        }

        if ($tokens[$prev]->isGivenKind(T_CLASS)) {
            return null !== $prevPrev && $tokens[$prevPrev]->isGivenKind([T_NEW, T_READONLY]);
        }

        if ($tokens[$prev]->equals(')')) {
            $open = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $prev);
            $keyword = $tokens->getPrevMeaningfulToken($open);

            return null === $keyword || !$tokens[$keyword]->isGivenKind(self::SKIPPED_KINDS);
        }

        if ($tokens[$prev]->isGivenKind([T_VARIABLE, CT::T_DYNAMIC_PROP_BRACE_CLOSE, CT::T_DYNAMIC_VAR_BRACE_CLOSE]) || ']' === $tokens[$prev]->getContent()) {
            return true;
        }

        return $tokens[$prev]->isGivenKind(T_STRING) && (null === $prevPrev || !$tokens[$prevPrev]->isGivenKind([T_FUNCTION, CT::T_RETURN_REF]));
    }

    /**
     * Returns the statically known name used for name-dependent exceptions.
     */
    private function getCallName(Tokens $tokens, int $index): string|null
    {
        $prev = $tokens->getPrevMeaningfulToken($index);

        if (null === $prev || !$tokens[$prev]->isGivenKind(T_STRING)) {
            return null;
        }

        $name = $tokens[$prev]->getContent();
        $prevPrev = $tokens->getPrevMeaningfulToken($prev);

        if (null !== $prevPrev && $tokens[$prevPrev]->isGivenKind(T_DOUBLE_COLON)) {
            $class = $tokens->getPrevMeaningfulToken($prevPrev);

            if (null === $class) {
                return null;
            }

            return $tokens[$class]->getContent().'::'.$name;
        }

        if (null !== $prevPrev && $tokens[$prevPrev]->isObjectOperator()) {
            return '->'.$name;
        }

        return $name;
    }

    private function fixCall(Tokens $tokens, int $open, int $close, string|null $name): void
    {
        if (!$this->isMultiline($tokens, $open, $close)) {
            return;
        }

        if (\in_array($name, self::IGNORED_METHODS, true)) {
            return;
        }

        if (null !== $name && str_starts_with($name, '->') && \in_array(substr($name, 2), self::IGNORED_OBJECT_METHODS, true)) {
            return;
        }

        if (null === ($elements = $this->getElements($tokens, $open, $close, $hasTrailingComma, $hasHeredoc))) {
            return;
        }

        // A heredoc/nowdoc argument always stands on its own line, even if it is the
        // only argument
        if ($hasHeredoc) {
            $this->expand($tokens, $open, $close, $elements, $hasTrailingComma, true);

            return;
        }

        // A call with only one argument is never split
        if (\count($elements) < 2) {
            return;
        }

        if ($this->isSecondArgumentArrayException($tokens, $elements, $name)) {
            $this->collapse($tokens, $open, $close, $elements);

            return;
        }

        $this->expand($tokens, $open, $close, $elements, $hasTrailingComma, true);
    }

    private function fixArray(Tokens $tokens, int $open, int $close): void
    {
        if (!$this->isMultiline($tokens, $open, $close)) {
            return;
        }

        if (null === ($elements = $this->getElements($tokens, $open, $close, $hasTrailingComma))) {
            return;
        }

        if ([] === $elements) {
            return;
        }

        // An array whose only element is an array that directly follows the opening
        // bracket is not expanded, e.g. [[ … ]]
        if (
            $tokens[$open + 1]->isGivenKind(CT::T_ARRAY_BRACKET_OPEN)
            && $close - 1 === $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_BRACKET, $open + 1)
        ) {
            return;
        }

        $this->expand($tokens, $open, $close, $elements, $hasTrailingComma);
    }

    /**
     * Returns the elements of a list as [start, end] pairs of their first and last
     * meaningful token or null if the list cannot be handled safely.
     *
     * @param-out bool $hasTrailingComma
     * @param-out bool $hasHeredoc
     *
     * @return list<array{int, int}>|null
     */
    private function getElements(Tokens $tokens, int $open, int $close, bool|null &$hasTrailingComma = null, bool|null &$hasHeredoc = null): array|null
    {
        $hasTrailingComma = false;
        $hasHeredoc = false;
        $segments = [];
        $segmentStart = $open + 1;

        // Do not touch lists containing inline HTML (also in nested blocks, e.g. in the
        // body of a closure)
        for ($i = $open + 1; $i < $close; ++$i) {
            if ($tokens[$i]->isGivenKind([T_INLINE_HTML, T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG])) {
                return null;
            }
        }

        for ($i = $open + 1; $i < $close; ++$i) {
            $token = $tokens[$i];

            // A heredoc/nowdoc on the top level of an element
            if ($token->isGivenKind(T_START_HEREDOC)) {
                $hasHeredoc = true;
                $i = (int) $tokens->getNextTokenOfKind($i, [[T_END_HEREDOC]]);
                continue;
            }

            if ($blockType = Tokens::detectBlockType($token)) {
                if ($blockType['isStart']) {
                    $i = $tokens->findBlockEnd($blockType['type'], $i);
                }

                continue;
            }

            if ($token->equals(',')) {
                $segments[] = [$segmentStart, $i - 1];
                $segmentStart = $i + 1;
            }
        }

        $segments[] = [$segmentStart, $close - 1];
        $elements = [];
        $lastSegment = \count($segments) - 1;

        foreach ($segments as $key => [$start, $end]) {
            while ($start <= $end && $tokens[$start]->isWhitespace()) {
                ++$start;
            }

            // Empty segment
            if ($start > $end) {
                // A trailing comma (or an empty list)
                if ($key === $lastSegment) {
                    $hasTrailingComma = $key > 0;
                    continue;
                }

                return null;
            }

            while ($tokens[$end]->isWhitespace()) {
                --$end;
            }

            // Do not touch lists with comments between the elements
            if ($tokens[$start]->isComment() || $tokens[$end]->isComment()) {
                return null;
            }

            $elements[] = [$start, $end];
        }

        return $elements;
    }

    private function isMultiline(Tokens $tokens, int $open, int $close): bool
    {
        for ($i = $open; $i <= $close; ++$i) {
            if (str_contains($tokens[$i]->getContent(), "\n")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Puts every element on its own line, adds a trailing comma and puts the closing
     * bracket on its own line.
     *
     * @param list<array{int, int}> $elements
     */
    private function expand(Tokens $tokens, int $open, int $close, array $elements, bool $hasTrailingComma, bool $compactArrays = false): void
    {
        if ([] === $elements) {
            return;
        }

        $indent = $this->getLineIndent($tokens, $open);
        $innerIndent = $indent.'    ';

        // Put the closing bracket on its own line
        if ($tokens[$close - 1]->isWhitespace()) {
            $tokens->offsetSet(
                $close - 1,
                new Token([T_WHITESPACE, $this->getLineBreaks($tokens[$close - 1]).$indent]),
            );
        } else {
            $tokens->insertAt($close, new Token([T_WHITESPACE, "\n".$indent]));
        }

        // Add the trailing comma
        if (!$hasTrailingComma) {
            $tokens->insertAt(end($elements)[1] + 1, new Token(','));
        }

        foreach (array_reverse($elements) as [$start, $end]) {
            // An array with only one single-line member is written in a compact form
            if (!$compactArrays || !$this->compactArray($tokens, $start, $end)) {
                $delta = \strlen($innerIndent) - \strlen($this->getLineIndent($tokens, $start));
                $this->shiftIndentation($tokens, $start, $end, $delta);
            }

            if ($tokens[$start - 1]->isWhitespace()) {
                $tokens->offsetSet(
                    $start - 1,
                    new Token([T_WHITESPACE, $this->getLineBreaks($tokens[$start - 1]).$innerIndent]),
                );
            } else {
                $tokens->insertAt($start, new Token([T_WHITESPACE, "\n".$innerIndent]));
            }
        }

        $tokens->clearEmptyTokens();
    }

    /**
     * Converts a multi-line array with only one single-line member to a single line,
     * e.g. ['foo' => 'bar'].
     */
    private function compactArray(Tokens $tokens, int $start, int $end): bool
    {
        if ($tokens[$start]->isGivenKind(T_ELLIPSIS) || !$this->isMultilineArray($tokens, $start, $end)) {
            return false;
        }

        $members = $this->getElements($tokens, $start, $end);

        if (null === $members || 1 !== \count($members)) {
            return false;
        }

        [[$memberStart, $memberEnd]] = $members;

        if (str_contains($tokens->generatePartialCode($memberStart, $memberEnd), "\n")) {
            return false;
        }

        for ($i = $start + 1; $i < $memberStart; ++$i) {
            $tokens->clearAt($i);
        }

        for ($i = $memberEnd + 1; $i < $end; ++$i) {
            $tokens->clearAt($i);
        }

        return true;
    }

    /**
     * Checks whether the call has two arguments, the first one is simple and the
     * second one is a multi-line array, e.g. myFunction($foo, [ … ]).
     *
     * @param list<array{int, int}> $elements
     */
    private function isSecondArgumentArrayException(Tokens $tokens, array $elements, string|null $name): bool
    {
        $method = null !== $name && str_starts_with($name, '->') ? substr($name, 2) : $name;

        if (\in_array($method, self::METHODS_WITHOUT_ARRAY_EXCEPTION, true)) {
            return false;
        }

        if (2 !== \count($elements)) {
            return false;
        }

        [[$start1, $end1], [$start2, $end2]] = $elements;
        $firstArg = $tokens->generatePartialCode($start1, $end1);

        if (1 === preg_match('/\s/', $firstArg)) {
            return false;
        }

        return $this->isMultilineArray($tokens, $start2, $end2);
    }

    private function isMultilineArray(Tokens $tokens, int $start, int $end): bool
    {
        if ($tokens[$start]->isGivenKind(T_ELLIPSIS)) {
            $start = $tokens->getNextMeaningfulToken($start);
        }

        if (null === $start || !$tokens[$start]->isGivenKind(CT::T_ARRAY_BRACKET_OPEN)) {
            return false;
        }

        if ($tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_BRACKET, $start) !== $end) {
            return false;
        }

        return str_contains($tokens->generatePartialCode($start, $end), "\n");
    }

    /**
     * Converts the call to myFunction($foo, [ … ]).
     *
     * @param list<array{int, int}> $elements
     */
    private function collapse(Tokens $tokens, int $open, int $close, array $elements): void
    {
        [[$start1, $end1], [$start2, $end2]] = $elements;

        if (!$tokens[$end1 + 1]->equals(',')) {
            return;
        }

        // Remove the trailing comma and the line break before the closing parenthesis
        for ($i = $close - 1; $i > $end2; --$i) {
            $tokens->clearAt($i);
        }

        $delta = \strlen($this->getLineIndent($tokens, $open)) - \strlen($this->getLineIndent($tokens, $start2));
        $this->shiftIndentation($tokens, $start2, $end2, $delta);

        // Put the array on the same line as the first argument
        for ($i = $start2 - 1; $i > $end1 + 1; --$i) {
            $tokens->clearAt($i);
        }

        $tokens->insertAt($start2, new Token([T_WHITESPACE, ' ']));

        // Remove the line break after the opening parenthesis
        for ($i = $start1 - 1; $i > $open; --$i) {
            $tokens->clearAt($i);
        }

        $tokens->clearEmptyTokens();
    }

    /**
     * Adds or removes spaces to/from the indentation of every line inside the given range.
     */
    private function shiftIndentation(Tokens $tokens, int $start, int $end, int $delta): void
    {
        if (0 === $delta) {
            return;
        }

        for ($i = $start; $i <= $end; ++$i) {
            if ($tokens[$i]->isGivenKind(T_START_HEREDOC)) {
                $heredocEnd = (int) $tokens->getNextTokenOfKind($i, [[T_END_HEREDOC]]);
                $i = $this->shiftHeredocIndentation($tokens, $i, $delta);

                // Tokens may have been inserted
                $end += $i - $heredocEnd;
                continue;
            }

            if ($i === $start || !$tokens[$i]->isGivenKind(T_WHITESPACE)) {
                continue;
            }

            $content = $tokens[$i]->getContent();

            if (false === ($pos = strrpos($content, "\n"))) {
                continue;
            }

            $lineBreaks = substr($content, 0, $pos + 1);
            $indent = $this->shiftLine(substr($content, $pos + 1), $delta);

            $tokens->offsetSet($i, new Token([T_WHITESPACE, $lineBreaks.$indent]));
        }
    }

    /**
     * Shifts the content and the closing marker of a heredoc/nowdoc, so the value of
     * the string does not change, and returns the index of the closing marker.
     */
    private function shiftHeredocIndentation(Tokens $tokens, int $index, int $delta): int
    {
        $endIndex = (int) $tokens->getNextTokenOfKind($index, [[T_END_HEREDOC]]);
        $endContent = $tokens[$endIndex]->getContent();
        $oldIndentLength = \strlen($endContent) - \strlen(ltrim($endContent, " \t"));

        // Never remove more than the indentation of the closing marker
        if ($delta < 0) {
            $delta = max($delta, -$oldIndentLength);
        }

        if (0 === $delta) {
            return $endIndex;
        }

        // Replace the complete indentation prefix so a tab-indented heredoc never
        // acquires a mixture of tabs and spaces. Content after the prefix is kept.
        $newIndent = str_repeat(' ', $oldIndentLength + $delta);

        // The opening token ends with a line break
        $atLineStart = true;

        for ($i = $index + 1; $i < $endIndex; ++$i) {
            if (!$tokens[$i]->isGivenKind(T_ENCAPSED_AND_WHITESPACE)) {
                // A line which starts with a variable
                if ($atLineStart && '' !== $newIndent) {
                    $tokens->insertAt($i, new Token([T_ENCAPSED_AND_WHITESPACE, $newIndent]));
                    ++$i;
                    ++$endIndex;
                }

                $atLineStart = false;
                continue;
            }

            $content = $tokens[$i]->getContent();
            $lines = explode("\n", $content);

            foreach ($lines as $key => $line) {
                // The first line does not start at the beginning of a line
                if (0 === $key && !$atLineStart) {
                    continue;
                }

                // Empty lines and the beginning of a line that continues in the next token
                if ('' === $line) {
                    continue;
                }

                $whitespace = \strlen($line) - \strlen(ltrim($line, " \t"));
                $lines[$key] = $newIndent.substr($line, min($oldIndentLength, $whitespace));
            }

            $atLineStart = str_ends_with($content, "\n");
            $tokens->offsetSet($i, new Token([T_ENCAPSED_AND_WHITESPACE, implode("\n", $lines)]));
        }

        $tokens->offsetSet($endIndex, new Token([T_END_HEREDOC, $newIndent.substr($endContent, $oldIndentLength)]));

        return $endIndex;
    }

    private function shiftLine(string $line, int $delta): string
    {
        if ($delta > 0) {
            return str_repeat(' ', $delta).$line;
        }

        $whitespace = \strlen($line) - \strlen(ltrim($line, " \t"));

        return substr($line, min(-$delta, $whitespace));
    }

    /**
     * Returns the indentation of the line the given token is on.
     */
    private function getLineIndent(Tokens $tokens, int $index): string
    {
        $indent = $this->getIndent($tokens, $index);

        return substr($indent, (int) strrpos($indent, "\n") + 1);
    }

    /**
     * Returns the line breaks of a whitespace token (at least one).
     */
    private function getLineBreaks(Token $token): string
    {
        return str_repeat("\n", max(1, substr_count($token->getContent(), "\n")));
    }
}
