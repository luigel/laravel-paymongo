<?php

declare(strict_types=1);

/*
 * The Testing page's examples (docs/examples/testing) are the reader's own
 * tests of the Your first payment app. Required here, their it() blocks
 * register in this file and run against that app.
 */

beforeEach(function () {
    boot_docs_first_payment_app();
});

foreach (glob(dirname(__DIR__, 2).'/docs/examples/testing/*.php') as $example) {
    require $example;
}
