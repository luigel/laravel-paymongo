# Contract tests

Smoke tests that run against the **real PayMongo test API** to verify the
package still matches PayMongo's live contract (paths, envelopes, statuses).
They are excluded from the default test suite and are skipped unless
explicitly enabled.

## Requirements

Both must hold, otherwise every test is skipped:

- `PAYMONGO_CONTRACT_TESTS=1` (any truthy value) is set in the environment.
- `PAYMONGO_SECRET_KEY` is a **test-mode** secret key (starts with `sk_test_`).
  Live keys never run; test-mode keys move no real money.

## Running

```bash
docker compose run --rm \
  -e PAYMONGO_CONTRACT_TESTS=1 \
  -e PAYMONGO_SECRET_KEY=sk_test_your_secret_key_here \
  php vendor/bin/pest --testsuite=contract
```

Without Docker: `PAYMONGO_CONTRACT_TESTS=1 PAYMONGO_SECRET_KEY=sk_test_... vendor/bin/pest --testsuite=contract`.

The environment variables win over the fake defaults in `phpunit.xml.dist`
because PHPUnit does not overwrite variables that are already set.

## Notes

- These tests create real (test-mode) resources: a payment intent and a
  checkout session per run. PayMongo test data can be cleaned from the
  dashboard.
- They use `Luigel\Paymongo\Tests\ContractTestCase`, which allows outbound
  HTTP (`Http::allowStrayRequests()`) and reads the keys from the real
  environment instead of the `sk_test_fake` defaults.
- Never commit real keys — not even test-mode ones.
