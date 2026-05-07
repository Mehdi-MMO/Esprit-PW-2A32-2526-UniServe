<?php

declare(strict_types=1);

/*
 * Copy this file to config.smtp.local.php and replace values.
 * Uses Resend API for transactional OTP emails.
 */
putenv('RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxxx');
putenv('RESEND_FROM_EMAIL=no-reply@yourdomain.com');
putenv('RESEND_FROM_NAME=UniServe');
