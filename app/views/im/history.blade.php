<div class="im_messages_cont" id="im_messages_cont">
    <div class="im_messages_res" style="padding-bottom: 109px;">
    <div id="im_history">
    @if($im)
    @foreach($im as $row)
        <div class="im_text support_answer im_msg {new} mb-3" id="imMsg{msg-id}" {{ $row['read_js_func'] }}>
            <div style="float:left;width:55px">
                <div class="ava_mini im_msg_ava im_ava_mini">
                    <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">
                        <img src="{{ $row['ava'] }}" style="width: 45px;" width="45"  alt="{{ $row['name'] }}"/>
                    </a>
                </div>
            </div>
            <div class="wallauthor support_anser_nam im_msg_name" style="padding-left:0px">
                <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">{{ $row['name'] }}</a>
                <div class="fl_r im_msg_date"><div class="fl_l">{{ $row['date'] }}</div>
                    <img src="/images/close_a_wall.png" onMouseOver="myhtml.title('{msg-id}', 'Удалить сообщение', 'del_text_')"
                         onClick="im.delet('{{ $row['msg_id'] }}', '{{ $row['folder'] }}'); return false" id="del_text_{{ $row['msg_id'] }}" class="msg_histry_del cursor_pointer im_msg_delf fl_r"  alt="{{ $row['name'] }}"/>
                </div>
            </div>
            <div style="float:left;width:442px;overflow:hidden">
                <div class="walltext im_msg_mag" style="margin-left:0px">{{ $row['text'] }}</div>
            </div>
        </div>
    @endforeach
    @endif
    </div>
    <div class="im_state">
        <div id="im_last_visit" style="display: none;">Последний раз был вчера в 19:53</div>
        <div id="im_typing" class="im_typing" style="display: none;"><span id="im_typing_name">Юра</span>  набирает сообщение..</div>
        <div id="im_recording_state" class="im_typing" style="display: none;"><span id="im_typing_name">Юра</span>  записывает голос..</div>
    </div>
    </div>
    @if(!$first_id)
    <script type="text/javascript">
        $(document).ready(function(){
            setInterval('im.im_footer_resize()', 200);
            vii_interval_im = setInterval('im.update()', 2000);
            $('.im_scroll').scroll(function(){
                if($('.im_scroll').scrollTop() <= ($('.im_scroll').height()/2)+250)
                    im.page('{for_user_id}');
            });
        });
        func = function(val){
            $('#msg_text').focus();
            if(document.selection){
                $('#message_tab_frm').document.selection.createRange().text = $('#message_tab_frm').document.selection.createRange().text+val;
            } else if($('#msg_text').selectionStart !== undefined){
                var element = $('#msg_text');
                var str = element.value;
                var start = element.selectionStart;
                var length = element.selectionEnd - element.selectionStart;
                element.value = str.substr(0, start) + str.substr(start, length) + val + str.substr(start + length);
            } else {
                $('#msg_text').value += val;
            }
        }
    </script>
    <div class="clear im_addform im_footer" id="im_footer">
{{--        <div class="ava_mini im_ava_mini">--}}
{{--            <a href="/u{myuser-id}" onClick="Page.Go(this.href); return false"><img src="{{ $my_ava }}" alt="" /></a>--}}
{{--        </div>--}}
        <div class="d-flex justify-content-around align-items-center">
            <div class="">
                <div class="wall_attach fl_r" onClick="wall.attach_menu('open', this.id, 'wall_attach_menu')"
                     onMouseOut="wall.attach_menu('close', this.id, 'wall_attach_menu')"
                     id="wall_attach" style="margin-top:0px">@_e('attach')</div>
                <div class="wall_attach_menu no_display" onMouseOver="wall.attach_menu('open', 'wall_attach', 'wall_attach_menu')" onMouseOut="wall.attach_menu('close', 'wall_attach', 'wall_attach_menu')" id="wall_attach_menu" style="margin-left:433px;margin-top:20px">
                    <div class="wall_attach_icon_smile" id="wall_attach_link" onClick="wall.attach_addsmile()">Смайлик</div>
                    <div class="wall_attach_icon_photo" id="wall_attach_link" onClick="wall.attach_addphoto()">Фотографию</div>
                    <div class="wall_attach_icon_video" id="wall_attach_link" onClick="wall.attach_addvideo()">Видеозапись</div>
                    <div class="wall_attach_icon_audio" id="wall_attach_link" onClick="wall.attach_addaudio()">Аудиозапись</div>
                    <div class="wall_attach_icon_doc" id="wall_attach_link" onClick="wall.attach_addDoc()">Документ</div>
                </div>
            </div>
            <div class="col-9">
                <div id="message_tab_frm">
                    <label for="msg_text"></label>
                    <textarea
                            class="im_text videos_input wysiwyg_inpt msg_text"
                            id="msg_text"
                            style="height:38px"
                            placeholder="Введите Ваше сообщение.."
                            onKeyPress="
             if(((event.keyCode == 13) || (event.keyCode == 10)) && (event.ctrlKey == false)) im.send('{{ $for_user_id }}', '{{ $my_name }}', '{{ $my_ava }}')
             if(((event.keyCode == 13) || (event.keyCode == 10)) && (event.ctrlKey == true)) func('\r\n')
            " onKeyUp="im.typograf()">
            </textarea>
                </div>
            </div>
            <div>
                <button onClick="im.send('{{ $for_user_id }}', '{{ $my_name }}', '{{ $my_ava }}')" id="sending" class="btn btn-ico btn-primary rounded-circle" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
                        <path d="m4.7 15.8c-.7 1.9-1.1 3.2-1.3 3.9-.6 2.4-1 2.9 1.1 1.8s12-6.7 14.3-7.9c2.9-1.6 2.9-1.5-.2-3.2-2.3-1.4-12.2-6.8-14-7.9s-1.7-.6-1.2 1.8c.2.8.6 2.1 1.3 3.9.5 1.3 1.6 2.3 3 2.5l5.8 1.1c.1 0 .1.1.1.1s0 .1-.1.1l-5.8 1.1c-1.3.4-2.5 1.3-3 2.7z" fill="#fff"/>
                    </svg>
                </button>
            </div>
        </div>
        <div id="attach_files" class="no_display" style="margin-left:60px"></div>
        <input id="vaLattach_files" type="hidden" />
    </div>
    <div id="jquery_jplayer"></div>
    <input type="hidden" id="teck_id" value="" />
    <input type="hidden" id="typePlay" value="standart" />
    <input type="hidden" id="teck_prefix" value="" />
    <input type="hidden" id="status_sending" value="1" />
    <input type="hidden" id="for_user_id" value="{for_user_id}" />
    @endif
</div>