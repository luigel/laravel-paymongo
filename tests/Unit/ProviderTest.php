<?php

declare(strict_types=1);

it('merges the default paymongo config', function () {
    expect(config('paymongo.base_url'))->toBe('https://api.paymongo.com/v1');
});
