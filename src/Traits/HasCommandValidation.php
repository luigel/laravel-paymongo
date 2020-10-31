<?php

namespace Luigel\Paymongo\Traits;

use Illuminate\Support\Facades\Validator;

trait HasCommandValidation
{
    public function validate($data, $rules, $message)
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $this->info($message);

            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return 1;
        }
    }
}
