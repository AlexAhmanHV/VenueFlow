<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public Demo Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, privileged demo accounts are locked behind a separate
    | access key and privileged routes require an unlocked session.
    |
    */
    'public_mode' => (bool) env('DEMO_PUBLIC_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Full Demo Access Key
    |--------------------------------------------------------------------------
    |
    | This key is entered on /demo/full-access to unlock privileged demo
    | accounts and routes for the current session.
    |
    */
    'full_access_key' => env('DEMO_FULL_ACCESS_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Session Flag
    |--------------------------------------------------------------------------
    */
    'session_flag' => 'demo.full_access_granted',

    /*
    |--------------------------------------------------------------------------
    | Privileged Emails
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of emails that should be treated as privileged in
    | public demo mode even before role lookup.
    |
    */
    'privileged_emails' => array_values(array_filter(array_map(
        static fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('DEMO_PRIVILEGED_EMAILS', 'super@demo.test'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Public Read-only Admin Accounts
    |--------------------------------------------------------------------------
    |
    | These accounts are allowed to log in during public demo mode but all
    | write actions on restaurant admin routes stay blocked unless full access
    | is unlocked.
    |
    */
    'read_only_emails' => array_values(array_filter(array_map(
        static fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('DEMO_READ_ONLY_EMAILS', 'owner@demo.test'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Public Registration
    |--------------------------------------------------------------------------
    */
    'disable_registration' => (bool) env('DEMO_DISABLE_REGISTRATION', true),

    /*
    |--------------------------------------------------------------------------
    | Public Booking Free Text
    |--------------------------------------------------------------------------
    */
    'restrict_public_notes' => (bool) env('DEMO_RESTRICT_PUBLIC_NOTES', true),
];
