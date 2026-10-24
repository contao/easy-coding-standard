# Plan: Rework of the ChainedMethodBlockFixer

This document is the agreed specification. It has been implemented (see "Implementation notes" at the end). Whoever
changes the implementation must follow the rules below exactly; where something is marked as an *assumption*, it has
not been confirmed explicitly and may be challenged, but it must not be changed silently.

## Goals

1. Separate statements that do not belong together with an empty line and remove the empty line between a variable and
   a chained-call block on that variable (mock setup).
2. Keep (and slightly extend) the behavior of the current `ChainedMethodBlockFixer` regarding mock/stub setup.
3. Put every argument of a multi-line call on its own line.
4. Add an empty line after the last assertion of a block.
5. Expand every array that contains a line break completely (one element per line).

## General decisions

- **Never reorder statements.** The fixers only insert or remove empty lines (and line breaks inside argument lists).
  Reordering can change behavior (constructors with side effects, overwritten variables) and must be done manually.
- **Empty lines are inserted and removed selectively.** If the rules require an empty line, it is inserted. An
  existing empty line is only removed in the cases described in "Removing empty lines" (chained-call block on the same
  variable, or two related single-line statements where A is an assignment). Never insert more than one empty line.
- **All other existing empty lines are kept.** See "Removing empty lines" for the exact conditions and examples.
- **Not a line-length fixer.** Single-line statements stay on one line, even if they are longer than 120 characters.
  Line breaks are only added if the code already contains a line break.
- **Applies everywhere:** methods, functions, closures, global code and configuration files such as `../../ecs.php`. Not
  restricted to test classes.
- **Comments:** see the separate "Comments" section for spacing and sequence boundaries.
- **Control structures are neutral.** `if`, `elseif`, `else`, `foreach`, `for`, `while`, `do`, `switch`, `match`
  statements, `try`, `return`, `throw`, `yield`, `break`, `continue`, `echo` and any other statement not matching one
  of the categories below are left alone; the whitespace before them is not changed. **Exception:** a (non-neutral)
  statement directly after the closing `}` of a block (e.g. a control structure) always gets an empty line before it.

## Fixer 1: `StatementGroupingFixer` (new, replaces `ChainedMethodBlockFixer`)

### Scope

- Works on the statements of one block (`{ … }`) or of the top-level code. Nested blocks (closures, control structure
  bodies, …) are processed as their own blocks, including blocks with only one statement.
- Only the whitespace token between the end of statement A (`;`) and the start of statement B is changed. The
  indentation of B is preserved.
- **Only directly adjacent statements are compared.** A comment or a neutral statement between two statements breaks
  the sequence; the statements on either side are not compared with each other.
- Statements on the same line (`$a = 1; $b = 2;`) are not compared. `case` and `default` labels end the preceding
  sequence; statements after the label are compared with each other.

### Statement categories

Every statement gets exactly one category:

| Category          | Examples                                                                                  |
|-------------------|-------------------------------------------------------------------------------------------|
| Assignment        | `$x = …;`, `$this->foo = …;`, `$x[] = …;`, `$x .= …;`                                     |
| Variable call     | `$mock->method(…);`, `$mock` + chained calls, `$this->foo->bar();`                        |
| Function call     | `unset($foo);`, `array_push($a, $b);`, `sprintf(…);`, `$fn(…);`, `$this->foo();`          |
| Static call       | `System::loadLanguageFile('default');`, `self::bootKernel();`, `parent::setUp();`         |
| Deprecation       | `trigger_deprecation(…);`, `@trigger_error(…, E_USER_DEPRECATED);`                        |
| Assertion         | `$this->assert*(…)`, `self::assert*(…)`, `static::assert*(…)`, `$this->expect*(…)`        |
| Neutral           | control structures, `return`, `throw`, … (see above)                                      |

Notes:

- Assertions are recognized by name in every class, not only in classes extending `TestCase`. They are *not* variable
  calls or static calls.
- `$this->expectException()`, `$this->expectExceptionMessage()` etc. count as assertions.
- `trigger_deprecation()` and `trigger_error()` with `E_USER_DEPRECATED` as last argument (with or without `@`, with or
  without a leading `\`) are deprecations and always their own group (see rules). `trigger_error()` with another
  error level is a function call. A trailing comma after `E_USER_DEPRECATED` is allowed.
- A leading `@` is ignored when determining the category (`@unlink($file);` is a function call).
- A closure call (`$fn(…);`, a variable directly followed by `(`) is a function call, not a variable call, and has no
  subject. It is therefore not related to the declaration of the closure.
- A method call directly on `$this` (`$this->foo();`, `$this->foo()->bar();`) is a function call without a subject,
  e.g. `$this->iniSet(…);` → `date_default_timezone_set(…);` stays together (rule 6). A call via a property
  (`$this->foo->bar();`) remains a variable call with the subject `$this->foo`.

### Subject of a statement

- Assignment: the assigned variable, e.g. `$x`, `$this->foo`.
- Array keys are part of the subject: `$GLOBALS['BE_FFL']['customFiles'] = …` has the subject
  `$GLOBALS['BE_FFL']['customFiles']`, `$x[] = …` has the subject `$x[]`. This also applies to method call targets:
  `$items['foo']->getValue()` has the subject `$items['foo']`.
- Variable call: the leading variable. If the variable is followed by a property access, the property is part of the
  subject: `$this->foo->bar()` → `$this->foo`; `$mock->method()` → `$mock`.
- Other categories have no subject.
- Static property assignments have a subject such as `self::$foo`. Destructuring (`[$a, $b] = …;`, `list(…) = …;`)
  is an assignment without a subject. If a property is accessed via a method call (`$this->getFoo()->bar = 1;`), the
  subject is `$this`.

When checking whether a statement uses a variable, non-interpolated string contents are ignored
(`foo('$container')` does not use `$container`); interpolated variables count. String literals forming array keys
remain part of the subject.

### Multi-line statements

A statement is multi-line if its code (from its first token to the `;`) contains a line break, e.g. multi-line arrays,
chained method calls with `->` on separate lines, multi-line calls or closures.

A **chained-call block** is a variable call where at least one `->` or `?->` is preceded by a line break, e.g.:

```php
$mock
    ->method('isFoo')
    ->willReturn(true)
;
```

### When are two adjacent statements A and B "related"?

1. **Declaration and usage:** A is an assignment to `$x` and B has the subject `$x`
   (`$requestStack = new RequestStack();` → `$requestStack->push($request);`). This also applies if A is multi-line
   (`$cron = new Cron(…multi-line…);` → `$cron->addCronJob($job);`; an existing empty line is kept). A multi-line
   assignment followed by another assignment to the same variable is not related.
2. **Same object:** A and B have the same object as subject and A is single-line or B is a chained-call block
   (`$mock` chain → `$mock` chain). A multi-line chained-call block followed by a single-line call on the same object
   (`$mock` chain → `$mock->isFoo();`) is *not* related ("add a new line when it is used"). "Same object" means:
   - the subjects are equal, or
   - one subject is a property or key of the other (`$mock` / `$mock->id`, `$config` / `$config['foo']`), or
   - both subjects are different keys of the same array (`$config['foo']` / `$config['bar']`,
     `$GLOBALS['TL_LANG']['MSC']['foo']` / `$GLOBALS['TL_LANG']['MSC']['bar']`). Top-level keys of `$GLOBALS` are
     not related (`$GLOBALS['BE_FFL'][…]` / `$GLOBALS['TL_DCA'][…]`).
   - both subjects are different properties of the same object (`$page->dateFormat` / `$page->timeFormat`). An
     object is defined and then several properties are set; this stays together. Properties of `$this` are not
     related (`$this->foo` / `$this->bar`).
   - `$this` alone is never the same object as `$this->foo` (otherwise all `$this->` statements would be related).
   - If the subjects are not equal (property or key), A and B must both be single-line or B must be a chained-call
     block: `$links = clone …;` → multi-line `$links['move'] = new Link(…);` is *not* related.
3. **Direct use:** A is a single-line assignment to `$x` and the right-hand side of B (an assignment) begins with a
   method call on `$x` (`$serializer = new Serializer(…);` → `$normalized = $serializer->normalize(…);`). A multi-line
   declaration is not related: `$operation = $operation->withParameters([…]);` → `$operations[$name] = $operation->…`
   gets an empty line (rule 2).
   **A new related group takes precedence over direct use:** if the directly adjacent statement C has the same
   object or array as B's subject (as defined in rule 2 above), A and B are not related by direct use. For example,
   `$payload = $challenge->toArray();` followed by `$payload['number'] = 42;` starts a new group at `$payload`.
   The related-group spacing rule (rule 4 below) inserts or preserves the empty line before this group.
   Merely calling a method on B's variable to assign a different variable does not start such a group:
   `$altcha = …;` → `$challenge = $altcha->createChallenge(…);` → `$payload = $challenge->toArray();` keeps
   `$altcha` and `$challenge` together. Using B's variable only as an argument also leaves direct use intact.
4. **Shared use:** A and B are single-line assignments whose right-hand sides both begin with a method call on the
   same variable (`$projectDir = $parameterBag->resolveValue(…);` → `$uploadDir = $parameterBag->resolveValue(…);`,
   `$defaults = $config->getDefaults();` → `$requirements = $config->getRequirements();`). `$this` does not count
   (`$a = $this->createStub(…);` → `$b = $this->createStub(…);` is not related). Using the variable only inside the
   expression is not enough (`$fileInfoImageSize = new File($image->…)->imageSize;` is not related).
   **A new block takes precedence over shared use:** if the variable assigned in B is used in the directly adjacent
   statement C (as subject, as method call target or as an argument; only the exact variable, `$request` does not
   match `$requestStack`), a new logical block begins with B and A and B are *not* related. This does not make B and C
   related (they may still be related by another rule, e.g. direct use).

```php
// Input
$factory = new MenuFactory();
$system = $factory->createItem('system');
$menu = $factory->createItem('mainMenu');
$menu->addChild($system);
$controller = $this->contaoFramework->getAdapter(Controller::class);
$contentModel = $this->contaoFramework->getAdapter(ContentModel::class);
$template = $this->contaoFramework->getAdapter(Template::class);
$event['details'] = $template->once(
    static fn (): string => $contentModel->foo($controller),
);

// Output
$factory = new MenuFactory();
$system = $factory->createItem('system');

$menu = $factory->createItem('mainMenu');
$menu->addChild($system);

$controller = $this->contaoFramework->getAdapter(Controller::class);
$contentModel = $this->contaoFramework->getAdapter(ContentModel::class);

$template = $this->contaoFramework->getAdapter(Template::class);
$event['details'] = $template->once(
    static fn (): string => $contentModel->foo($controller),
);
```

```php
// Unchanged: $table is used in the next statement, so the existing empty line is kept
$processed[] = $file->getBasename();

$table = $file->getBasename('.php');
$extract = DcaExtractor::getInstance($table);
```

Without the empty line in the input, none is inserted either (two unrelated single-line assignments, rule 6).

**Exception to the new block:** if the variable assigned in B is used in C only as an *argument* (not as subject or
method call target), A and B are still not related, but they are not separated from their group either: rule 4 does
not insert an empty line (`$graph1 = $schemaManager->…;` → `$graph2 = $schemaManager->…;` →
`$this->assertSame($graph1, $graph2);`). Existing empty lines are kept.

Using a variable only as an argument does **not** make statements related: `$requestStack->push($request)` is not
related to `$request = …`, `$operations = [$prefix.'…' => …]` is not related to `$prefix = …`, and
`$this->assertSame(…, $operation->getUriTemplate())` is not related to `$operation = …`.

```php
// Expected output: keep or insert the empty line before the new array group
$altcha = $this->getAltcha($repository);
$challenge = $altcha->createChallenge('salt?expires='.(time() - 600), 42);

$payload = $challenge->toArray();
$payload['number'] = 42;
$payload['challenge'] = 'foobar';
```

The group spans several lines, but each statement is single-line. This exception belongs to the definition of
"direct use"; rule 3b (an assignment followed by a function or static call) is unchanged.

A **related group** is a maximal sequence of adjacent statements where each pair of neighbours is related.

### Rules (evaluate top to bottom, the first matching rule wins)

For two adjacent statements A and B (no comment, no neutral statement between them):

1. A and B are related → **no empty line** (even if A or B is multi-line).
2. A or B is multi-line → **empty line**.
3. A or B is a deprecation (`trigger_deprecation()`, `trigger_error(…, E_USER_DEPRECATED)`) → **empty line**.
3a. B is `unset()`, `parse_str()` or `parse_url()` → **no empty line**
    (`$data = $this->toArray($data);` → `unset($data['@id']);`).
3b. A is an assignment to `$x` and B is a function or static call that uses `$x` (e.g. as an argument) → **no empty
    line** (`$container = …;` → `System::setContainer($container);`, `$foo = 'Foo';` → `$callback($foo);`). This
    does not make A and B related; `$request` does not match `$requestStack`.
3c. A is an assignment whose right-hand side begins with a call to the function `f()` and B is a call to the same
    function → **no empty line** (`$substituteCharacter = mb_substitute_character();` →
    `mb_substitute_character(0xFFFD);`). This does not make A and B related.
4. A or B belongs to a related group of two or more statements (and they are not related to each other) →
   **empty line**.
5. A is an assignment and B is an assertion → **no empty line**.
6. A and B belong to the same group → **no empty line**; otherwise **empty line**. The groups are *variables*
   (assignments and variable calls), *calls* (function calls and static calls) and *assertions*. Deprecations and
   neutral statements belong to no group.

### Removing empty lines

"No empty line" in rules 1, 3a, 3b, 3c, 5 and 6 means that no empty line is *inserted*. An existing empty line
between A and B is only **removed** if A and B are related (rule 1) and one of these conditions is met:

- B is a chained-call block and A and B have the **same subject** (e.g. `$mock = …;` → `$mock` chain, `$mock` chain →
  `$mock` chain), or
- A is an assignment and **both A and B are single-line** (e.g. declaration and direct use: `$serializer = …;` →
  `$normalized = $serializer->…;`, properties and keys: `$config = [];` → `$config['foo'] = …;`, `$items[] = 'a';` →
  `$items[] = 'b';`, `$services = parent::…();` → `$services['foo'] = …;`, `$client = …;` → `$client?->request(…);`).

In all other cases the existing empty line is kept, even if A and B are related:

- **A or B is multi-line** (and B is not a chained-call block on the same subject): `$normalizer = $this->createNormalizer();`
  → `$result = $normalizer->denormalize(…multi-line…);`, `$normalizer = new …(…multi-line…);` →
  `$normalizer->setNormalizer(…);`, `$options ??= …;` → `$options = $options->withSetting(…multi-line…);`,
  `$cron = new Cron(…multi-line…);` → `$cron->addCronJob(…);`.
- **A is not an assignment:** `$operation->setUrl($url);` → `$operation['attributes']` chain,
  `$services->defaults()->autowire();` → `$services->set(Foo::class);`, `$mock->foo();` → `$mock->id = 1;`.

If the empty line is missing in these cases, the fixer does not insert one either (unless another rule requires it).

```php
// Input
$this->assertTrue($a);
$this->assertFalse($b);
$config = [];

$config['foo'] = 'Foo';

foreach ($items as $item) {
    $a = 1;
    $b = 2;
}
$c = 3;

// Output (the empty line between the related single-line $config statements is removed)
$this->assertTrue($a);
$this->assertFalse($b);

$config = [];
$config['foo'] = 'Foo';

foreach ($items as $item) {
    $a = 1;
    $b = 2;
}

$c = 3;
```

```php
// Unchanged: the existing empty lines are kept
$resources = iterator_to_array($collection);

$operation = iterator_to_array($resources[0]->getOperations());
$this->assertTrue($operation->getPaginationClientItemsPerPage());

$this->assertResource($resources[0], 'Article', 'tl_article', '/dc/article', 'article', true);

$this->converters = new WidgetConverterRegistry([$converter]);

$GLOBALS['TL_DCA']['tl_content']['fields']['rows'] = ['inputType' => 'customRows'];
```

```php
// Input
$sortRecursive = static function (&$node) use (&$sortRecursive): void {
    ksort($node);
};
$sortRecursive($prefixTree);
ksort($notPrefixed);

// Output ($sortRecursive() is a function call: rule 2 before it, rule 6 after it)
$sortRecursive = static function (&$node) use (&$sortRecursive): void {
    ksort($node);
};

$sortRecursive($prefixTree);
ksort($notPrefixed);
```

```php
// Input
$mock = $this->createMock(Foo::class);
$mock
    ->method('foo')
    ->willReturn('Foo')
;

$mock
    ->method('bar')
    ->willReturn('Bar')
;

// Output (the empty line before the related chained-call block is removed)
$mock = $this->createMock(Foo::class);
$mock
    ->method('foo')
    ->willReturn('Foo')
;
$mock
    ->method('bar')
    ->willReturn('Bar')
;
```

```php
// Unchanged: the empty lines between unrelated statements are kept
$indentComment = $this->getIndent($tokens, $prevComment, false);
$replacement = rtrim($indentComment, "\t ").$indent;
$i = $prevComment - 1;

$tokens->offsetSet($prevComment - 1, new Token([T_WHITESPACE, $replacement]));
```

```php
// Unchanged
$tokens->overrideRange($isArrayStart, $emptyEnd, $newTokens);

$index = $emptyEnd + 1;
```

```php
// Input
$GLOBALS['BE_FFL']['customFiles'] = $widget::class;
$GLOBALS['TL_DCA']['tl_content']['fields'] = [
    'files' => ['inputType' => 'customFiles', 'eval' => ['multiple' => true]],
];

// Output (different array keys are not related → rule 2)
$GLOBALS['BE_FFL']['customFiles'] = $widget::class;

$GLOBALS['TL_DCA']['tl_content']['fields'] = [
    'files' => ['inputType' => 'customFiles', 'eval' => ['multiple' => true]],
];
```

### Comments

Comments are not statements. **A comment breaks the sequence:** the statements before and after it are not compared
with each other.

For comments directly before a (non-neutral) statement: **always an empty line before the comment, never one after
it.** Consecutive comment lines count as one comment.

- The empty line before the comment is only inserted if the comment follows a `;` or `}` (not after `{`, `(`, `:`).
- Comments at the end of a line of code (`$foo = 'Foo'; // bar`) are not touched.
- A standalone comment after a comment at the end of a line of code is formatted separately.
- File header comments after `<?php` are not touched.

```php
// Input
$foo = 'Foo';
// Comment

$bar = 'Bar';

// Output
$foo = 'Foo';

// Comment
$bar = 'Bar';
```

### Examples (input → expected output)

The order of statements is never changed; the expected outputs below are what the fixer produces.

```php
// Unchanged: unrelated single-line assignments stay together
$request = new Request();
$environment = new Environment();
$hasScope = false;
```

```php
// Input
$request = new Request();
$requestStack = new RequestStack();
$requestStack->push($request);
$environment = new Environment();
$hasScope = false;

// Output (rule 4: the $requestStack group is separated, the order is not changed)
$request = new Request();

$requestStack = new RequestStack();
$requestStack->push($request);

$environment = new Environment();
$hasScope = false;
```

Moving `$request`, `$environment` and `$hasScope` together and the `$requestStack` lines to the end would also be fine,
but that is a manual reordering and is not done by the fixer.

```php
// Unchanged: $requestStack->push() is not adjacent to its declaration, so nothing is related (rule 6 applies)
$requestStack = new RequestStack();
$environment = new Environment();
$requestStack->push($request);
```

```php
// Input
$request = new Request();
$environment = new Environment();
trigger_deprecation('contao/core-bundle', '5.0', 'Foo is deprecated.');
System::loadLanguageFile('default');
self::bootKernel();
unset($foo);
$hasScope = false;

// Output
$request = new Request();
$environment = new Environment();

trigger_deprecation('contao/core-bundle', '5.0', 'Foo is deprecated.');

System::loadLanguageFile('default');
self::bootKernel();
unset($foo);

$hasScope = false;
```

```php
// Mock setup: no empty lines while the mock object is created (rules 1 and 2 of "related")
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

    // But add a new line when it is used
    $mock->isFoo();
}
```

Without the comment, an empty line must still be inserted before `$mock->isFoo();` (multi-line chain followed by a
single-line call on the same object is not related → rule 2).

Note: the code sample in the current `ChainedMethodBlockFixer::getDefinition()` has an empty line between the two
`$mock` chains. This changes: the two chains now belong together.

```php
// Input
$prefix = 'contao_api_'.$name;
$operations = [
    $prefix.'_get_collection' => $this->createCollectionOperation($path),
    $prefix.'_move' => $this->createMoveOperation($path),
    $prefix.'_get' => $this->createGetOperation($path),
    $prefix.'_upload' => $this->createUploadOperation($path),
    $prefix.'_metadata' => $this->createMetadataOperation($path),
];
$metadata = ['filesystem' => $name, 'relation_filesystem' => $tags['api_relation_filesystem'] ?? $name];

// Output
$prefix = 'contao_api_'.$name;

$operations = [
    $prefix.'_get_collection' => $this->createCollectionOperation($path),
    $prefix.'_move' => $this->createMoveOperation($path),
    $prefix.'_get' => $this->createGetOperation($path),
    $prefix.'_upload' => $this->createUploadOperation($path),
    $prefix.'_metadata' => $this->createMetadataOperation($path),
];

$metadata = ['filesystem' => $name, 'relation_filesystem' => $tags['api_relation_filesystem'] ?? $name];
```

```php
// Input
$names = $this->createStub(PropertyNameCollectionFactoryInterface::class);
$names
    ->method('create')
    ->willReturn(new PropertyNameCollection(['path']))
;
$classes = $this->createStub(ResourceClassResolverInterface::class);
$classes
    ->method('isResourceClass')
    ->willReturn(true)
;
$links = new LinkFactory($names, $properties, $classes);

// Output
$names = $this->createStub(PropertyNameCollectionFactoryInterface::class);
$names
    ->method('create')
    ->willReturn(new PropertyNameCollection(['path']))
;

$classes = $this->createStub(ResourceClassResolverInterface::class);
$classes
    ->method('isResourceClass')
    ->willReturn(true)
;

$links = new LinkFactory($names, $properties, $classes);
```

```php
// Input
$serializer = new Serializer([$this->createNormalizer(), new RelationReferenceNormalizer()]);
$record = new DataContainerRecord('tl_content', ['rows' => [
    (object) ['file' => new RelationReference('file-id', '/files/file-id')],
    new \stdClass(),
]], 17);
$normalized = $serializer->normalize($record, 'jsonld');

// Output of both fixers (no reordering; the arguments of DataContainerRecord are split by MultilineCallArgumentsFixer)
$serializer = new Serializer([$this->createNormalizer(), new RelationReferenceNormalizer()]);

$record = new DataContainerRecord(
    'tl_content',
    [
        'rows' => [
            (object) ['file' => new RelationReference('file-id', '/files/file-id')],
            new \stdClass(),
        ],
    ],
    17,
);

$normalized = $serializer->normalize($record, 'jsonld');
```

The preferred result after a *manual* reordering would be:

```php
$record = new DataContainerRecord(
    'tl_content',
    [
        'rows' => [
            (object) ['file' => new RelationReference('file-id', '/files/file-id')],
            new \stdClass(),
        ],
    ],
    17,
);

$serializer = new Serializer([$this->createNormalizer(), new RelationReferenceNormalizer()]);
$normalized = $serializer->normalize($record, 'jsonld');
```

The fixer must leave this version unchanged (rule 3 of "related" keeps `$serializer` and `$normalized` together).

```php
// Input
$operation = $operations[$name];
$this->assertSame('/files/{pathOrUuid}', $operation->getUriTemplate());
$this->assertSame(['pathOrUuid' => '.+'], $operation->getRequirements());
$link = $operation->getUriVariables()['pathOrUuid'];
$this->assertSame(['path'], $link->getIdentifiers());
$this->assertSame(VirtualFilesystemItem::class, $link->getFromClass());

// Output
$operation = $operations[$name];
$this->assertSame('/files/{pathOrUuid}', $operation->getUriTemplate());
$this->assertSame(['pathOrUuid' => '.+'], $operation->getRequirements());

$link = $operation->getUriVariables()['pathOrUuid'];
$this->assertSame(['path'], $link->getIdentifiers());
$this->assertSame(VirtualFilesystemItem::class, $link->getFromClass());
```

```php
// Input
$this->expectException(\InvalidArgumentException::class);
$this->expectExceptionMessage('Invalid');
$service->run();

// Output
$this->expectException(\InvalidArgumentException::class);
$this->expectExceptionMessage('Invalid');

$service->run();
```

## Fixer 2: `MultilineCallArgumentsFixer` (new)

### Rule

If a call has **two or more arguments** and is **multi-line**, every argument is put on its own line, followed by a
trailing comma, and the closing parenthesis is put on its own line.

- Applies to function calls, method calls (`->`, `?->`), static calls (`Foo::bar()`, `self::`, `static::`,
  `parent::`) and `new Foo(…)`. Calls without a known name (`$object->{$method}(…)`, `$callbacks[0](…)`,
  `new class(…) {}`) are calls too; the name-dependent exceptions do not apply to them.
- Does not apply to function/method declarations (handled by `NoLineBreakBetweenMethodArgumentsFixer`), language
  constructs (`isset`, `unset`, `empty`, `list`, `array()`), control structure conditions and attributes
  (*assumption* for attributes).
- Control structure conditions (`if`, `elseif`, `while`, `for`, `foreach`, `switch`, `match`, `catch`), declaration
  parameter lists including default values, attributes and strings are skipped completely, including calls and arrays
  inside them.
- Lists with comments between their elements, or containing inline HTML or PHP open/close tags anywhere inside them
  (also in nested closure bodies), are not changed.
- A call is multi-line if at least one of its arguments contains a line break (also nested, e.g. a multi-line array
  inside a nested call).
- A call with only one argument is never split (except for heredocs, see below).

```php
// Unchanged (single line, even though it is longer than 120 characters)
return $tokens[$index - 1]->isGivenKind([T_OBJECT_OPERATOR, T_PAAMAYIM_NEKUDOTAYIM]) && $tokens[$index + 1]->equals('(');

// Wrong
return $tokens[$index - 1]->isGivenKind([
    T_OBJECT_OPERATOR,
    T_PAAMAYIM_NEKUDOTAYIM,
]) && $tokens[$index + 1]->equals('(');
```

### Rule for heredocs

A heredoc/nowdoc argument always stands on its own line. If a call has a heredoc/nowdoc on the top level of one of its
arguments, the call is split like a multi-line call, even if it has only one argument and regardless of the position
of the heredoc. The exceptions below (e.g. `myFunction($foo, [ … ])`) do not apply. The content and the closing marker
of the heredoc are re-indented together, so the value of the string does not change. Empty lines inside the heredoc
are left empty, and indentation is never reduced by more than the indentation of the closing marker. A heredoc inside
a nested call only splits that call.

```php
// Input
$this->assertString(<<<'EOD'
    foo
    EOD);

$this->assertSame($foo, <<<'EOD'
    foo
    EOD);

$this->assertSame(<<<'EOD'
    foo
    EOD, $foo);

// Output
$this->assertString(
    <<<'EOD'
        foo
        EOD,
);

$this->assertSame(
    $foo,
    <<<'EOD'
        foo
        EOD,
);

$this->assertSame(
    <<<'EOD'
        foo
        EOD,
    $foo,
);
```

### Rule for arrays

The same fixer also handles array literals (short syntax `[ … ]`), wherever they occur (arguments, assignments,
return values, nested arrays, …):

- An array without a line break stays on one line, also if it is nested: `['foo' => ['bar', 'baz']]` stays.
- An array whose only element is another array that directly follows the opening bracket is not expanded:
  `[[ … ]]` and `[[[ … ]]]` stay (only the innermost array is expanded).
- As soon as an array contains a line break, it is expanded **completely**: every element is put on its own line,
  followed by a trailing comma, the opening bracket is followed by a line break and the closing bracket is put on its
  own line. This also applies if the array has only one element.
- A line break is "contained" if one of the elements is multi-line (e.g. a nested multi-line array or call), if the
  array is already partly on several lines, or if a line break has to be inserted because of another rule.
- This applies recursively: every enclosing array that thereby contains a line break is expanded too. Nested arrays
  without a line break (e.g. `(object) ['file' => new RelationReference(…)]`) stay on one line.
- Elements are moved as a whole and re-indented by one level per opened bracket.
- Not affected: array destructuring (`[$a, $b] = …`), array access (`$foo['bar']`) and `list()`.

```php
// Unchanged
$foo = ['foo' => ['bar', 'baz']];

// Input
$foo = ['foo' => [
    'bar' => 'Bar',
    'baz' => 'Baz',
]];

// Output
$foo = [
    'foo' => [
        'bar' => 'Bar',
        'baz' => 'Baz',
    ],
];
```

### Rule for single-member arrays in split calls

When a call is split (arguments, one per line), an array argument that has **exactly one member**, and that member is
single-line, is written in a compact form on one line: `['bar' => 'baz'],`. Arrays with more than one member are
expanded as usual. An array whose only member is multi-line (e.g. `['rows' => [ … ]]`) stays expanded. The compaction
only applies to array arguments of calls that are split by this fixer; free-standing arrays (`$foo = [ … ];`) are
not compacted. Single-line calls are never split, so they are not touched.

```php
// Input
$mock
    ->method()
    ->with('foo', [
        'bar' => 'baz',
    ])
;

// Output
$mock
    ->method()
    ->with(
        'foo',
        ['bar' => 'baz'],
    )
;

// Input
$mock
    ->method()
    ->with('foo', [
        'foo' => 'bar',
        'bar' => 'baz',
    ])
;

// Output
$mock
    ->method()
    ->with(
        'foo',
        [
            'foo' => 'bar',
            'bar' => 'baz',
        ],
    )
;
```

### Exceptions (taken over from `FunctionCallWithMultilineArrayFixer`)

- The multi-line array is the only argument: `myFunction([ … ]);` stays.
- The multi-line array is the second of two arguments and the first argument is simple (contains no space):
  `myFunction($foo, [ … ]);` stays (the existing fixer even removes line breaks to produce this form).
- This exception does not apply to `with`, `withConsecutive` and `withAnyParameters`.
- `ArrayUtil::arrayInsert` is ignored completely.
- `addArgument()` and `addOption()` method calls are ignored, because `SingleLineConfigureCommandFixer` puts them on
  a single line on purpose.

### Example

```php
// Input
$factory = new DataContainerSchemaFactory($this->createContaoFrameworkStub([
    Controller::class => $controller,
    System::class => $this->createAdapterStub(['loadLanguageFile']),
]), $this->converters, $this->relationResolver, $this->localeSwitcher, $this->createRelationDefinitionFactory($this->converters));

// Output
$factory = new DataContainerSchemaFactory(
    $this->createContaoFrameworkStub([
        Controller::class => $controller,
        System::class => $this->createAdapterStub(['loadLanguageFile']),
    ]),
    $this->converters,
    $this->relationResolver,
    $this->localeSwitcher,
    $this->createRelationDefinitionFactory($this->converters),
);
```

`createContaoFrameworkStub([…])` is not split because it has only one argument.

The `DataContainerRecord` example in the statement grouping section shows the combined output of both fixers: the
arguments are split and the nested multi-line arrays are expanded; inner single-line arrays stay unchanged.

### Relation to existing fixers

- `MultilineCallArgumentsFixer` replaces `FunctionCallWithMultilineArrayFixer`; its existing tests must remain covered,
  except where this specification explicitly changes the behavior. Array formatting is part of the new fixer because
  it uses the same list splitting and indentation logic.
- `MultiLineLambdaFunctionArgumentsFixer` is kept and runs after the new fixer.
- `../../config/set/symfony.php` is loaded last, so `MethodArgumentSpaceFixer` effectively uses `on_multiline => ignore`.
- Verify that the full configuration is stable across repeated runs, including `TrailingCommaInMultilineFixer`,
  `ArrayIndentationFixer`, `WhitespaceAfterCommaInArrayFixer` and the closure argument fixer.
- `StatementGroupingFixer` remains separate because it shares almost no code with the argument fixer.

## Order of execution

Higher priority runs first:

1. `MultilineCallArgumentsFixer` (priority -1), so the final multi-line status of statements is known.
2. `StatementGroupingFixer` (priority -2).
3. `StatementIndentationFixer` and `MultiLineLambdaFunctionArgumentsFixer` (both priority -3).

Document these dependencies in the `getPriority()` docblocks.

## Integration

- Register the new fixers in `../../config/contao.php` and remove `ChainedMethodBlockFixer` and
  `FunctionCallWithMultilineArrayFixer` there.
- Update the fixer table in `../../README.md`.
- Put the code samples from this document into `getDefinition()`.
- Follow the code style of the existing fixers in `../../src/Fixer` (final classes extending `AbstractFixer`,
  `IndentationFixerTrait` for indentation).

## Tests

Tests go to `../../tests/Fixer` (one test class per fixer, same structure as the existing tests).

- All examples of this document, each as input and expected output, plus "already correct" cases that must stay
  unchanged.
- The existing tests of `ChainedMethodBlockFixerTest` and `FunctionCallWithMultilineArrayFixerTest` (adapt only where
  this document explicitly changes the behavior, e.g. two `$mock` chains now stay together).
- Removal of the empty line before a chained-call block on the same subject (`$mock = …;` → `$mock` chain, two
  `$mock` chains) and between two related single-line statements if A is an assignment (`$serializer` /
  `$normalized`, `$config = [];` / `$config['foo'] = …;`, `$items[] = 'a';` / `$items[] = 'b';`). All other existing
  empty lines are kept, e.g. between two calls on the same object.
- Empty lines that must be kept even though the statements are related:
  `$normalizer = $this->createNormalizer();` → `$result = $normalizer->denormalize(…multi-line…);`,
  `$normalizer = new …(…multi-line…);` → `$normalizer->setNormalizer(…);`, `$options ??= …;` →
  `$options = $options->withSetting(…multi-line…);`, `$operation->setUrl($url);` → `$operation['attributes']` chain,
  `$cron = new Cron(…multi-line…);` → `$cron->addCronJob(…);`.
- Closure calls (`$fn(…);`) are function calls (grouped with `ksort()` etc., separated from assignments).
- Shared use is broken up if the variable assigned in B is used in the next statement (`$factory`/`$menu`,
  `$template`/`$event['details']`, `$processed[]`/`$table`).
- Direct use is broken up when B and C have the same object or array as subject: the `$challenge` / `$payload`
  example keeps an existing empty line and inserts a missing one. A second fixer pass must leave the output unchanged.
  The preceding `$altcha` / `$challenge` pair stays together; using `$payload` only as an argument also leaves direct
  use intact.
- Code in closures, global code and an `../../ecs.php`-like configuration file.
- Comments between statements: the statements before and after them are not compared (e.g. a comment inside a `$mock`
  setup); empty line before the comment after `;` and `}`, none after `{` and `:`, none after the comment; consecutive
  comment lines count as one; comments at the end of a line and file header comments after `<?php` are unchanged.
- Blocks with a single statement: the empty line after `}` and the comment rule apply there too.
- Categories: other assignment operators (`.=`), static properties (`self::$foo = …`), destructuring and `list()`,
  `$fn(…)->foo();`, `\trigger_deprecation()`, `@trigger_error(…, E_USER_DEPRECATED)` (with and without `@`),
  `trigger_error()` with another level and `@unlink()` (function calls).
- Statements on the same line are not compared; `case` and `default` labels.
- `$this->foo` / `$this->bar` (and `$this->getFoo()->bar`) are not related, existing empty lines are kept; different
  properties with a multi-line B are not related.
- Multi-line assertions (assumption 2) and chained-call blocks with `?->`.
- Control structures between statements (untouched).
- Assertions via `$this->`, `self::` and `static::`, `$this->expect*()`, in a non-test class as well.
- Existing empty lines between unrelated statements are kept (the examples above).
- Array keys are part of the subject (`$GLOBALS['…']` with different keys is not related, `$x[]` chains are).
- Variable detection: names in non-interpolated strings do not count as uses; interpolated variables do. String keys
  remain part of array subjects for direct use and for use as function arguments.
- Argument fixer: function, method, static call and `new`; long single-line calls and arrays (> 120 characters) stay
  unchanged (including the `isGivenKind()` example); nested calls; all exceptions; trailing comma; the
  `DataContainerRecord` example (arguments split, nested array expanded to `[` / `'rows' => [ … ],` / `]`, inner
  single-line array left as it is).
- Heredoc rule: heredoc as only, last and first argument is put on its own line; heredoc with interpolated variables
  and empty lines is re-indented without changing its value; outdenting (negative delta); heredoc as array element;
  heredoc in a nested call (only the inner call is split).
- Ignored code with *multi-line* content (otherwise the test would prove nothing): control structure conditions,
  default values in function declarations, attributes, `addArgument()` and `ArrayUtil::arrayInsert()`.
- The `myFunction($foo, [ … ])` exception does not apply to `withConsecutive()`, `withAnyParameters()` and first
  arguments containing a space (`'a b'`, `$a ?? $b`).
- Unchanged: `array()`, calls inside interpolated strings (`"{$a->b(…)}"`), lists containing inline HTML (also
  inside a closure body).
- Empty lines between arguments are kept; a partly split call without trailing comma gets one; `[[ … ],]` (trailing
  comma) and `[ [ … ] ]` (whitespace) are expanded.
- Array rule: nested single-line array unchanged (`['foo' => ['bar', 'baz']]`); `['foo' => [ … ]]` expanded;
  array with one multi-line element; partly multi-line array (`[` / `'a', 'b',` / `]`); deeply nested arrays;
  destructuring and array access unchanged.
- Single-member arrays in split calls are written compactly (`['bar' => 'baz'],`); arrays with more than one member and
  arrays with a multi-line member are expanded; already expanded calls are compacted; free-standing arrays are not
  compacted.

## Assumptions (not explicitly confirmed)

1. A variable, function or static call directly followed by an assertion gets an empty line (rule 6), e.g.
   `$service->run();` → `$this->assertTrue(…);`. Only "assignment followed by assertion" was confirmed to stay
   together.
2. Multi-line assertions are handled by rule 2, i.e. they get an empty line before and after them unless they are
   related to the neighbour (they usually are not).

## Implementation notes

`MultilineCallArgumentsFixer` processes lists from the outside in. A list is only expanded if it already contains a
line break, so its enclosing list has already been processed. The tests must verify that a second pass produces no
further changes, including cases where single-member array arguments are compacted.
