@if($search)
    @foreach($search as $row)
<div class="friends_onefriend width_100">
    <a href="/{{ $row['adres'] }}" onClick="Page.Go(this.href); return false"><div class="friends_ava"><img src="{{ $row['ava'] }}"  alt="{{ $row['name'] }}"/></div></a>
    <div class="fl_l" style="width:500px">
        <a href="/{{ $row['adres'] }}" onClick="Page.Go(this.href); return false"><b>{{ $row['name'] }}</b></a><div class="friends_clr"></div>
        <span class="color777">{{ $row['traf'] }}</span><div class="friends_clr"></div>
    </div>
</div>
    @endforeach
@endif