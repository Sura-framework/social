<script type="text/javascript">var page_cnt_rate = 1;</script>
<div class="miniature_box">
    <div class="p-0 miniature_pos card">
        <div class="card-header">
            <div class="miniature_title fl_l apps_box_text">@_e('rating_history')</div><a class="cursor_pointer fl_r" style="font-size:12px" onClick="viiBox.clos('view_rating', 1)">@_e('close')</a>
        </div>
        <div class="card-body">
                <div id="rating_users">
                    @if($users)
                        @foreach($users as $row)
                            <div class="rate_block">
                                <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">
                                    <img src="{{ $row['ava'] }}" style="width:50px;height: 50px;" alt="{{ $row['user_search_pref'] }}"/>
                                </a>
                                <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">
                                    <b>{{ $row['user_search_pref'] }}</b>
                                </a>
                                <div class="profile_ratingview">+{{ $row['rate'] }}</div>
                                <div class="rate_date">{{ $row['date'] }}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="info_center pb-5 pt-5">@_e('rating_no_update')</div>
                    @endif
                </div>
                @if(count($users) > 9)
                    <div class="rate_alluser cursor_pointer" onClick="rating.page()" id="rate_prev_ubut">
                        <div id="load_rate_prev_ubut">@_e('rating_next')</div>
                    </div>
                @endif
        </div>

    </div>
{{--    <div class="clear" style="height:100px"></div>--}}
</div>