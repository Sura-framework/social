@if($search)
@foreach($search as $row)
<a href="/{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false;" onMouseOver="FSE.ClrHovered(this.id)"
       id="all_fast_res_clr{{ $row['id'] }}">
    <div class="row align-items-center">
        <div class="col-4" >
            <img src="{{ $row['ava'] }}" style="width: {{ $row['img_width'] }}px;" id="fast_img"  alt="{{ $row['user_search_pref'] }}"/>
        </div>
        <div class="col ml-3">
            <div id="fast_name">{{ $row['user_search_pref'] }}</div>
            <div><span>{{ $row['country'] }}{{ $row['city'] }}</span></div>
            <span>{{ $row['age'] }}</span>
        </div>
    </div>
</a>
@endforeach
@endif