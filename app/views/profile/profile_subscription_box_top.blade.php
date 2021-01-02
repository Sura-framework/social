<script>
    $('.box_footer').hide();
    $('.bg_show_bottom').hide();
</script>
<div style="margin-top:-5px"></div>
@if($top)
<div class="tb_tabs_wrap" id="tb_tabs_wrap">
    <div id="tb_tabs">
        <div class="tb_tabs clear_fix">
            <div class="progress fl_r tb_prg" id="tb_prg"></div>
            <div class="fl_l summary_tab_sel" id="all_button">
                <a class="summary_tab2" id="tbt_members" style="cursor: pointer;">
                    <div class="summary_tab3">Оценили<span class="fans_count" id="subs_count">{{ $subcr_num }}</span></div>
                </a>
            </div>
            <div class="tb_tabs_sh"></div>
        </div>
    </div>
</div>
<div class="clear" style="margin-top:10px"></div>
@endif
@if($sql_)
    @foreach($sql_ as $row)
        <div class="onefriend"><a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false"><div><img src="{{ $row['ava'] }}" alt="" /></div>{{ $row['name'] }}<br /><span>{{ $row['last_name'] }}</span></a></div>
    @endforeach
@endif
{{ $navigation }}