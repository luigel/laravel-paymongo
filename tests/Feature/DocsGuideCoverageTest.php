<?php

declare(strict_types=1);

use Luigel\Paymongo\PaymongoManager;
use Luigel\Paymongo\Services\AbstractService;
use Luigel\Paymongo\Tests\Fixtures\DocsCoverage\UndocumentedManager;
use Symfony\Component\Finder\Finder;

/*
 * Every public service method must be mentioned on a guide page in
 * docs/, so adding one without documenting it fails the suite. The
 * generated Reference pages list every method anyway, so they don't count,
 * and they are built by the docs site rather than shipped here.
 *
 * A method counts as mentioned when a page, with the examples it includes,
 * calls it on its service accessor: `payouts()->list(`. Names like
 * `retrieve` are shared by most services, so the call must name the
 * service too.
 */

/**
 * Every guide page's markdown, with its `php include=` examples inlined.
 *
 * @return array<string, string>
 */
function docs_guide_pages(): array
{
    $pages = [];

    foreach (Finder::create()->files()->name('*.md')->depth(0)->in(dirname(__DIR__, 2).'/docs')->sortByName() as $file) {
        $pages[$file->getRelativePathname()] = (string) preg_replace_callback(
            '/```php include=(\S+)/',
            static function (array $match) use ($file): string {
                $example = $file->getPath().'/'.$match[1];

                if (! is_file($example)) {
                    throw new RuntimeException("{$file->getRelativePathname()} includes missing example [{$match[1]}].");
                }

                return $match[0]."\n".file_get_contents($example);
            },
            $file->getContents(),
        );
    }

    return $pages;
}

/**
 * The public service methods no guide page mentions, as they are called:
 * `payouts()->list()`.
 *
 * @param  class-string  $manager
 * @param  array<string, string>  $pages
 * @return list<string>
 */
function docs_undocumented_service_methods(string $manager, array $pages): array
{
    $undocumented = [];

    foreach ((new ReflectionClass($manager))->getMethods(ReflectionMethod::IS_PUBLIC) as $accessor) {
        $serviceType = $accessor->getReturnType();

        if (! $serviceType instanceof ReflectionNamedType || ! is_subclass_of($serviceType->getName(), AbstractService::class)) {
            continue;
        }

        foreach ((new ReflectionClass($serviceType->getName()))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor() || $method->isStatic() || $method->getDeclaringClass()->getName() === AbstractService::class) {
                continue;
            }

            $call = '/(?<!\w)'.$accessor->getName().'\(\)\s*->\s*'.$method->getName().'\(/';
            $mentioned = array_filter($pages, static fn (string $page): bool => preg_match($call, $page) === 1);

            if ($mentioned === []) {
                $undocumented[] = "{$accessor->getName()}()->{$method->getName()}()";
            }
        }
    }

    return $undocumented;
}

it('mentions every public service method on a guide page', function () {
    $undocumented = docs_undocumented_service_methods(PaymongoManager::class, docs_guide_pages());

    expect($undocumented)->toBe([], 'Not mentioned on any guide page in docs/: '.implode(', ', $undocumented));
});

it('names a public service method no guide page mentions', function () {
    expect(docs_undocumented_service_methods(UndocumentedManager::class, docs_guide_pages()))
        ->toBe(['payouts()->reverseSettlement()']);
});

it('does not count a method called on another service', function () {
    $pages = ['upgrading.md' => 'Call `Paymongo::payments()->list()`, or `Paymongo::payouts()->retrieve()`.'];

    expect(docs_undocumented_service_methods(UndocumentedManager::class, $pages))
        ->toBe(['payouts()->list()', 'payouts()->reverseSettlement()']);
});
