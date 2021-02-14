@if(!empty($notifications))
@foreach($notifications as $row)
<div id="notification{{ $row['id'] }}" class="notification" onclick="QNotifications.open_notify({{ $row['id'] }});">
{{--    <div class="unp-date-separator">{{ $row['date'] }}</div>--}}
    <div class="date"><span>{{ $row['date'] }}</span><span class="del" onclick="QNotifications.del({{ $row['id'] }});"></span></div>
    <div style="margin-bottom: 10px;"><span class="icon"><span class="icn {{ $row['icon'] }}"></span></span> <span>{{ $row['type'] }}</span></div>
    <div style="display: inline-flex;">{{ $row['users'] }}</div>
    @if($row['gifts'])
    <img src="/uploads/gifts/{{ $row['gift'] }}.png" style="width: 64px;float: right;margin-right: 20px;" alt="{{ $row['gift'] }}"/>@endif
</div>@endforeach
@else
    <p>Нет оповещений.</p>
@endif