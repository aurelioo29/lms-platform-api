@php
    $appName = config('app.name');

    // TODO: ganti ini sesuai warna primary login/register kamu
    // contoh: '#2563eb' (blue), '#4f46e5' (indigo), '#0ea5a4' (teal)
    $primary = '#2563eb';

    $title = $greeting ?: ($level === 'error' ? __('Whoops!') : __('Verify your email'));

    $intro = $introLines[0] ?? __('Click the button below to verify your email address.');
    $outro = $outroLines[0] ?? __('If you didn’t create an account, you can ignore this email.');

    $badgeText = __('Email Verification');

    // Button color based on level (tetap mengikuti primary untuk default)
    $buttonBg = match ($level) {
        'success' => '#16a34a',
        'error' => '#dc2626',
        default => $primary,
    };
@endphp

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $appName }}</title>
</head>

<body
    style="margin:0;padding:0;background:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#111827;">
    {{-- Preheader (hidden) --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        {{ $intro }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellpadding="0" cellspacing="0"
                    style="width:620px;max-width:620px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 18px 50px rgba(17,24,39,.10);">

                    {{-- Top accent --}}
                    <tr>
                        <td style="height:5px;background:{{ $primary }};line-height:5px;font-size:5px;">&nbsp;</td>
                    </tr>

                    {{-- Header --}}
                    <tr>
                        <td style="padding:20px 26px 10px 26px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="left" style="vertical-align:middle;">
                                        <div style="font-weight:900;font-size:14px;color:#111827;letter-spacing:-.2px;">
                                            {{ $appName }}
                                        </div>
                                        <div style="margin-top:6px;font-size:12px;color:#6b7280;">
                                            {{ __('Account Notification') }}
                                        </div>
                                    </td>
                                    <td align="right" style="vertical-align:middle;">
                                        <span
                                            style="display:inline-block;font-size:11px;font-weight:800;color:{{ $primary }};background:rgba(37,99,235,.10);padding:6px 10px;border-radius:999px;">
                                            {{ strtoupper($badgeText) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Content --}}
                    <tr>
                        <td style="padding:14px 26px 24px 26px;">
                            <h1
                                style="margin:0 0 10px 0;font-size:22px;line-height:1.25;color:#111827;font-weight:900;letter-spacing:-.2px;">
                                {{ $title }}
                            </h1>

                            <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#374151;">
                                {{ $intro }}
                            </p>

                            @isset($actionText)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:16px 0 10px 0;">
                                    <tr>
                                        <td>
                                            <a href="{{ $actionUrl }}" target="_blank" rel="noopener"
                                                style="
                                                display:inline-block;
                                                background:{{ $buttonBg }};
                                                color:#ffffff !important;
                                                text-decoration:none;
                                                font-weight:800;
                                                font-size:14px;
                                                padding:12px 18px;
                                                border-radius:12px;
                                                box-shadow:0 10px 22px rgba(37,99,235,.22);
                                                ">
                                                {{ $actionText }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endisset

                            <p style="margin:10px 0 0 0;font-size:13px;line-height:1.65;color:#6b7280;">
                                {{ $outro }}
                            </p>

                            <p style="margin:18px 0 0 0;font-size:14px;line-height:1.7;color:#374151;">
                                {{ __('Regards,') }}<br>
                                <strong style="color:#111827;">{{ $appName }}</strong>
                            </p>

                            @isset($actionText)
                                <div style="margin-top:18px;padding-top:14px;border-top:1px solid #eef2f7;">
                                    <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;">
                                        {{ __('If the button doesn’t work, open this link:') }}
                                    </p>
                                    <p style="margin:8px 0 0 0;font-size:12px;line-height:1.6;word-break:break-all;">
                                        <a href="{{ $actionUrl }}"
                                            style="color:{{ $primary }};text-decoration:underline;">
                                            {{ $displayableActionUrl }}
                                        </a>
                                    </p>
                                </div>
                            @endisset
                        </td>
                    </tr>
                </table>

                <div
                    style="max-width:620px;margin:12px auto 0;color:#9ca3af;font-size:12px;text-align:center;line-height:1.6;">
                    © {{ date('Y') }} {{ $appName }}.
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
