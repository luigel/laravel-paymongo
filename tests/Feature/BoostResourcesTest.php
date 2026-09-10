<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Symfony\Component\Yaml\Yaml;

/*
 * Laravel Boost ships resources/boost/guidelines/*.blade.php into every agent session of
 * apps depending on this package, and resources/boost/skills/<name>/SKILL.md as on-demand
 * skills. These tests keep both renderable and well-formed.
 */

const BOOST_GUIDELINE = __DIR__.'/../../resources/boost/guidelines/core.blade.php';
const BOOST_SKILL = __DIR__.'/../../resources/boost/skills/paymongo-v3-upgrade/SKILL.md';

/**
 * Render a guideline the way Boost does: its @boostsnippet directives become fenced code
 * blocks, and backticks are swapped out so Blade never sees them, then restored.
 */
function render_boost_guideline(string $path): string
{
    $source = (string) file_get_contents($path);

    $source = (string) preg_replace(
        "/@boostsnippet\\(\\s*['\"]([^'\"]*)['\"]\\s*,\\s*['\"]([^'\"]*)['\"]\\s*\\)/",
        "$1:\n```$2",
        $source,
    );
    $source = str_replace('@endboostsnippet', '```', $source);

    $placeholder = '___SINGLE_BACKTICK___';
    $rendered = Blade::render(str_replace('`', $placeholder, $source));

    return str_replace($placeholder, '`', $rendered);
}

/**
 * @return array{name: ?string, description: ?string, body: string}
 */
function parse_boost_skill(string $path): array
{
    $source = (string) file_get_contents($path);

    preg_match('/\A---\R(.*?)\R---\R?(.*)\z/s', $source, $matches);

    $frontMatter = $matches[1] ?? '';
    $body = $matches[2] ?? $source;

    if (class_exists(Yaml::class)) {
        /** @var array<string, mixed> $parsed */
        $parsed = Yaml::parse($frontMatter) ?? [];
    } else {
        preg_match('/^name:\s*(.+)$/m', $frontMatter, $name);
        preg_match('/^description:\s*(.+)$/m', $frontMatter, $description);
        $parsed = ['name' => $name[1] ?? null, 'description' => $description[1] ?? null];
    }

    return [
        'name' => isset($parsed['name']) ? trim((string) $parsed['name']) : null,
        'description' => isset($parsed['description']) ? trim((string) $parsed['description']) : null,
        'body' => $body,
    ];
}

it('ships a core guideline that renders through Blade', function () {
    expect(BOOST_GUIDELINE)->toBeFile();

    $rendered = render_boost_guideline(BOOST_GUIDELINE);

    expect($rendered)
        ->toContain('Paymongo::paymentIntents()')
        ->toContain('150050')
        ->toContain('```php')
        ->not->toContain('@boostsnippet')
        ->not->toContain('@endboostsnippet');
});

it('keeps the core guideline free of PHP open tags and unsupported Blade constructs', function () {
    $source = (string) file_get_contents(BOOST_GUIDELINE);

    expect($source)
        ->not->toContain('<?php')
        ->not->toContain('<x-')
        ->not->toContain('@include')
        ->not->toContain('@props')
        ->not->toContain('@can');
});

it('ships the paymongo-v3-upgrade skill with valid front matter', function () {
    expect(BOOST_SKILL)->toBeFile();

    $skill = parse_boost_skill(BOOST_SKILL);

    expect($skill['name'])->toBe('paymongo-v3-upgrade')
        ->and($skill['description'])->toBeString()->not->toBeEmpty()
        ->and($skill['body'])->not->toMatch('/^\s*@\w+/m');
});

it('links only to reference files that exist', function () {
    expect(BOOST_SKILL)->toBeFile();

    preg_match_all('/\]\((references\/[^)#]+)/', (string) file_get_contents(BOOST_SKILL), $matches);

    if ($matches[1] === []) {
        expect(true)->toBeTrue();

        return;
    }

    foreach (array_unique($matches[1]) as $reference) {
        expect(dirname(BOOST_SKILL).'/'.$reference)->toBeFile();
    }
});
