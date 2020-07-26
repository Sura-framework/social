@extends('app.app')
@section('content')
    <div class="container">
        <div class="buttonsprofile albumsbuttonsprofile pt-3" >
            <a href="/friends/{{ $user_id }}/" onClick="Page.Go(this.href); return false;">Все друзья</a>
            <div class="activetab"><a href="/friends/online/{{ $user_id }}/" onClick="Page.Go(this.href); return false;"><div>Друзья на сайте</div></a></div>
            @if($owner)<a href="/friends/requests/" onClick="Page.Go(this.href); return false;">Заявки в друзья {{ $demands }}</a>@else
            <a href="/friends/common/{{ $user_id }}/" onClick="Page.Go(this.href); return false;">Общие друзья</a>
            <a href="/u{{ $user_id }}" onClick="Page.Go(this.href); return false;">К странице {{ $name }}</a>@endif
        </div>
        <div>
            @if($friends)
                @foreach($friends as $row)
                    <div class="friends_onefriend width_100" id="friend_{{ $row['user_id'] }}">
                        <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">
                            <div class="friends_ava"><img src="{{ $row['ava'] }}" alt="" id="ava_{{ $row['user_id'] }}" /></div>
                        </a>
                        <div class="fl_l" style="width:500px">
                            <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false"><b>{{ $row['user_search_pref'] }}</b></a>
                            <div class="friends_clr"></div>
                            {{ $row['country'] }}{{ $row['city'] }}<div class="friends_clr"></div>
                            {{ $row['age'] }}<div class="friends_clr"></div>
                            <span class="online">{{ $row['online'] }}</span><div class="friends_clr"></div>
                        </div>
                        <div class="menuleft fl_r friends_m">
                            @if($row['viewer'])<a href="/" onClick="messages.new_({{ $row['user_id'] }}); return false"><div>Написать сообщение</div></a>@endif
                            @if($row['owner'])<a onMouseDown="friends.delet({{ $row['user_id'] }}, 0); return false"><div>Убрать из друзей</div></a>@endif
                            <a href="/albums/{{ $row['user_id'] }}/" onClick="Page.Go(this.href); return false"><div>Альбомы</div></a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="info_center">У Вас с этим пользователем нет общих друзей.</div>
            @endif
        </div>
    </div>

@endsection