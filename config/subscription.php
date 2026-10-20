<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Free Trial
    |--------------------------------------------------------------------------
    |
    | Every owner gets the "trial_plan" (a plan slug) free for this many months
    | after signing up. The trial covers all shops created within that window.
    |
    */

    'trial_months' => 1,

    'trial_plan' => 'business-plus',

    /*
    |--------------------------------------------------------------------------
    | Payment Contact
    |--------------------------------------------------------------------------
    |
    | Shown in the "subscription expired" modal so the owner can choose a plan
    | and pay. The Telegram value is a username without "@".
    |
    */

    'contact_telegram' => env('SUBSCRIPTION_CONTACT_TELEGRAM', 'Nodirjon10_01'),

    'contact_phone' => env('SUBSCRIPTION_CONTACT_PHONE'),

];
