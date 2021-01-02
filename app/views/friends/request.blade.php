@extends('app.app')
@section('content')
    <div class="container">
        {{ $menu }}
        <div>
            @if($friends)
                @foreach($friends as $row)
                    <div class="friends_onefriend width_100 d-flex" id="friend_{{ $row['user_id'] }}">
                        <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">
                            <div class="avatar {{ $row['ava_online'] }} mr-5  d-block">
                                <img src="{{ $row['ava'] }}" alt="" id="ava_{{ $row['user_id'] }}" class="avatar-img rounded-circle" style="width: 60px;height: 60px;"/>
                            </div>
                        </a>
                        <div class="fl_l" style="width:500px">
                            <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false"><b>{{ $row['user_search_pref'] }}</b></a>
                            <div class="friends_clr"></div>
                            {{ $row['country'] }}{{ $row['city'] }}<div class="friends_clr"></div>
                            {{ $row['age'] }}<div class="friends_clr"></div>
                            <span class="online">{{ $row['online'] }}</span><div class="friends_clr"></div>
                        </div>
                        <div class="menuleft fl_r friends_m">
                           <button class="btn btn-primary" onMouseDown="friends.take({{ $row['user_id'] }}); return false">Добавить</button>
                            <button class="btn btn-primary" onMouseDown="friends.reject({{ $row['user_id'] }}); return false">Отклонить</button>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="info_center">@_e('friends_common_not')</div>
            @endif
        </div>
    </div>
@endsection