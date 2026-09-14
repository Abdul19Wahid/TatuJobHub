<?php
/*
 * Mail configuration — TatuJobHub / InfinityFree hosting
 *
 * InfinityFree blocks outbound SMTP on ports 25, 465, 587 to external hosts.
 * The only reliable sending method on InfinityFree is PHP's mail() function,
 * which routes through their internal sendmail relay automatically.
 *
 * HOW TO SET UP A REAL "FROM" ADDRESS:
 *   1. In your InfinityFree control panel, go to Email Accounts.
 *   2. Create: noreply@tatujobhub.xo.je
 *   3. That's it — mail() will be accepted and delivered.
 *
 * If you later move to a VPS or want SMTP (e.g. Gmail App Password),
 * set 'driver' => 'smtp' and fill in the smtp_* fields below.
 */
return [
    // 'mail'  → use PHP mail() via InfinityFree's relay (recommended on shared hosting)
    // 'smtp'  → use external SMTP (only works on VPS / your own server)
    'driver'          => 'mail',

    // From address — must be a mailbox that exists on tatujobhub.xo.je
    'from_email'      => 'noreply@tatujobhub.xo.je',
    'from_name'       => 'TatuJobHub',

    // SMTP settings (only used when driver = 'smtp')
    'smtp_host'       => '',
    'smtp_port'       => 587,
    'smtp_user'       => '',
    'smtp_pass'       => '',
    'smtp_encryption' => 'tls',

    'debug'           => false,
];
