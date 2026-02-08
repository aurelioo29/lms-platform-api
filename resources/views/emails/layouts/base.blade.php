@php
    $appName = config('app.name');
    $primary = config('app.mail_primary', '#2563eb');

    // ⚠️ Penting: Gmail tidak bisa akses localhost.
    // Gunakan URL publik: https://domainkamu.com/images/logo-email.png atau ngrok url
    $logoUrl = config('app.mail_logo_url') ?: env('MAIL_LOGO_URL');

    // Optional variables
    $preheader = $preheader ?? '';
    $headerSubtitle = $headerSubtitle ?? __('Account Notification');
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
        {{ $preheader }}
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
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="vertical-align:middle;">
                                                    @if (!empty($logoUrl))
                                                        <img src="{{ $logoUrl }}" alt="{{ $appName }}"
                                                            width="32" height="32"
                                                            style="display:block;border:0;outline:none;text-decoration:none;border-radius:8px;" />
                                                    @else
                                                        {{-- Fallback: kalau logo URL belum diset, jangan tampilkan gambar kosong --}}
                                                        <div
                                                            style="width:32px;height:32px;border-radius:8px;background:rgba(0,0,0,.06);">
                                                        </div>
                                                    @endif
                                                </td>

                                                <td style="vertical-align:middle;padding-left:10px;">
                                                    <div
                                                        style="font-weight:900;font-size:14px;color:#111827;letter-spacing:-.2px;">
                                                        {{ $appName }}
                                                    </div>
                                                    <div style="margin-top:6px;font-size:12px;color:#6b7280;">
                                                        {{ $headerSubtitle }}
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                    <td align="right" style="vertical-align:middle;">
                                        @isset($badge)
                                            <span
                                                style="display:inline-block;font-size:11px;font-weight:800;color:{{ $primary }};background:rgba(37,99,235,.10);padding:6px 10px;border-radius:999px;">
                                                {{ strtoupper($badge) }}
                                            </span>
                                        @endisset
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:14px 26px 24px 26px;">
                            @yield('content')
                        </td>
                    </tr>
                </table>

                {{-- Footer --}}
                <div
                    style="max-width:620px;margin:12px auto 0;color:#9ca3af;font-size:12px;text-align:center;line-height:1.6;">
                    © {{ date('Y') }} {{ $appName }}.
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
