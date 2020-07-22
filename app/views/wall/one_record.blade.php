@foreach($wall_records as $row)
    <div class="card mt-3" id="wall_record_{{ $row['rec_id'] }}">
        @if($row['record'])
        <div id="link_tag_{{ $row['user_id'] }}_{{ $row['rec_id'] }}"></div>
        <div class="card-body">
            <div class="mb-3">
                <div class="row align-items-center">
                    <div class="col-auto [privacy-comment][if-comments]wall_ava_mini[/if-comments][/privacy-comment]" id="ava_rec_{{ $row['rec_id'] }}">
                        <a href="/u{{ $row['user_id'] }}" class="avatar"  onmouseover="wall.showTag({{ $row['user_id'] }}, {{ $row['rec_id'] }}, 1)" onmouseout="wall.hideTag({{ $row['user_id'] }}, {{ $row['rec_id'] }}, 1)">
                            <img src="{{ $row['ava'] }}" alt="..." class="avatar-img rounded-circle">
                        </a>
                    </div>
                    <div class="col ml-n2">
                        <h4 class="mb-1" onmouseover="wall.showTag({{ $row['user_id'] }}, {{ $row['rec_id'] }}, 1)" onmouseout="wall.hideTag({{ $row['user_id'] }}, {{ $row['rec_id'] }}, 1)">{{ $row['name'] }} {{ $row['online'] }}</h4>
                        <p class="card-text small text-muted">
                            <span class="fe fe-clock"></span>{{ $row['type'] }} <time datetime="2018-05-24"> {{ $row['date'] }}</time>
                        </p>
                    </div>
                    <div class="col-auto">
                        @if($row['owner'])
                        <div class="wall_delete" onMouseOver="myhtml.title('{{ $row['rec_id'] }}', 'Удалить запись', 'wall_del_')" onClick="wall.delet('{{ $row['rec_id'] }}'); return false" id="wall_del_{{ $row['rec_id'] }}"></div>
                        @endif
                        <div class="wall_tell_all cursor_pointer" onMouseOver="myhtml.title('{{ $row['rec_id'] }}', 'Отправить в сообщество или другу', 'wall_tell_all_')" onClick="Repost.Box('{{ $row['rec_id'] }}'); return false "id="wall_tell_all_{{ $row['rec_id'] }}"></div>
                        @if($row['author_user_id'])
                            <div class="wall_tell cursor_pointer" onMouseOver="myhtml.title('{{ $row['rec_id'] }}', 'Рассказать друзьям', 'wall_tell_')" onClick="wall.tell('{{ $row['rec_id'] }}'); return false" id="wall_tell_{{ $row['rec_id'] }}" style="margin-top:2px;margin-left:4px"></div>
                        <div class="wall_tell_ok no_display" id="wall_ok_tell_{{ $row['rec_id'] }}" style="margin-left:2px;margin-top:1px"></div>
                        <div class="wall_delete" onMouseOver="myhtml.title('{{ $row['rec_id'] }}', 'Отметить как спам', 'wall_spam_')" onClick="Report.WallSend('wall', '{{ $row['rec_id'] }}'); return false" id="wall_spam_{{ $row['rec_id'] }}"></div>
                        @endif
                    </div>
                </div>
            </div>
            <p class="mb-3">{{ $row['text'] }}</p>
            @if($row['privacy_comment'] AND $row['if_comments'])
            <div class="mb-3">
                <span id="fast_comm_link_{{ $row['rec_id'] }}" class="fast_comm_link">&nbsp;|&nbsp; <a href="/" id="fast_link_{{ $row['rec_id'] }}" onClick="wall.open_fast_form('{{ $row['rec_id'] }}'); wall.fast_open_textarea('{{ $row['rec_id'] }}'); return false">Комментировать</a></span>
            </div>
            @endif
            <div class="mb-3">
                <div class="public_likes_user_block no_display"
                     id="public_likes_user_block{{ $row['rec_id'] }}" onMouseOver="groups.wall_like_users_five('{{ $row['rec_id'] }}'[wall-func],
           'uPages'[/wall-func])" onMouseOut="groups.wall_like_users_five_hide('{{ $row['rec_id'] }}')" style="margin-left:585px">
                    <div onClick="wall.all_liked_users('{{ $row['rec_id'] }}', '', '{likes}')">Понравилось {likes-text}</div>
                    <div class="public_wall_likes_hidden">
                        <div class="public_wall_likes_hidden2">
                            <a href="/u{{ $row['viewer-id'] }}" id="like_user{viewer-id}_{{ $row['rec_id'] }}" class="no_display" onClick="Page.Go(this.href); return false">
                                <img src="{{ $row['viewer_ava'] }}" width="32"  alt="" /></a>
                            <div id="likes_users{{ $row['rec_id'] }}"></div>
                        </div>
                    </div>
                    <div class="public_like_strelka"></div>
                </div>
                <input type="hidden" id="update_like{{ $row['rec_id'] }}" value="0" />
                <div class="fl_r public_wall_like cursor_pointer" onClick="{like-js-function}" onMouseOver="groups.wall_like_users_five('{{ $row['rec_id'] }}', 'uPages')" onMouseOut="groups.wall_like_users_five_hide('{{ $row['rec_id'] }}')" id="wall_like_link{{ $row['rec_id'] }}">
                    <div class="fl_l" id="wall_like_active">Мне нравится</div>
                    <div class="public_wall_like_no {{ $row['yes_like'] }}" id="wall_active_ic{{ $row['rec_id'] }}"></div>
                    <b id="wall_like_cnt{{ $row['rec_id'] }}" class="{{ $row['yes_like_color'] }}">{{ $row['likes'] }}</b>
                </div>
            </div>
                @if($row['privacy_comment'] AND $row['if_comments'])
                <hr>
                <div class="comment mb-3 ">
                    <div class="wall_fast_form no_display" id="fast_form_{{ $row['rec_id'] }}">
                        <div class="no_display wall_fast_texatrea" id="fast_textarea_{{ $row['rec_id'] }}">
                            <label for="fast_text_{{ $row['rec_id'] }}"></label>
                            <textarea class="wall_inpst fast_form_width wall_fast_text" style="height:33px;color:#000;margin:0px;width:688px" id="fast_text_{{ $row['rec_id'] }}"
                                                                                          onKeyPress="if(event.keyCode == 10 || (event.ctrlKey && event.keyCode == 13))wall.fast_send('{{ $row['rec_id'] }}', '{author-id}', 2)"></textarea>
                            <div class="button_div fl_l margin_top_5"><button onClick="wall.fast_send('{{ $row['rec_id'] }}', '{author-id}', 2); return false" id="fast_buts_{{ $row['rec_id'] }}">Отправить</button></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                @endif
            @endif
            @if($row['all_comm'])
            <div class="cursor_pointer" onClick="wall.all_comments('{{ $row['rec_id'] }}', '{author-id}', 1); return false" id="wall_all_but_link_{{ $row['rec_id'] }}">
                <div class="public_wall_all_comm" id="wall_all_comm_but_{{ $row['rec_id'] }}">Показать {gram-record-all-comm}</div>
            </div>
            @endif
            @if($row['comment'])
            <hr>
            <div class="comment mb-3" id="wall_fast_comment_{comm-id}" onMouseOver="ge('fast_del_{comm-id}').style.display = 'block'" onMouseOut="ge('fast_del_{comm-id}').style.display = 'none'">
                <div class="row">
                    <div class="col-auto">
                        <a class="avatar" href="/u{{ $row['user_id'] }}">
                            <img src="{ava}" alt="{name}" class="avatar-img rounded-circle">
                        </a>
                    </div>
                    <div class="col ml-n2">
                        <div class="comment-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="comment-title">{name}</h5>
                                </div>
                                <div class="col-auto">
                                    [not-owner]&nbsp;-&nbsp; <a href="#" onClick="wall.Answer('{{ $row['rec_id'] }}', '{comm-id}', '{name}'); return false" id="answer_lnk">Ответить</a>[/not-owner]
                                </div>
                                [owner]<a href="/" class="size10 fl_r no_display" id="fast_del_{comm-id}" onClick="wall.fast_comm_del('{comm-id}'); return false">Удалить</a>[/owner]
                            </div>
                        </div> <!-- / .row -->
                        <p class="comment-text">
                            {text}
                        </p>
                        <time class="comment-time">{date}</time>
                        <a href="#" onClick="wall.Answer('{{ $row['rec_id'] }}', '{comm-id}', '{name}'); return false" id="answer_lnk">Ответить</a>
                    </div>
                </div>
            </div> <!-- / .row -->
            @endif
        </div>
        @if($row['comment_form'])
        <hr>
        <div class="wall_fast_opened_form" id="fast_form">
            <input type="text" class="wall_inpst fast_form_width wall_fast_input" value="Комментировать..." id="fast_inpt_{{ $row['rec_id'] }}" onMouseDown="wall.fast_open_textarea('{{ $row['rec_id'] }}', 2); return false" style="margin:0px;width:688px" />
            <div class="no_display wall_fast_texatrea" id="fast_textarea_{{ $row['rec_id'] }}">
            <textarea class="wall_inpst fast_form_width wall_fast_text" style="height:33px;color:#000;margin:0px;width:688px" id="fast_text_{{ $row['rec_id'] }}"
            onKeyPress="if(event.keyCode == 10 || (event.ctrlKey && event.keyCode == 13))wall.fast_send('{{ $row['rec_id'] }}', '{author-id}', 2)"></textarea>
                <div class="button_div fl_l margin_top_5"><button onClick="wall.fast_send('{{ $row['rec_id'] }}', '{author-id}', 2); return false" id="fast_buts_{{ $row['rec_id'] }}">Отправить</button></div>
                <div class="wall_answer_for_comm fl_l">
                    <a class="cursor_pointer answer_comm_for" id="answer_comm_for_{{ $row['rec_id'] }}"></a>
                    <input type="hidden" class="answer_comm_id" id="answer_comm_id{{ $row['rec_id'] }}" />
                </div>
            </div>
            <div class="clear"></div>
        </div>@endif
    </div>
@endforeach