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

/**
 * @phpstan-type StatementInfo array{
 *     category: string,
 *     subject: string|null,
 *     subjectParts: list<string>,
 *     multiline: bool,
 *     chained: bool,
 *     valueSubject: string|null,
 *     valueFunction: string|null,
 *     function: string|null,
 *     uses: array<string, 'object'|'argument'>
 * }
 */
final class StatementGroupingFixer extends AbstractFixer
{
    use IndentationFixerTrait;

    private const ASSIGNMENT = 'assignment';

    private const VARIABLE_CALL = 'variable_call';

    private const FUNCTION_CALL = 'function_call';

    private const STATIC_CALL = 'static_call';

    private const DEPRECATION = 'deprecation';

    private const ASSERTION = 'assertion';

    private const NEUTRAL = 'neutral';

    /**
     * Function calls that do not need an empty line before them.
     */
    private const ATTACHED_FUNCTIONS = ['unset', 'parse_str', 'parse_url'];

    private const ASSIGNMENT_OPERATORS = [
        T_AND_EQUAL,
        T_COALESCE_EQUAL,
        T_CONCAT_EQUAL,
        T_DIV_EQUAL,
        T_MINUS_EQUAL,
        T_MOD_EQUAL,
        T_MUL_EQUAL,
        T_OR_EQUAL,
        T_PLUS_EQUAL,
        T_POW_EQUAL,
        T_SL_EQUAL,
        T_SR_EQUAL,
        T_XOR_EQUAL,
    ];

    /**
     * Tokens that start a statement which is not a control structure or a declaration, so a
     * closing curly brace (e.g. of a closure) does not end the statement.
     */
    private const EXPRESSION_START_KINDS = [
        T_VARIABLE,
        T_STRING,
        T_NS_SEPARATOR,
        T_STATIC,
        T_LIST,
        CT::T_DESTRUCTURING_BRACKET_OPEN,
    ];

    /**
     * Statements of the same group are not separated by an empty line.
     */
    private const GROUPS = [
        self::ASSIGNMENT => 'variable',
        self::VARIABLE_CALL => 'variable',
        self::FUNCTION_CALL => 'call',
        self::STATIC_CALL => 'call',
        self::ASSERTION => 'assertion',
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Statements that belong together must not be separated by an empty line, statements that do not belong together must be separated by an empty line.',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php

                        use PHPUnit\Framework\TestCase;

                        class SomeTest extends TestCase
                        {
                            public function testFoo(): void
                            {
                                $mock = $this->createMock(Foo::class);
                                $mock
                                    ->method('isFoo')
                                    ->willReturn(true)
                                ;

                                $mock
                                    ->method('isBar')
                                    ->willReturn(false)
                                ;
                                $mock->isFoo();
                            }
                        }
                        EOT,
                ),
                new CodeSample(
                    <<<'EOT'
                        <?php

                        $request = new Request();
                        $requestStack = new RequestStack();
                        $requestStack->push($request);
                        $environment = new Environment();
                        $hasScope = false;
                        EOT,
                ),
                new CodeSample(
                    <<<'EOT'
                        <?php

                        $request = new Request();
                        $environment = new Environment();
                        trigger_deprecation('contao/core-bundle', '5.0', 'Foo is deprecated.');
                        System::loadLanguageFile('default');
                        self::bootKernel();
                        unset($foo);
                        $hasScope = false;
                        EOT,
                ),
                new CodeSample(
                    <<<'EOT'
                        <?php

                        $operation = $operations[$name];
                        $this->assertSame('/files/{pathOrUuid}', $operation->getUriTemplate());
                        $this->assertSame(['pathOrUuid' => '.+'], $operation->getRequirements());
                        $link = $operation->getUriVariables()['pathOrUuid'];
                        $this->assertSame(['path'], $link->getIdentifiers());
                        EOT,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([T_VARIABLE, T_STRING, T_UNSET]);
    }

    /**
     * Must run after MultilineCallArgumentsFixer. Must run before
     * StatementIndentationFixer.
     */
    public function getPriority(): int
    {
        return -2;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        // Every frame represents a block; only curly brace blocks (and the top-level
        // code) contain statements.
        $stack = [];
        $frame = ['list' => true, 'start' => null, 'statements' => []];

        for ($index = 0, $count = \count($tokens); $index < $count; ++$index) {
            $token = $tokens[$index];

            if ($blockType = Tokens::detectBlockType($token)) {
                if ($blockType['isStart']) {
                    if ($frame['list'] && null === $frame['start']) {
                        $frame['start'] = $index;
                    }

                    $stack[] = $frame;

                    $frame = [
                        'list' => Tokens::BLOCK_TYPE_BRACE === $blockType['type'],
                        'start' => null,
                        'statements' => [],
                    ];
                } elseif (null !== ($parent = array_pop($stack))) {
                    if ($frame['list']) {
                        $this->fixStatements($tokens, $frame['statements']);
                    }

                    $frame = $parent;
                    $start = $frame['start'];

                    // The closing brace ends a control structure or declaration
                    if ($token->equals('}') && $frame['list'] && null !== $start && !$tokens[$start]->isGivenKind(self::EXPRESSION_START_KINDS)) {
                        $frame['start'] = null;
                    }
                }

                continue;
            }

            if (!$frame['list'] || $token->isWhitespace() || $token->isComment()) {
                continue;
            }

            if ($token->equals(';')) {
                if (null !== $frame['start']) {
                    $frame['statements'][] = [$frame['start'], $index];
                    $frame['start'] = null;
                }

                continue;
            }

            if ($token->isGivenKind([T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG, T_INLINE_HTML])) {
                $frame['start'] = null;
                continue;
            }

            if (null === $frame['start']) {
                $frame['start'] = $index;
                continue;
            }

            // The colon ends a case label
            if ($token->equals(':') && $tokens[$frame['start']]->isGivenKind([T_CASE, T_DEFAULT])) {
                $frame['start'] = null;
            }
        }

        $this->fixStatements($tokens, $frame['statements']);
    }

    /**
     * @param list<array{0: int, 1: int}> $statements
     */
    private function fixStatements(Tokens $tokens, array $statements): void
    {
        // A single statement is not compared with other statements, but the empty lines
        // after a closing curly brace and around comments are still fixed
        if ([] === $statements) {
            return;
        }

        $count = \count($statements);

        $info = [];
        $adjacent = [];
        $related = [];
        $argumentOnly = [];

        foreach ($statements as $key => [$start, $end]) {
            $info[$key] = $this->analyzeStatement($tokens, $start, $end);
        }

        for ($key = 0; $key < $count - 1; ++$key) {
            $end = $statements[$key][1];
            $nextStart = $statements[$key + 1][0];

            // Only directly adjacent statements on separate lines are compared
            $adjacent[$key] = $nextStart === $end + 2 && $this->isLineBreak($tokens[$end + 1]);
        }

        for ($key = 0; $key < $count - 1; ++$key) {
            // The statement after B, if it is directly adjacent
            $c = $adjacent[$key + 1] ?? false ? $info[$key + 2] : null;

            $related[$key] = $adjacent[$key] && $this->isRelated($info[$key], $info[$key + 1], $c);
            $argumentOnly[$key] = $adjacent[$key] && !$related[$key] && $this->isBrokenByArgumentUse($info[$key], $info[$key + 1], $c);
        }

        for ($key = 0; $key < $count - 1; ++$key) {
            if (!$adjacent[$key]) {
                continue;
            }

            $a = $info[$key];
            $b = $info[$key + 1];

            if (self::NEUTRAL === $a['category'] || self::NEUTRAL === $b['category']) {
                continue;
            }

            $inGroup = ($related[$key - 1] ?? false) || ($related[$key + 1] ?? false);

            // Shared use that is only broken because B is used as an argument does not
            // separate A and B from the group
            if ($this->needsEmptyLine($a, $b, $related[$key], $inGroup && !$argumentOnly[$key])) {
                $this->setEmptyLine($tokens, $statements[$key][1] + 1, true);
            } elseif ($this->removesEmptyLine($a, $b, $related[$key])) {
                $this->setEmptyLine($tokens, $statements[$key][1] + 1, false);
            }
        }

        foreach ($statements as $key => [$start]) {
            if (self::NEUTRAL === $info[$key]['category']) {
                continue;
            }

            // A statement after a closing curly brace (e.g. of a control structure)
            if ($this->isLineBreak($tokens[$start - 1]) && $tokens[$start - 2]->equals('}')) {
                $this->setEmptyLine($tokens, $start - 1, true);
            }

            $this->fixComments($tokens, $start);
        }
    }

    /**
     * Removes empty lines between related statements only, and only if B is a
     * chained-call block on the same subject as A (e.g. "$mock = …;" and a "$mock"
     * chain) or if A is an assignment and A and B are both single-line. Multi-line
     * statements keep their empty line.
     *
     * @param StatementInfo $a
     * @param StatementInfo $b
     */
    private function removesEmptyLine(array $a, array $b, bool $related): bool
    {
        if (!$related) {
            return false;
        }

        if ($b['chained'] && null !== $a['subject'] && $a['subject'] === $b['subject']) {
            return true;
        }

        return self::ASSIGNMENT === $a['category'] && !$a['multiline'] && !$b['multiline'];
    }

    /**
     * Adds an empty line before the comments preceding a statement and removes the
     * empty line between the comments and the statement.
     */
    private function fixComments(Tokens $tokens, int $start): void
    {
        if (!$this->isLineBreak($tokens[$start - 1]) || !$tokens[$start - 2]->isComment()) {
            return;
        }

        $last = $start - 2;
        $first = $last;

        // Consecutive comment lines count as one comment
        while (
            $tokens[$first - 1]->isWhitespace()
            && 1 === substr_count($tokens[$first - 1]->getContent(), "\n")
            && $tokens[$first - 2]->isComment()
            && $this->isLineBreak($tokens[$first - 3]) // stop at a comment at the end of a line of code
        ) {
            $first -= 2;
        }

        // Do not touch comments at the end of a line of code
        if (!$this->isLineBreak($tokens[$first - 1])) {
            return;
        }

        $prev = $tokens->getPrevMeaningfulToken($first);

        // Do not touch file header comments
        if (null === $prev || $tokens[$prev]->isGivenKind(T_OPEN_TAG) || $this->isDeclareStatementEnd($tokens, $prev)) {
            return;
        }

        $this->setEmptyLine($tokens, $last + 1, false);

        if ($tokens[$prev]->equals(';') || $tokens[$prev]->equals('}')) {
            $this->setEmptyLine($tokens, $first - 1, true);
        }
    }

    private function isDeclareStatementEnd(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->equals(';')) {
            return false;
        }

        $close = $tokens->getPrevMeaningfulToken($index);

        if (null === $close || !$tokens[$close]->equals(')')) {
            return false;
        }

        $open = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $close);
        $keyword = $tokens->getPrevMeaningfulToken($open);

        return null !== $keyword && $tokens[$keyword]->isGivenKind(T_DECLARE);
    }

    /**
     * @param StatementInfo $a
     * @param StatementInfo $b
     */
    private function needsEmptyLine(array $a, array $b, bool $related, bool $inGroup): bool
    {
        // Rule 1: related statements
        if ($related) {
            return false;
        }

        // Rule 2: multi-line statements
        if ($a['multiline'] || $b['multiline']) {
            return true;
        }

        // Rule 3: deprecations
        if (self::DEPRECATION === $a['category'] || self::DEPRECATION === $b['category']) {
            return true;
        }

        // Rule 3a: unset(), parse_str() and parse_url()
        if (self::FUNCTION_CALL === $b['category'] && \in_array($b['function'], self::ATTACHED_FUNCTIONS, true)) {
            return false;
        }

        // Rule 3b: a variable that is declared and then used in a function or static call
        if ($this->isUsedInCall($a, $b)) {
            return false;
        }

        // Rule 3c: the value of a variable is the result of a function that is called again
        if (self::ASSIGNMENT === $a['category'] && self::FUNCTION_CALL === $b['category'] && null !== $a['valueFunction'] && $a['valueFunction'] === $b['function']) {
            return false;
        }

        // Rule 4: groups of related statements
        if ($inGroup) {
            return true;
        }

        // Rule 5: assignments and calls stay with a following assertion
        if (self::ASSERTION === $b['category'] && \in_array($a['category'], [self::ASSIGNMENT, self::VARIABLE_CALL, self::FUNCTION_CALL, self::STATIC_CALL], true)) {
            return false;
        }

        // Rule 6: statements of the same group stay together
        return !isset(self::GROUPS[$a['category']]) || self::GROUPS[$a['category']] !== (self::GROUPS[$b['category']] ?? null);
    }

    /**
     * @param StatementInfo      $a
     * @param StatementInfo      $b
     * @param StatementInfo|null $c
     */
    private function isRelated(array $a, array $b, array|null $c = null): bool
    {
        if (null === $a['subject']) {
            return false;
        }

        // Declaration and usage, e.g. "$mock = …;" and "$mock->foo();" (also if the
        // declaration is multi-line), or the same object, e.g. two "$mock" chains
        if ($a['subject'] === $b['subject']) {
            return !$a['multiline'] || $b['chained'] || (self::ASSIGNMENT === $a['category'] && self::VARIABLE_CALL === $b['category']);
        }

        // A property or key of the object, e.g. "$mock->id = 42;" and "$mock", or
        // "$links = …;" and "$links['move'] = …;" (only if both are single-line)
        if ($this->isSameObject($a, $b) && ((!$a['multiline'] && !$b['multiline']) || $b['chained'])) {
            return true;
        }

        // Direct use (only if the declaration is single-line), unless a new block begins
        // with B
        if (self::ASSIGNMENT === $a['category'] && self::ASSIGNMENT === $b['category'] && !$a['multiline'] && $a['subject'] === $b['valueSubject']) {
            return null === $c || !$this->isSameObject($b, $c);
        }

        // Shared use, unless a new block begins with B
        return $this->sharesCall($a, $b) && null === $this->getNextUse($b, $c);
    }

    /**
     * Checks whether A and B share the same variable, but are not related because B
     * is used in the next statement only as an argument (not as an object).
     *
     * @param StatementInfo      $a
     * @param StatementInfo      $b
     * @param StatementInfo|null $c
     */
    private function isBrokenByArgumentUse(array $a, array $b, array|null $c): bool
    {
        return $this->sharesCall($a, $b) && 'argument' === $this->getNextUse($b, $c);
    }

    /**
     * Checks whether A and B are single-line assignments whose values begin with a method
     * call on the same variable, e.g. "$a = $config->getA();" and "$b = $config->getB();".
     *
     * @param StatementInfo $a
     * @param StatementInfo $b
     */
    private function sharesCall(array $a, array $b): bool
    {
        return self::ASSIGNMENT === $a['category']
            && self::ASSIGNMENT === $b['category']
            && !$a['multiline']
            && !$b['multiline']
            && null !== $a['valueSubject']
            && '$this' !== $a['valueSubject']
            && $a['valueSubject'] === $b['valueSubject'];
    }

    /**
     * Returns how the variable assigned in B is used in the next statement: as an
     * "object" (subject or method call target), as an "argument" or not at all.
     *
     * @param StatementInfo      $b
     * @param StatementInfo|null $c
     *
     * @return 'object'|'argument'|null
     */
    private function getNextUse(array $b, array|null $c): string|null
    {
        if (null === $c || null === $b['subject'] || !isset($c['uses'][$b['subject']])) {
            return null;
        }

        if ($c['subject'] === $b['subject']) {
            return 'object';
        }

        return $c['uses'][$b['subject']];
    }

    /**
     * Checks whether B is a function or static call that uses the variable assigned
     * in A, e.g. "$container = …;" and "System::setContainer($container);".
     *
     * @param StatementInfo $a
     * @param StatementInfo $b
     */
    private function isUsedInCall(array $a, array $b): bool
    {
        if (self::ASSIGNMENT !== $a['category'] || !\in_array($b['category'], [self::FUNCTION_CALL, self::STATIC_CALL], true)) {
            return false;
        }

        return null !== $a['subject'] && isset($b['uses'][$a['subject']]);
    }

    /**
     * Checks whether two different subjects belong to the same object.
     *
     * @param StatementInfo $a
     * @param StatementInfo $b
     */
    private function isSameObject(array $a, array $b): bool
    {
        $subjectA = $a['subject'];
        $subjectB = $b['subject'];

        if (null === $subjectB) {
            return false;
        }

        // Do not relate all statements starting with "$this->" (the subject is "$this"
        // if a property is accessed via a method call, e.g. "$this->getFoo()->bar = 1;")
        if ('$this' === $subjectA || '$this' === $subjectB) {
            return false;
        }

        // Compare complete access parts to find the common object or array, even when
        // the assignments target different nested keys.
        $commonParts = [];

        foreach ($a['subjectParts'] as $key => $part) {
            if (($b['subjectParts'][$key] ?? null) !== $part) {
                break;
            }

            $commonParts[] = $part;
        }

        // Different top-level globals and different properties of $this are not related
        // merely because their root variable is the same.
        return [] !== $commonParts && !\in_array(implode('', $commonParts), ['$GLOBALS', '$this'], true);
    }

    /**
     * @return StatementInfo
     */
    private function analyzeStatement(Tokens $tokens, int $start, int $end): array
    {
        // Whitespace and comments are irrelevant for recognizing deprecation calls.
        $code = '';

        for ($i = $start; $i <= $end; ++$i) {
            $current = $tokens[$i];

            if ($current->isWhitespace() || $current->isComment()) {
                continue;
            }

            $code .= $current->getContent();
        }

        $info = [
            'category' => self::NEUTRAL,
            'subject' => null,
            'subjectParts' => [],
            'multiline' => str_contains($tokens->generatePartialCode($start, $end), "\n"),
            'chained' => false,
            'valueSubject' => null,
            'valueFunction' => null,
            'function' => null,
            'uses' => $this->getVariableUses($tokens, $start, $end),
        ];

        // Silenced statements, e.g. "@trigger_error(…);"
        if ($tokens[$start]->equals('@')) {
            $start = $tokens->getNextMeaningfulToken($start);

            if (null === $start) {
                return $info;
            }
        }

        $token = $tokens[$start];

        if ($token->isGivenKind(T_VARIABLE)) {
            // Closure call, e.g. "$fn()"
            $next = $tokens->getNextMeaningfulToken($start);

            if (null !== $next && $tokens[$next]->equals('(')) {
                $info['category'] = self::FUNCTION_CALL;

                return $info;
            }

            return $this->analyzeVariableStatement($tokens, $start, '', $info);
        }

        if ($token->isGivenKind(T_UNSET)) {
            $info['category'] = self::FUNCTION_CALL;
            $info['function'] = 'unset';

            return $info;
        }

        if ($token->isGivenKind([CT::T_DESTRUCTURING_BRACKET_OPEN, T_LIST])) {
            $next = $tokens->getNextMeaningfulToken($start);

            if (null === $next) {
                return $info;
            }

            $close = $token->isGivenKind(T_LIST)
                ? $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $next)
                : $tokens->findBlockEnd(Tokens::BLOCK_TYPE_DESTRUCTURING_BRACKET, $start);

            $next = $tokens->getNextMeaningfulToken($close);

            if (null !== $next && $tokens[$next]->equals('=')) {
                $info['category'] = self::ASSIGNMENT;
            }

            return $info;
        }

        if (!$token->isGivenKind([T_STRING, T_NS_SEPARATOR, T_STATIC])) {
            return $info;
        }

        // Read the (possibly namespaced) name
        $index = $start;
        $name = $token->getContent();

        while (($next = $tokens->getNextMeaningfulToken($index)) && $tokens[$next]->isGivenKind([T_STRING, T_NS_SEPARATOR])) {
            $index = $next;
            $name = $tokens[$index]->getContent();
        }

        $next = $tokens->getNextMeaningfulToken($index);

        if (null === $next) {
            return $info;
        }

        // Function call
        if ($tokens[$next]->equals('(') && !$token->isGivenKind(T_STATIC)) {
            $info['function'] = strtolower($name);
            $info['category'] = $this->isDeprecation($info['function'], $code) ? self::DEPRECATION : self::FUNCTION_CALL;

            return $info;
        }

        if (!$tokens[$next]->isGivenKind(T_DOUBLE_COLON)) {
            return $info;
        }

        $member = $tokens->getNextMeaningfulToken($next);

        if (null === $member) {
            return $info;
        }

        // Static property
        if ($tokens[$member]->isGivenKind(T_VARIABLE)) {
            return $this->analyzeVariableStatement($tokens, $member, $name.'::', $info);
        }

        $arguments = $tokens->getNextMeaningfulToken($member);

        if (!$tokens[$member]->isGivenKind(T_STRING) || null === $arguments || !$tokens[$arguments]->equals('(')) {
            return $info;
        }

        if (\in_array(strtolower($name), ['self', 'static'], true) && str_starts_with($tokens[$member]->getContent(), 'assert')) {
            $info['category'] = self::ASSERTION;
        } else {
            $info['category'] = self::STATIC_CALL;
        }

        return $info;
    }

    /**
     * @param StatementInfo $info
     *
     * @return StatementInfo
     */
    private function analyzeVariableStatement(Tokens $tokens, int $index, string $prefix, array $info): array
    {
        $variable = $index;
        $hasCall = false;
        $chained = false;

        while (true) {
            $next = $tokens->getNextMeaningfulToken($index);

            if (null === $next) {
                return $info;
            }

            if ($tokens[$next]->isObjectOperator() || $tokens[$next]->isGivenKind(T_DOUBLE_COLON)) {
                if ($tokens[$next]->isObjectOperator() && $this->isLineBreak($tokens[$next - 1])) {
                    $chained = true;
                }

                $index = $tokens->getNextMeaningfulToken($next);

                if (null === $index) {
                    return $info;
                }

                if ($blockType = Tokens::detectBlockType($tokens[$index])) {
                    $index = $tokens->findBlockEnd($blockType['type'], $index);
                }

                continue;
            }

            if ($tokens[$next]->equals('(')) {
                $hasCall = true;
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $next);
                continue;
            }

            if ($tokens[$next]->equals('[')) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_INDEX_BRACKET, $next);
                continue;
            }

            break;
        }

        if ($tokens[$next]->equals('=') || $tokens[$next]->isGivenKind(self::ASSIGNMENT_OPERATORS)) {
            $info['category'] = self::ASSIGNMENT;
            $info['subjectParts'] = $this->getSubjectParts($tokens, $variable);
            $info['subjectParts'][0] = $prefix.$info['subjectParts'][0];
            $info['subject'] = implode('', $info['subjectParts']);
            $info['valueSubject'] = $this->getCallSubject($tokens, $tokens->getNextMeaningfulToken($next));
            $info['valueFunction'] = $this->getCallFunction($tokens, $tokens->getNextMeaningfulToken($next));

            return $info;
        }

        if (!$hasCall) {
            return $info;
        }

        $operator = $tokens->getNextMeaningfulToken($variable);

        if (null !== $operator && $tokens[$operator]->isGivenKind(T_DOUBLE_COLON)) {
            $member = $tokens->getNextMeaningfulToken($operator);

            $arguments = null !== $member ? $tokens->getNextMeaningfulToken($member) : null;

            if (null !== $arguments && $tokens[$arguments]->equals('(')) {
                $info['category'] = self::STATIC_CALL;

                return $info;
            }
        }

        if ('' === $prefix && $this->isAssertion($tokens, $variable)) {
            $info['category'] = self::ASSERTION;

            return $info;
        }

        $parts = $this->getSubjectParts($tokens, $variable);
        $parts[0] = $prefix.$parts[0];

        $subject = implode('', $parts);

        // A method call on $this, e.g. "$this->iniSet()", is a function call
        if ('$this' === $subject) {
            $info['category'] = self::FUNCTION_CALL;

            return $info;
        }

        $info['category'] = self::VARIABLE_CALL;
        $info['subject'] = $subject;
        $info['subjectParts'] = $parts;
        $info['chained'] = $chained;

        return $info;
    }

    /**
     * Returns the variable and, if it is followed by a property access, the property,
     * e.g. "$this->foo" for "$this->foo->bar()". Array keys are part of the subject,
     * e.g. "$GLOBALS['TL_DCA']['tl_content']".
     *
     * @return list<string>
     */
    private function getSubjectParts(Tokens $tokens, int &$index): array
    {
        $parts = [$tokens[$index]->getContent(), ...$this->getArrayKeys($tokens, $index)];
        $operator = $tokens->getNextMeaningfulToken($index);

        if (null === $operator || !$tokens[$operator]->isObjectOperator()) {
            return $parts;
        }

        $property = $tokens->getNextMeaningfulToken($operator);

        if (null === $property || !$tokens[$property]->isGivenKind(T_STRING)) {
            return $parts;
        }

        $next = $tokens->getNextMeaningfulToken($property);

        if (null !== $next && $tokens[$next]->equals('(')) {
            return $parts;
        }

        $index = $property;

        return [...$parts, '->'.$tokens[$property]->getContent(), ...$this->getArrayKeys($tokens, $index)];
    }

    /**
     * Records actual variable accesses and their prefixes. String keys remain part of
     * an access but are never searched for variable names. Interpolated variables are
     * T_VARIABLE tokens and are therefore included.
     *
     * @return array<string, 'object'|'argument'>
     */
    private function getVariableUses(Tokens $tokens, int $start, int $end): array
    {
        $uses = [];

        for ($i = $start; $i <= $end; ++$i) {
            if (!$tokens[$i]->isGivenKind(T_VARIABLE)) {
                continue;
            }

            $index = $i;
            $parts = $this->getSubjectParts($tokens, $index);
            $prev = $tokens->getPrevMeaningfulToken($i);

            // In Foo::$property the variable token names a static property, whereas in
            // Foo::$method() it reads the local variable naming the method.
            $next = $tokens->getNextMeaningfulToken($i);

            if (null !== $prev && $tokens[$prev]->isGivenKind(T_DOUBLE_COLON) && (null === $next || !$tokens[$next]->equals('('))) {
                $class = $tokens->getPrevMeaningfulToken($prev);

                if (null === $class) {
                    continue;
                }

                $prefix = $tokens[$class]->getContent();

                while (null !== ($previous = $tokens->getPrevMeaningfulToken($class)) && $tokens[$previous]->isGivenKind(T_NS_SEPARATOR)) {
                    $prefix = $tokens[$previous]->getContent().$prefix;
                    $class = $previous;
                    $previous = $tokens->getPrevMeaningfulToken($class);

                    if (null !== $previous && $tokens[$previous]->isGivenKind(T_STRING)) {
                        $prefix = $tokens[$previous]->getContent().$prefix;
                        $class = $previous;
                    }
                }

                $parts[0] = $prefix.'::'.$parts[0];
            }

            $next = $tokens->getNextMeaningfulToken($index);
            $subject = '';

            foreach ($parts as $key => $part) {
                $subject .= $part;

                // An append target is not a readable variable access.
                if ('[]' === $part) {
                    continue;
                }

                $object = isset($parts[$key + 1])
                    ? str_starts_with($parts[$key + 1], '->')
                    : null !== $next && ($tokens[$next]->isObjectOperator() || $tokens[$next]->isGivenKind(T_DOUBLE_COLON));

                if ($object || !isset($uses[$subject])) {
                    $uses[$subject] = $object ? 'object' : 'argument';
                }
            }
        }

        return $uses;
    }

    /**
     * Returns the array keys following the given token as separate parts (without
     * whitespace) and moves the index to the last closing bracket.
     *
     * @return list<string>
     */
    private function getArrayKeys(Tokens $tokens, int &$index): array
    {
        $keys = [];

        while (true) {
            $next = $tokens->getNextMeaningfulToken($index);

            if (null === $next) {
                return $keys;
            }

            if (!$tokens[$next]->equals('[')) {
                return $keys;
            }

            $end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_INDEX_BRACKET, $next);
            $key = '';

            for ($i = $next; $i <= $end; ++$i) {
                if (!$tokens[$i]->isWhitespace() && !$tokens[$i]->isComment()) {
                    $key .= $tokens[$i]->getContent();
                }
            }

            $keys[] = $key;
            $index = $end;
        }
    }

    /**
     * Returns the subject if the expression begins with a method call, e.g.
     * "$serializer" for "$serializer->normalize()" or "$items['foo']" for
     * "$items['foo']->getValue()". Leading type casts do not change the subject of
     * the call.
     */
    private function getCallSubject(Tokens $tokens, int|null $index): string|null
    {
        while (null !== $index && $tokens[$index]->isCast()) {
            $index = $tokens->getNextMeaningfulToken($index);
        }

        if (null === $index || !$tokens[$index]->isGivenKind(T_VARIABLE)) {
            return null;
        }

        $subject = $tokens[$index]->getContent().implode('', $this->getArrayKeys($tokens, $index));

        for ($level = 0; $level < 2; ++$level) {
            $operator = $tokens->getNextMeaningfulToken($index);

            if (null === $operator || !$tokens[$operator]->isObjectOperator()) {
                return null;
            }

            $index = $tokens->getNextMeaningfulToken($operator);

            if (null === $index || !$tokens[$index]->isGivenKind(T_STRING)) {
                return null;
            }

            $next = $tokens->getNextMeaningfulToken($index);

            if (null !== $next && $tokens[$next]->equals('(')) {
                return $subject;
            }

            $subject .= '->'.$tokens[$index]->getContent().implode('', $this->getArrayKeys($tokens, $index));
        }

        return null;
    }

    /**
     * Returns the function name if the expression begins with a function call, e.g.
     * "mb_substitute_character" for "mb_substitute_character()".
     */
    private function getCallFunction(Tokens $tokens, int|null $index): string|null
    {
        if (null !== $index && $tokens[$index]->isGivenKind(T_NS_SEPARATOR)) {
            $index = $tokens->getNextMeaningfulToken($index);
        }

        if (null === $index || !$tokens[$index]->isGivenKind(T_STRING)) {
            return null;
        }

        $next = $tokens->getNextMeaningfulToken($index);

        if (null === $next || !$tokens[$next]->equals('(')) {
            return null;
        }

        return strtolower($tokens[$index]->getContent());
    }

    /**
     * Checks whether the function call is "trigger_deprecation(…)" or
     * "trigger_error(…, E_USER_DEPRECATED)".
     */
    private function isDeprecation(string $function, string $code): bool
    {
        if ('trigger_deprecation' === $function) {
            return true;
        }

        return 'trigger_error' === $function && 1 === preg_match('/,\\\\?E_USER_DEPRECATED,?\);$/', $code);
    }

    private function isAssertion(Tokens $tokens, int $index): bool
    {
        if ('$this' !== $tokens[$index]->getContent()) {
            return false;
        }

        $operator = $tokens->getNextMeaningfulToken($index);

        if (null === $operator || !$tokens[$operator]->isObjectOperator()) {
            return false;
        }

        $method = $tokens->getNextMeaningfulToken($operator);

        if (null === $method || !$tokens[$method]->isGivenKind(T_STRING)) {
            return false;
        }

        $arguments = $tokens->getNextMeaningfulToken($method);

        if (null === $arguments || !$tokens[$arguments]->equals('(')) {
            return false;
        }

        $name = $tokens[$method]->getContent();

        return str_starts_with($name, 'assert') || str_starts_with($name, 'expect');
    }

    private function setEmptyLine(Tokens $tokens, int $index, bool $emptyLine): void
    {
        $content = $tokens[$index]->getContent();
        $lineBreaks = substr_count($content, "\n");
        $indent = substr($content, (int) strrpos($content, "\n") + 1);

        if ($emptyLine && $lineBreaks < 2) {
            $tokens->offsetSet($index, new Token([T_WHITESPACE, "\n\n".$indent]));
        } elseif (!$emptyLine && $lineBreaks > 1) {
            $tokens->offsetSet($index, new Token([T_WHITESPACE, "\n".$indent]));
        }
    }
}
