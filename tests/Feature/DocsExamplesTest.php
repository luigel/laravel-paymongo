<?php

declare(strict_types=1);

use Luigel\Paymongo\Facades\Paymongo;
use Symfony\Component\Finder\Finder;

/*
 * Code examples in the Package docs are real files under docs/examples,
 * included into the markdown. Each one runs here under Paymongo::fake(), so
 * an example that no longer works against the package fails the suite.
 *
 * A walkthrough's examples are the files of one small app instead (a
 * controller, its routes, a listener), so they are left out here and run
 * together, end to end, by their own test.
 *
 * An example that makes no API call at all (Money arithmetic, say) is
 * listed as offline, and must send nothing.
 */

const DOCS_WALKTHROUGHS = [
    'first-payment', // DocsFirstPaymentTest
    'receiving-webhooks', // DocsWebhooksTest
    'testing', // DocsTestingTest
];

const DOCS_OFFLINE_EXAMPLES = [
    'amounts/money.php',
];

dataset('docs examples', function (): array {
    $examples = [];

    foreach (Finder::create()->files()->name('*.php')->in(dirname(__DIR__, 2).'/docs/examples')->exclude(DOCS_WALKTHROUGHS)->sortByName() as $file) {
        $examples[$file->getRelativePathname()] = [$file->getPathname(), in_array($file->getRelativePathname(), DOCS_OFFLINE_EXAMPLES, true)];
    }

    return $examples;
});

it('runs the docs example under Paymongo::fake()', function (string $path, bool $offline) {
    Paymongo::fake();

    run_docs_example($path);

    $offline ? Paymongo::assertNothingSent() : Paymongo::assertSent(fn (): bool => true);
})->with('docs examples');

it('fails a docs example that no longer works against the package', function (string $code, string $exception, string $message) {
    $path = tempnam(sys_get_temp_dir(), 'docs-example');
    file_put_contents($path, "<?php\n\nuse Luigel\\Paymongo\\Facades\\Paymongo;\n\n{$code}\n");

    Paymongo::fake();

    try {
        expect(fn () => run_docs_example($path))->toThrow($exception, $message);
    } finally {
        unlink($path);
    }
})->with([
    'a renamed method' => ["Paymongo::paymentIntents()->find('pi_1');", Error::class, 'Call to undefined method'],
    'an undefined variable' => ['Paymongo::paymentIntents()->create($attributes);', ErrorException::class, 'Undefined variable $attributes'],
]);
