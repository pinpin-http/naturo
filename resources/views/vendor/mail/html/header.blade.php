@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="horizontalWanita.png" class="logo" alt="Wanita Care">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
