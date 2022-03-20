<?php

return [

    'livemode' => env('PAYMONGO_LIVEMODE', false),

    /**
     * Public and Secret keys from Paymongo. You can get the keys here https://dashboard.paymongo.com/developers.
     */

    /**
     * Public keys are meant to be used for any requests coming from the frontend, such as generating tokens or sources,
     * either using Javascript or through the mobile SDKs.
     * Public keys cannot be used to trigger payments or modify any part of the transaction flow.
     * They have the prefix pk_live_ for live mode and pk_test_ for test mode.
     */
    'public_key' => env('PAYMONGO_PUBLIC_KEY', null),

    /**
     * Secret keys, on the other hand, are for triggering or modifying payments. Never share your secret keys anywhere
     * that is publicly accessible: Github, client-side Javascript code, your website or even chat rooms.
     * The prefixes for the secret keys are sk_live_ for live mode and sk_test_ for test mode.
     */
    'secret_key' => env('PAYMONGO_SECRET_KEY', null),

    /**
     * Paymongo's team continuously adding more features and integrations to the API.
     * Currently, the API supports doing payments via debit and credit cards issued by Visa and Mastercard.
     */
    'version' => env('PAYMONGO_VERSION', '2019-08-05'),

    /*
     * This class is responsible for calculating the signature that will be added to
     * the headers of the webhook request. A webhook client can use the signature
     * to verify the request hasn't been tampered with.
     */
    'signer' => \Luigel\Paymongo\Signer\DefaultSigner::class,

    /**
     * Paymongo webhooks signature secret.
     */
    'webhook_signatures' => [
        'payment_paid' => env('PAYMONGO_WEBHOOK_SIG_PAYMENT_PAID', env('PAYMONGO_WEBHOOK_SIG')),
        'payment_failed' => env('PAYMONGO_WEBHOOK_SIG_PAYMENT_FAILED', env('PAYMONGO_WEBHOOK_SIG')),
        'source_chargeable' => env('PAYMONGO_WEBHOOK_SIG_SOURCE_CHARGABLE', env('PAYMONGO_WEBHOOK_SIG')),
        'payment_refunded' => env('PAYMONGO_WEBHOOK_SIG_PAYMENT_REFUNDED', env('PAYMONGO_WEBHOOK_SIG')),
        'payment_refund_updated' => env('PAYMONGO_WEBHOOK_SIG_PAYMENT_REFUND_UPDATED', env('PAYMONGO_WEBHOOK_SIG')),
    ],

    /**
     * Webhook signature configuration for backwards compatibility.
     */
    'webhook_signature' => env('PAYMONGO_WEBHOOK_SIG'),

    /*
     * This is the name of the header where the signature will be added.
     */
    'signature_header_name' => env('PAYMONGO_SIG_HEADER', 'paymongo-signature'),

    /**
     * This is the amount type to automatically convert the amount in your payload.
     * The default is Paymongo::AMOUNT_TYPE_FLOAT.
     *
     * Choices are: Paymongo::AMOUNT_TYPE_FLOAT, or Paymongo::AMOUNT_TYPE_INT
     */
    'amount_type' => \Luigel\Paymongo\Paymongo::AMOUNT_TYPE_FLOAT,
];
