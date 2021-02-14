<div class="im_text support_answer im_msg {{ $new }} mb-3" id="imMsg{{ $msg_id }}" {{ $read_js_func }}>
    <div style="float:left;width:55px">
        <div class="ava_mini im_msg_ava im_ava_mini">
            <a href="/u{{ $user_id }}" onClick="Page.Go(this.href); return false">
                <img src="{{ $ava }}" style="width: 45px;" width="45"  alt="{{ $name }}"/>
            </a>
        </div>
    </div>
    <div class="wallauthor support_anser_nam im_msg_name" style="padding-left:0px">
        <a href="/u{{ $user_id }}" onClick="Page.Go(this.href); return false">{{ $name }}</a>
        <div class="fl_r im_msg_date"><div class="fl_l">{{ $date }}</div>
            <img src="/images/close_a_wall.png" onMouseOver="myhtml.title('{msg-id}', 'Удалить сообщение', 'del_text_')"
                 onClick="im.delet('{{ $msg_id }}', '{{ $folder }}'); return false" id="del_text_{{ $msg_id }}" class="msg_histry_del cursor_pointer im_msg_delf fl_r"  alt="{{ $name }}"/>
        </div>
    </div>
    <div style="float:left;width:442px;overflow:hidden">
        <div class="walltext im_msg_mag" style="margin-left:0px">{{ $text }}</div>
    </div>
</div>