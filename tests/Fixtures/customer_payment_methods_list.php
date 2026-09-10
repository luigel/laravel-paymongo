<?php

declare(strict_types=1);

use Luigel\Paymongo\Testing\Fixtures;

return Fixtures::list([
    Fixtures::customerPaymentMethod(),
    Fixtures::customerPaymentMethod([
        'id' => 'cpm_Wk3RmNp7YtXzB2vC5sD8eFg4',
        'payment_method_id' => 'pm_Bq6TnVr9ZuWxC3yD7sE2fGh5',
        'payment_method_type' => 'gcash',
        'details' => null,
        'created_at' => 1725926400,
        'updated_at' => 1725926400,
    ]),
]);
