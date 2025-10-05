<x-mail::message>
# Two-Factor Authentication Disabled

Hello {{ $user->name }},

This is a security notification to inform you that two-factor authentication (2FA) has been disabled for your account.

**Account Details:**
- Email: {{ $user->email }}
- Date: {{ now()->format('F j, Y \a\t g:i A') }}

If you did not make this change or believe your account has been compromised, please contact your system administrator immediately.

If you disabled 2FA yourself, you can re-enable it at any time from your account security settings.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
