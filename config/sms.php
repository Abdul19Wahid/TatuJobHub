<?php
/*
 * SMS configuration — Twilio, used by app/helpers/Sms.php
 *
 * TRIAL ACCOUNT LIMITATION:
 * Twilio trial accounts can only send SMS to verified phone numbers.
 * To verify a number: Twilio Console → Phone Numbers → Verified Caller IDs → Add New
 * Once you upgrade your Twilio account this restriction is removed.
 *
 * LOCAL DEV: leave 'enabled' => false (or account_sid/auth_token blank)
 * so registration and other flows don't try to call Twilio at all —
 * Sms::send() just returns false without erroring, and the app
 * continues normally (e.g. registration still succeeds, it just
 * won't actually text anyone).
 */
return [
    'enabled'     => false,

    'account_sid' => '',
    'auth_token'  => '',
    'from_number' => '',
];
