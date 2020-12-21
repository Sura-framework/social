@extends('app.app')
@section('content')
<div class="container-lg">
    <div class="row">
        <div class="col-4">
            {{ $menu }}
        </div>
        <div class="col-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/settings/">@_e('settings')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@_e('blacklist')</li>
                </ol>
            </nav>
            @if($user_blacklist_num)
            <div class="margin_top_10"></div>
                <div class="allbar_title">@_e('blacklist_num') {{ $cnt }}</div>
            @else
                <div class="alert alert-primary">{{ $user_blacklist_info }}</div>
            @endif
            @if($user_blacklist)
                @foreach($user_blacklist as $key)
                <div class="sett_oneblack" id="u{{ $key['user-id'] }}">
                    <a href="/u{{ $key['user-id'] }}" onClick="Page.Go(this.href); return false"><img src="{{ $key['ava'] }}" alt="" /></a>
                    <a href="/u{{ $key['user-id'] }}" onClick="Page.Go(this.href); return false"><b>{{ $key['name'] }}</b></a>
                    <div style="margin-top:7px">
                        <a href="/u{{ $key['user-id'] }}" onClick="settings.delblacklist('{user-id}'); return false" id="del_{{ $key['user-id'] }}">@_e('blacklist_delete')</a>
                    </div>
                </div>
                @endforeach
            @endif

        </div>
    </div>
</div>
@endsection