@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display:inline-block;">
            {{-- kalau punya logo --}}
            @if (trim($slot) === 'Laravel')
                <span style="font-weight:900;letter-spacing:-.2px;">{{ config('app.name') }}</span>
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
