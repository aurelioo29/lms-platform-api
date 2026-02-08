@php
    $primary = config('app.mail_primary', '#2563eb');
    $logoUrl = config('app.mail_logo_url'); // wajib URL publik (bukan localhost)
@endphp

@extends('emails.layouts.base')

@section('content')
    <h1 style="margin:0 0 10px 0;font-size:20px;line-height:1.3;font-weight:800;color:#111827;">
        Halo, {{ $name }}
    </h1>

    <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#374151;">
        Terima kasih sudah mendaftar di <strong>{{ $appName }}</strong>.
        Klik tombol di bawah untuk memverifikasi email dan mengaktifkan akun kamu.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:18px 0 12px 0;">
        <tr>
            <td>
                <a href="{{ $actionUrl }}" target="_blank" rel="noopener"
                    style="display:inline-block;background:{{ $primary }};color:#fff !important;text-decoration:none;font-weight:700;font-size:14px;padding:12px 16px;border-radius:12px;">
                    Verifikasi Email
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;">
        Kalau kamu tidak merasa mendaftar, abaikan email ini.
    </p>

    <div style="margin-top:18px;padding-top:14px;border-top:1px solid #eef2f7;">
        <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;">
            Jika tombol tidak berfungsi, buka link ini:
        </p>
        <p style="margin:8px 0 0 0;font-size:12px;line-height:1.6;word-break:break-all;">
            <a href="{{ $actionUrl }}" style="color:{{ $primary }};text-decoration:underline;">
                {{ $actionUrl }}
            </a>
        </p>
    </div>
@endsection
