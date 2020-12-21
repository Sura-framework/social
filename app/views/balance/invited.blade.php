@extends('app.app')
@section('content')
<div class="container-lg">
    <div class="row">
        <div class="col-4">
            {{ $menu }}
        </div>
        <div class="col-8 text-center">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/balance/">@_e('balance')</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@_e('friend_invited')</li>
                </ol>
            </nav>
            <div class="mt-3"></div><div class="allbar_title" style="border-bottom:0px;margin-bottom:0px">@_e('friend_invite_info4')</div>
            @if($invited)
                @foreach($invited as $row)
                    <div class="friends_onefriend width_100" style="margin-top:0px">
                        <a href="/u{user-id}" onClick="Page.Go(this.href); return false"><div class="friends_ava"><img src="{ava}" alt="" id="ava_{user-id}" /></div></a>
                        <div class="fl_l" style="width:500px">
                            <a href="/u{user-id}" onClick="Page.Go(this.href); return false"><b>{name}</b></a><div class="friends_clr"></div>
                            {country}{city}<div class="friends_clr"></div>
                            {age}<div class="friends_clr"></div>
                            <span class="online">{online}</span><div class="friends_clr"></div>
                        </div>
                        <div class="menuleft fl_r friends_m">
                            <a href="/" onClick="messages.new_({user-id}); return false"><div>@_e('write_message')</div></a>
                            <a href="/albums/{user-id}/" onClick="Page.Go(this.href); return false"><div>@_e('albums')</div></a>
                        </div>
                    </div>
                @endforeach
            @else
                @_e('friend_invite_info5')
            @endif
        </div>
    </div>
</div>
@endsection