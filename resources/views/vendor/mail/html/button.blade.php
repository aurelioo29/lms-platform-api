<table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 18px 0;">
    <tr>
        <td align="center">
            <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td>
                        <a href="{{ $url }}" class="button button-{{ $color ?? 'primary' }}" target="_blank"
                            rel="noopener"
                            style="display:inline-block;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:800;font-size:14px;">
                            {{ $slot }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
