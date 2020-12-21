@extends('app.app')
@section('content')
    <div class="container-lg">
        <div class="row">
            <div class="col-4">
                {{ $menu }}
            </div>
            <div class="col-8">
                <div class="margin_top_10"></div>
                <div class="allbar_title">@_e('friend_invite_info')</div>
                <div class="ubm_descr">
                    <div class="text-center">
                        <p class="mb-3">@_e('friend_invite_info2')</p>
                        <label class="color777">@_e('friend_invite_info3')</label>
                        <input type="text" class="videos_input" style="width:200px" onClick="this.select()" value="{{ $site }}{{ $uid }}" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection