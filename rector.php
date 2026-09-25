<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Rector
|--------------------------------------------------------------------------
|
|   composer refactor:dry   preview the changes Rector would make (what CI runs)
|   composer refactor       apply them, then run Pint to restore the house style
|
| Language level is PHP 8.2 (the package floor). On top of the PHP sets we run
| the dead-code, code-quality, type-declaration, early-return and privatization
| sets. In Rector 2.x the early-return set is empty - its rules moved into
| code-quality - it is kept so the intent stays visible.
|
| Rules that fight the package's established style are skipped below, each
| with the reason next to it. Rector runs with its own PHPStan container: the
| Larastan extension is not loaded here (its bootstrap constants are not
| available inside Rector), which is fine for these rule sets.
|
*/

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveDuplicatedReturnSelfDocblockRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessUnionReturnDocblockRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\TypeDeclaration\Rector\BooleanAnd\BinaryOpNullableToInstanceofRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\While_\WhileNullableToInstanceofRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSkip([
        __DIR__.'/vendor',
        __DIR__.'/docs',

        // Constructor promotion is applied deliberately, one DTO attribute per
        // parameter; deciding when to promote stays a human call.
        ClassPropertyAssignToConstructorPromotionRector::class,

        // Nullable objects are compared with `=== null` throughout (CursorPage,
        // CursorTokenPage, PaymongoClient). The `instanceof` flavour reads worse
        // for a `?Closure` and PHPStan level 8 is equally happy with both.
        FlipTypeControlToUseExclusiveTypeRector::class,
        WhileNullableToInstanceofRector::class,
        BinaryOpNullableToInstanceofRector::class,

        // The Route::paymongoWebhooks() macro closure carries a
        // `/** @var Router $this */` docblock that PHPStan needs on a statement;
        // an arrow function has no statement to hang it on.
        ClosureToArrowFunctionRector::class => [
            __DIR__.'/src/PaymongoServiceProvider.php',
        ],

        // Pest tests stay `function () {` (Pest style); closures in src are typed.
        AddClosureVoidReturnTypeWhereNoReturnRector::class => [
            __DIR__.'/tests',
        ],

        // `Resource::fromArray()` is declared `: self` but documented `@return static`
        // on purpose: AbstractService::one()/many() resolve their generics through it.
        RemoveDuplicatedReturnSelfDocblockRector::class => [
            __DIR__.'/src/Data/Resource.php',
        ],

        // `nextPage(): ?self` needs `@return self<T>|null` to carry the page's
        // generic item type; the native type alone loses it (PHPStan level 8).
        RemoveUselessUnionReturnDocblockRector::class => [
            __DIR__.'/src/Pagination/CursorPage.php',
            __DIR__.'/src/Pagination/CursorTokenPage.php',
        ],
    ])
    ->withPhpVersion(PhpVersion::PHP_82)
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        earlyReturn: true,
        privatization: true,
    )
    // Short (global) classes are imported, matching the code base
    // (`use Closure;`, `use Throwable;`, ...), not left as `\Closure`.
    ->withImportNames(importShortClasses: true, removeUnusedImports: true)
    ->withCache(cacheDirectory: __DIR__.'/.rector-cache', cacheClass: FileCacheStorage::class);
