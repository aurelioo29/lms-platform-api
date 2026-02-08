@extends('emails.layouts.base')

@php
    $preheader = 'Reset your password using the link inside.';
    $badge = 'Password Reset';
    $headerSubtitle = 'Reset password';
    $expireMinutes = config('auth.passwords.users.expire', 60);
@endphp

@section('content')
    <h1 style="margin:0 0 10px 0;font-size:22px;line-height:1.25;color:#111827;font-weight:900;letter-spacing:-.2px;">
        Reset your password
    </h1>

    <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#374151;">
        We received a request to reset your password. Click the button below to continue.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:16px 0 10px 0;">
        <tr>
            <td>
                <a href="{{ $actionUrl }}" target="_blank" rel="noopener"
                    style="display:inline-block;background:{{ config('app.mail_primary', '#2563eb') }};color:#ffffff !important;text-decoration:none;font-weight:800;font-size:14px;padding:12px 18px;border-radius:12px;box-shadow:0 10px 22px rgba(37,99,235,.22);">
                    Reset Password
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:10px 0 0 0;font-size:13px;line-height:1.65;color:#6b7280;">
        This reset link will expire in {{ $expireMinutes }} minutes.
        If you did not request a password reset, you can ignore this email.
    </p>

    <div style="margin-top:18px;padding-top:14px;border-top:1px solid #eef2f7;">
        <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;">
            If the button doesn’t work, open this link:
        </p>
        <p style="margin:8px 0 0 0;font-size:12px;line-height:1.6;word-break:break-all;">
            <a href="{{ $actionUrl }}"
                style="color:{{ config('app.mail_primary', '#2563eb') }};text-decoration:underline;">
                {{ $actionUrl }}
            </a>
        </p>
    </div>
@endsection
