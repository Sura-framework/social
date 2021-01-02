@extends('app.app')
@section('content')
    <div class="d-flex justify-content-center">
    <div class="padcont2 mt-3 shadow2">
        <div class="profiewr">
            <a href="/wall/{{ $user_id }}/" onClick="Page.Go(this.href); return false" style="text-decoration:none">
                <div class="albtitle" style="border-bottom:0px">
                    @_e('publications')
                    <span id="wall_rec_num">{{ $wall_rec_num }}</span>
                </div>
            </a>
            @if($privacy_wall_block)
                <div class="newmes" id="wall_tab" style="border-bottom:0px;margin-bottom:-5px">
                    <input type="hidden" value="Написать сообщение..." id="wall_input_text" />
                    <label for="wall_input"></label>
                    <input type="text" class="wall_inpst" value="Написать сообщение..." onMouseDown="wall.form_open(); return false" id="wall_input" style="margin:0px" />
                    <div class="no_display" id="wall_textarea">
                        <label for="wall_text"></label>
                        <textarea id="wall_text" class="wall_inpst wall_fast_opened_texta"
                                  onKeyUp="wall.CheckLinkText(this.value)"
                                  onBlur="wall.CheckLinkText(this.value, 1)">
                    </textarea>
                        <input type="hidden" value="{{ $user_id }}" id="user_id" />
                        <div id="attach_files" class="margin_top_10 no_display"></div>
                        <div id="attach_block_lnk" class="no_display clear">
                            <div class="attach_link_bg">
                                <div id="loading_att_lnk"><img src="/images/loading_mini.gif" style="margin-bottom:-2px"  alt="" /></div>
                                <img src="" id="attatch_link_img" class="no_display cursor_pointer" onClick="wall.UrlNextImg()"  alt="" />
                                <div id="attatch_link_title"></div>
                                <div id="attatch_link_descr"></div>
                                <div class="clear"></div>
                            </div>
                            <div class="attach_toolip_but"></div>
                            <div class="attach_link_block_ic fl_l"></div>
                            <div class="attach_link_block_te">
                                <div class="fl_l">Ссылка: <a href="/" id="attatch_link_url" target="_blank"></a></div>
                                <img class="fl_l cursor_pointer" style="margin-top:2px;margin-left:5px" src="/images/close_a.png" onMouseOver="myhtml.title('1', 'Не прикреплять', 'attach_lnk_')" id="attach_lnk_1" onClick="wall.RemoveAttachLnk()" /></div>
                            <input type="hidden" id="attach_lnk_stared" />
                            <input type="hidden" id="teck_link_attach" />
                            <span id="urlParseImgs" class="no_display"></span>
                        </div>
                        <div class="clear"></div>
                        <div id="attach_block_vote" class="no_display">
                            <div class="attach_link_bg">
                                <div class="texta">@_e('ttt')
                                    Тема опроса:</div>
                                <label for="vote_title"></label>
                                <input type="text" id="vote_title" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px"
                                       onKeyUp="$('#attatch_vote_title').text(this.value)"
                                /><div class="mgclr"></div>
                                <div class="texta">@_e('ttt')
                                    Варианты ответа:<br /><small><span id="addNewAnswer"><a class="cursor_pointer" onClick="Votes.AddInp()">@_e('ttt')
                                            добавить</a></span> | <span id="addDelAnswer">@_e('ttt')
                                        удалить</span></small></div><input type="text" id="vote_answer_1" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px" /><div class="mgclr"></div>
                                <div class="texta">&nbsp;</div>
                                <label for="vote_answer_2"></label>
                                <input type="text" id="vote_answer_2" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px" /><div class="mgclr"></div>
                                <div id="addAnswerInp"></div>
                                <div class="clear"></div>
                            </div>
                            <div class="attach_toolip_but"></div>
                            <div class="attach_link_block_ic fl_l"></div><div class="attach_link_block_te"><div class="fl_l">Опрос: <a id="attatch_vote_title" style="text-decoration:none;cursor:default"></a></div>
                                <img class="fl_l cursor_pointer" style="margin-top:2px;margin-left:5px" src="/images/close_a.png" onMouseOver="myhtml.title('1', 'Не прикреплять', 'attach_vote_')" id="attach_vote_1" onClick="Votes.RemoveForAttach()"  alt="" />
                            </div>
                            <input type="hidden" id="answerNum" value="2" />
                        </div>
                        <div class="clear"></div>
                        <input id="vaLattach_files" type="hidden" />
                        <div class="clear"></div>
                        <div class=" fl_l margin_top_10"><button onClick="wall.send(); return false" id="wall_send" class="btn btn-secondary">@_e('ttt')
                                Отправить</button></div>
                        <div class="wall_attach fl_r" onClick="wall.attach_menu('open', this.id, 'wall_attach_menu')" onMouseOut="wall.attach_menu('close', this.id, 'wall_attach_menu')" id="wall_attach">Прикрепить</div>
                        <div class="wall_attach_menu no_display" onMouseOver="wall.attach_menu('open', 'wall_attach', 'wall_attach_menu')" onMouseOut="wall.attach_menu('close', 'wall_attach', 'wall_attach_menu')" id="wall_attach_menu">
                            <div class="wall_attach_icon_smile" id="wall_attach_link" onClick="wall.attach_addsmile()">@_e('ttt')
                                Смайлик</div>
                            <div class="wall_attach_icon_photo" id="wall_attach_link" onClick="wall.attach_addphoto()">@_e('ttt')
                                Фотографию</div>
                            <div class="wall_attach_icon_video" id="wall_attach_link" onClick="wall.attach_addvideo()">@_e('ttt')
                                Видеозапись</div>
                            <div class="wall_attach_icon_audio" id="wall_attach_link" onClick="wall.attach_addaudio()">@_e('ttt')
                                Аудиозапись</div>
                            <div class="wall_attach_icon_doc" id="wall_attach_link" onClick="wall.attach_addDoc()">@_e('ttt')
                                Документ</div>
                            <div class="wall_attach_icon_vote" id="wall_attach_link" onClick="$('#attach_block_vote').slideDown('fast');wall.attach_menu('close', 'wall_attach', 'wall_attach_menu');$('#vote_title').focus();$('#vaLattach_files').val($('#vaLattach_files').val()+'vote|start||')">Опрос</div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            @endif
            <div id="wall_records">
                @if($wall_rec_num_block AND !$blacklist)
                    @include('wall.one_record', array('wall_records' => $wall_records))
                @else
                    <div class="wall_none" >@_e('wall_null')</div>
                @endif
            </div>
            @if($wall_link_block AND !$blacklist)
                <span id="wall_all_record"></span>
                <div onClick="wall.page('{{ $user_id }}'); return false" id="wall_l_href" class="cursor_pointer">
                    <div class="photo_all_comm_bg wall_upgwi" id="wall_link">@_e('wall_next')</div>
                </div>
            @endif
            @if($blacklist)
                <div class="err_yellow" style="font-weight:normal;margin-top:5px">{{ $name }} @_e('profile_block')</div>
            @endif
        </div>
    </div>
    </div>
@endsection
