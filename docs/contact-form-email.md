# Contact Form Email

This document describes how the public contact form sends email to the admin and how to configure it for production.

## Overview

- **Route:** `/contact` (GET shows the form, POST submits).
- **Recipient:** The address set in **Admin → Settings → Contact Information → Contact Email** (stored in the `settings` table as `contact_email`).
- **Behaviour:** On valid submission, the app sends one email to that address with the submitter’s name, email, and message. The admin can reply directly (Reply-To is set to the submitter).

If no contact email is configured, the user sees a “not configured” message and no email is sent.

## How It Works

1. User submits the form (name, email, message, accept terms).
2. `ContactController@store` validates the request and reads `Setting::get('contact_email')`.
3. If `contact_email` is empty: redirect back with error flash; no email.
4. If set: `Mail::to($to)->send(new ContactFormSubmitted($data))`, then redirect back with success.

**Mailable:** `App\Mail\ContactFormSubmitted`

- Accepts validated `name`, `email`, `message`.
- Subject from `website.contact.email_subject` (lang).
- Reply-To set to the submitter so replies go to them.
- Body: Markdown view `resources/views/emails/contact-form-submitted.blade.php` (uses `<x-mail::message>`).

**From address:** All mail uses the app’s default “from” (see [Production setup](#production-setup)).

## Admin Configuration

1. Log in as a user with **manage-settings** (e.g. admin).
2. Go to **Admin → Settings**.
3. In **Contact Information**, set **Contact Email** to the inbox that should receive contact form submissions (e.g. `support@yourdomain.com`).
4. Save.

This value is the only recipient for contact form emails.

## Production Setup

### 1. Mail driver and “from” address

In production `.env`, set a real mailer and the default “from” address:

```env
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Without this, the default is `log` and messages only go to the log.

### 2. Transport configuration

Configure the transport for the mailer you use.

**SMTP (typical with Mailgun, SendGrid, Postmark, or your host):**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

Or a single URL:

```env
MAIL_URL=smtp://user:password@smtp.example.com:587
```

**Other drivers** (see `config/mail.php`): `ses`, `postmark`, `resend`, `sendmail`. Set `MAIL_MAILER` to the driver name and the env vars that driver expects (e.g. AWS credentials for `ses`, Postmark token for `postmark`).

### 3. Contact email in the app

In production, set **Admin → Settings → Contact Email** to the address that should receive contact form messages. That value is stored in the database and used as the recipient for every contact form submission.

## Local / development

- Default `MAIL_MAILER=log`: emails are written to `storage/logs/laravel.log`, not sent.
- To see real emails locally, use [Mailpit](https://github.com/axllent/mailpit) with Sail (`http://localhost:8025`), or set `MAIL_MAILER=smtp` and point to a test SMTP service (e.g. Mailtrap).

## Files

| Purpose | File |
|--------|------|
| Controller | `app/Http/Controllers/ContactController.php` |
| Mailable | `app/Mail/ContactFormSubmitted.php` |
| Email view | `resources/views/emails/contact-form-submitted.blade.php` |
| Form request | `app/Http/Requests/ContactRequest.php` |
| Contact page | `resources/js/pages/Contact.vue` |
| Mail config | `config/mail.php` |

## Troubleshooting

- **No email received:** Ensure `MAIL_MAILER` and transport env vars are set in production and **Contact Email** is set in Admin → Settings. Check `storage/logs/laravel.log` for mail errors.
- **User sees “contact form not configured”:** Set **Contact Email** in Admin → Settings.
- **Wrong recipient:** Change **Contact Email** in Admin → Settings; no code change needed.
