@foreach($wall_records as $row)
    <div class="card mt-3 wallrecord" id="wall_record_{{ $row['id'] }}">
        @if($row['record'])
        <div id="link_tag_{{ $row['user_id'] }}_{{ $row['id'] }}"></div>
        <div class="card-body">
            <div class="mb-3">
                <div class="row align-items-center">
                    <div class="col-auto @if($row['privacy_comment'] == true AND $row['if_comments'] == true) wall_ava_mini @endif " id="ava_rec_{{ $row['id'] }}">
                        <a href="/{{ $row['address'] }}" class="avatar link_tag_{{ $row['id'] }}"  onmouseover="wall.showTag({{ $row['user_id'] }}, {{ $row['id'] }}, {{ $row['action_type'] }}, 'link_tag_')" onmouseout="wall.hideTag({{ $row['user_id'] }}, {{ $row['id'] }}, {{ $row['action_type'] }})">
                            {{-- <img src="{{ $row['ava'] }}" alt="..." class="avatar-img rounded-circle"> --}}

                            <div class="wall-avatar wall-avatar-online mr-5">
                                <img src="{{ $row['ava'] }}" alt="{{ $row['name'] }}" class="wall-avatar-img rounded-circle" style="width: 34px;height: 34px;">
                            </div>
                        </a>
                    </div>
                    <div class="col ml-n2">
                        <h4 class="mb-1 wall_user name_tag_{{ $row['id'] }}" onmouseover="wall.showTag({{ $row['user_id'] }}, {{ $row['id'] }}, {{ $row['action_type'] }}, 'name_tag_')" onmouseout="wall.hideTag({{ $row['user_id'] }}, {{ $row['id'] }}, {{ $row['action_type'] }})">{{ $row['name'] }}</h4>
                        <p class="card-text small text-muted">
                            <time datetime="2018-05-24">{{ $row['date'] }}</time>
                        </p>
                    </div>
                    <div class="col-auto">
                        @if($row['action_type'] == 1)
                            @if($row['owner'])
                            <div class="wall_delete" onMouseOver="myhtml.title('{{ $row['id'] }}', 'Удалить запись', 'wall_del_')" onClick="wall.delet('{{ $row['id'] }}'); return false" id="wall_del_{{ $row['id'] }}"></div>
                            @endif
                            <div class="wall_tell_all cursor_pointer" onMouseOver="myhtml.title('{{ $row['id'] }}', 'Отправить в сообщество или другу', 'wall_tell_all_')" onClick="Repost.Box('{{ $row['id'] }}', {{ $row['action_type'] }}); return false "id="wall_tell_all_{{ $row['id'] }}"></div>
                            @if($row['author_user_id'])
                                <div class="wall_tell cursor_pointer" onMouseOver="myhtml.title('{{ $row['id'] }}', 'Рассказать друзьям', 'wall_tell_')" onClick="wall.tell('{{ $row['id'] }}'); return false" id="wall_tell_{{ $row['id'] }}" style="margin-top:2px;margin-left:4px"></div>
                            <div class="wall_tell_ok no_display" id="wall_ok_tell_{{ $row['id'] }}" style="margin-left:2px;margin-top:1px"></div>
                            <div class="wall_delete" onMouseOver="myhtml.title('{{ $row['id'] }}', 'Отметить как спам', 'wall_spam_')" onClick="Report.WallSend('wall', '{{ $row['id'] }}'); return false" id="wall_spam_{{ $row['id'] }}"></div>
                            @endif
                        @else
                            @if($row['owner'])
                                <div class="wall_delete" onMouseOver="myhtml.title('{{ $row['id'] }}', 'Удалить запись', 'wall_del_')" onClick="groups.delete('{{ $row['id'] }}', '{{ $row['user_id'] }}'); return false" id="wall_del_{{ $row['id'] }}"></div>
                            @endif
                            <div class="wall_tell_all cursor_pointer" onMouseOver="myhtml.title('{{ $row['id'] }}', 'Отправить в сообщество или другу', 'wall_tell_all_')" onClick="Repost.Box('{{ $row['id'] }}', {{ $row['action_type'] }}); return false "id="wall_tell_all_{{ $row['id'] }}"></div>
                            @if($row['author_user_id'])
                                <div class="wall_tell cursor_pointer" onMouseOver="myhtml.title('{{ $row['id'] }}', 'Рассказать друзьям', 'wall_tell_')" onClick="groups.tell('{{ $row['id'] }}'); return false" id="wall_tell_{{ $row['id'] }}" style="margin-top:2px;margin-left:4px"></div>
                                <div class="wall_tell_ok no_display" id="wall_ok_tell_{{ $row['id'] }}" style="margin-left:2px;margin-top:1px"></div>
                                <div class="wall_delete" onMouseOver="myhtml.title('{{ $row['id'] }}', 'Отметить как спам', 'wall_spam_')" onClick="Report.WallSend('wall', '{{ $row['id'] }}'); return false" id="wall_spam_{{ $row['id'] }}"></div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <p class="mb-3 wall_text">{{ $row['text'] }}</p>
            @if($row['privacy_comment']  == true AND $row['if_comments'] == true)
                <div class="mb-3">
                <span id="fast_comm_link_{{ $row['id'] }}" class="fast_comm_link">&nbsp;|&nbsp; <a href="/" id="fast_link_{{ $row['id'] }}" onClick="wall.open_fast_form('{{ $row['id'] }}'); wall.fast_open_textarea('{{ $row['id'] }}'); return false">Комментировать</a></span>
            </div>
            @endif
        </div>
            <div class="">
                <div class="public_likes_user_block no_display"
                     id="public_likes_user_block{{ $row['id'] }}" onMouseOver="groups.wall_like_users_five('{{ $row['id'] }}', {{ $row['action_type'] }})" onMouseOut="groups.wall_like_users_five_hide('{{ $row['id'] }}')" style="margin-left:490px">
                    @if($row['action_type'] == 1)
                        <div onClick="wall.all_liked_users('{{ $row['id'] }}', '', '{{ $row['likes'] }}')">Понравилось {{ $row['likes_text'] }}</div>
                    @else
                        <div onClick="groups.wall_all_liked_users('{{ $row['id'] }}', '', '{{ $row['likes'] }}')">Понравилось {{ $row['likes_text'] }}</div>
                    @endif

                    <div class="public_wall_likes_hidden">
                        <div class="public_wall_likes_hidden2">
                            <a href="/u{{ $row['viewer_id'] }}" id="like_user{{ $row['viewer_id'] }}_{{ $row['id'] }}" class="no_display" onClick="Page.Go(this.href); return false">
                                <img src="{{ $row['viewer_ava'] }}" width="32"  alt="" /></a>
                            <div id="likes_users{{ $row['id'] }}"></div>
                        </div>
                    </div>
                    <div class="public_like_strelka"></div>
                </div>
                <input type="hidden" id="update_like{{ $row['id'] }}" value="0" />
                <div class="fl_r public_wall_like cursor_pointer"
                     onClick="{{ $row['like_js_function'] }}"
                     onMouseOver="groups.wall_like_users_five('{{ $row['id'] }}', {{ $row['action_type'] }})"
                     onMouseOut="groups.wall_like_users_five_hide('{{ $row['id'] }}')"
                     id="wall_like_link{{ $row['id'] }}">
                    <div class="fl_l" id="wall_like_active">Мне нравится</div>
                    <div class="public_wall_like_no {{ $row['yes_like'] }}" id="wall_active_ic{{ $row['id'] }}"></div>
                    <b id="wall_like_cnt{{ $row['id'] }}" class="{{ $row['yes_like_color'] }}">{{ $row['likes'] }}</b>
                </div>
            </div>
        <div class="mx-n2 p-2 mt-3 bg-light">
                @if($row['privacy_comment'] == true AND $row['if_comments'] == true)
                    <div class="comment mb-3 ">
                        <div class="wall_fast_form no_display" id="fast_form_{{ $row['id'] }}">
                            <div class="no_display wall_fast_texatrea" id="fast_textarea_{{ $row['id'] }}">
                                <label for="fast_text_{{ $row['id'] }}"></label>
                                <textarea class="wall_inpst fast_form_width wall_fast_text" style="height:33px;color:#000;margin:0px;width:688px" id="fast_text_{{ $row['id'] }}"
                                  onKeyPress="if(event.keyCode == 10 || (event.ctrlKey && event.keyCode == 13))wall.fast_send('{{ $row['id'] }}', '{{ $row['user_id'] }}', 2)">
                                </textarea>
                                <div class="button_div fl_l margin_top_5"><button onClick="wall.fast_send('{{ $row['id'] }}', '{{ $row['user_id'] }}', 2); return false" id="fast_buts_{{ $row['id'] }}">Отправить</button></div>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                @endif


            @if($row['all_comm'])
                <div class="cursor_pointer" onClick="wall.all_comments('{{ $row['id'] }}', '{{ $row['user_id'] }}', 1); return false" id="wall_all_but_link_{{ $row['id'] }}">
                    <div class="public_wall_all_comm" id="wall_all_comm_but_{{ $row['id'] }}">Показать {{ $row['gram_record_all_comm'] }}</div>
                </div>
            @endif
            @if($row['comment'])

                @if($row['comments'])
                    @foreach($row['comments'] as $comments)
                <div class="comment " id="wall_fast_comment_{{ $comments['comm_id'] }}" onMouseOver="ge('fast_del_{{ $comments['comm_id'] }}').style.display = 'block'" onMouseOut="ge('fast_del_{{ $comments['comm_id'] }}').style.display = 'none'">
                    <div class="media">
                        <a class="avatar" href="/u{{ $comments['user_id'] }}">
                            <img class="mr-2 rounded-circle" src="{{ $comments['ava'] }}" alt="{{ $comments['name']  }}" height="32">
                        </a>
                        <div class="media-body">
                            <h5 class="mt-0">{{ $comments['name']  }} <small class="text-muted"><time datetime="2018-05-24"> {{ $comments['date'] }}</time></small></h5>
                            {{ $comments['text'] }}

                            <br>
                            @if($comments['owner'])
                                <a href="/" class="size10 fl_r no_display text-muted" id="fast_del_{{ $comments['comm_id'] }}" onClick="wall.fast_comm_del('{{ $comments['comm_id'] }}'); return false">Удалить</a>
                            @else
                                &nbsp;-&nbsp; <a href="#" class="text-muted font-13 d-inline-block mt-2" onClick="wall.Answer('{{ $comments['id'] }}', '{{ $comments['comm_id'] }}', '{{ $comments['name'] }}'); return false" id="answer_lnk">Ответить</a>
                            @endif

                        </div>
                    </div>
                </div> <!-- / .row -->
                        @endforeach
                @endif
            @endif
        </div>
        @endif
        @if(!$row['comment_form'])
        <div class="wall_fast_opened_form" id="fast_form" style="margin: 0;padding: 20px 10px;">
            <label for="fast_inpt_{{ $row['id'] }}"></label>
            <input type="text" class="wall_inpst fast_form_width wall_fast_input" value="Комментировать..." id="fast_inpt_{{ $row['id'] }}" onMouseDown="wall.fast_open_textarea('{{ $row['id'] }}', 2); return false"  />
            <div class="no_display wall_fast_texatrea" id="fast_textarea_{{ $row['id'] }}">
            <textarea class="wall_inpst fast_form_width wall_fast_text" style="height:33px;color:#000;margin:0px;" id="fast_text_{{ $row['id'] }}"
            onKeyPress="if(event.keyCode == 10 || (event.ctrlKey && event.keyCode == 13))wall.fast_send('{{ $row['id'] }}', '{{ $row['author_id'] }}', 2)"></textarea>
                <div class="button_div fl_l margin_top_5"><button onClick="wall.fast_send('{{ $row['id'] }}', '{{ $row['user_id'] }}', 2); return false" id="fast_buts_{{ $row['id'] }}">Отправить</button></div>
                <div class="wall_answer_for_comm fl_l">
                    <a class="cursor_pointer answer_comm_for" id="answer_comm_for_{{ $row['id'] }}"></a>
                    <input type="hidden" class="answer_comm_id" id="answer_comm_id{{ $row['id'] }}" />
                </div>
            </div>
            <div class="clear"></div>
        </div>@endif
    </div>
@endforeach