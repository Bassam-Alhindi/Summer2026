<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Flash toast messages
    |--------------------------------------------------------------------------
    |
    | Every notification the server pushes to the toast area lives here so it
    | follows the visitor's locale instead of being hardcoded in a controller.
    |
    */

    'transaction_created' => 'Transaction added successfully!',
    'transaction_updated' => 'Transaction updated successfully!',

    'budget_exceeded' => "⚠️ Budget warning: the ':category' category reached :percentage% of its limit and exceeded it by :amount SAR",
    'budget_near_limit' => "📊 Budget warning: the ':category' category reached :percentage% of its monthly budget limit",

    'category_exists' => 'Heads up: this category already exists!',

    'profile_updated' => 'Profile updated.',
    'password_updated' => 'Password updated.',

    'assistant_failed' => 'Something went wrong while processing your request. Please try again.',
    'assistant_not_configured' => 'The AI assistant is not set up yet. An AI API key needs to be added before it can answer.',
    'current_password_mismatch' => 'The provided password does not match your current password.',
];
