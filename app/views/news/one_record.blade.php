@foreach($news as $row)
    <div id="link_tag_{{ $row['author_id'] }}_{{ $row['rec_id'] }}"></div>
    <div class="card mt-3" id="wall_record_{{ $row['rec_id'] }}">
        <div class="card-body">
            <div class="mb-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <a href="/{{ $row['link'] }}{{ $row['author_id'] }}" class="avatar" onmouseover="wall.showTag({{ $row['author_id'] }}, {{ $row['rec_id'] }}, {{ $row['action_type'] }})" onmouseout="wall.hideTag({{ $row['author_id'] }}, {{ $row['rec_id'] }}, {{ $row['action_type'] }})">
                            <img src="{{ $row['ava'] }}" alt="{{ $row['author'] }}" class="avatar-img rounded-circle">
                        </a>
                    </div>
                    <div class="col ml-n2">
                        <h4 class="mb-1"  onmouseover="wall.showTag({{ $row['author_id'] }}, {{ $row['rec_id'] }}, {{ $row['action_type'] }})" onmouseout="wall.hideTag({{ $row['author_id'] }}, {{ $row['rec_id'] }}, {{ $row['action_type'] }})">
                            {{ $row['author'] }}
                        </h4>
                        <p class="card-text small text-muted">
                            <span class="fe fe-clock"></span>{{ $row['action_type'] }} <time datetime="2018-05-24"> {{ $row['date'] }}</time>
                        </p>
                    </div>
                    <div class="col-auto">
                        @if(!$row['wall'])
                            <div class="wall_tell_all cursor_pointer" onMouseOver="myhtml.title('{{ $row['rec_id'] }}', 'Отправить в сообщество или другу', 'wall_tell_all_')" onClick="Repost.Box('{{ $row['rec_id'] }}'[groups], 1[/groups]); return false "id="wall_tell_all_{{ $row['rec_id'] }}" style="margin-top:-17px;margin-right:20px"></div>
                            <div class="wall_tell cursor_pointer wall_tell_fornews" onMouseOver="myhtml.title('{{ $row['rec_id'] }}', 'Рассказать друзьям', 'wall_tell_')" onClick="@if($row['wall_func'])wall.tell('{{ $row['rec_id'] }}'); return false @else groups.wall_tell('{{ $row['rec_id'] }}'); return false @endif " id="wall_tell_{{ $row['rec_id'] }}"></div>
                            <div class="wall_tell_ok no_display wall_tell_fornews" id="wall_ok_tell_{{ $row['rec_id'] }}"></div>
                        @endif
                    </div>
                </div> <!-- / .row -->
            </div>
            <!-- action_text -->
            <p class="mb-3">action_text
                {{ $row['action_text'] }}
            </p>
            @if($row['comments_link'])
                <div class="mb-3">
                                    <span id="fast_comm_link_{{ $row['rec_id'] }}" class="fast_comm_link">&nbsp;|&nbsp;
                                    <a href="/" id="fast_link_{{ $row['rec_id'] }}" onClick="wall.open_fast_form('{{ $row['rec_id'] }}'); wall.fast_open_textarea('{{ $row['rec_id'] }}'); return false">@_e('ttt')Комментировать</a>
                                    </span>
                </div>
            @endif
            @if(isset($row['wall']) AND $row['wall'])
                <div class="mb-3">
                    <div class="public_likes_user_block no_display"
                         id="public_likes_user_block{{ $row['rec_id'] }}" onMouseOver="groups.wall_like_users_five('{{ $row['rec_id'] }}'[wall-func], 'uPages'[/wall-func])" onMouseOut="groups.wall_like_users_five_hide('{{ $row['rec_id'] }}')" style="margin-left:585px">
                        <div onClick="[wall-func]wall.all_liked_users[/wall-func][groups]groups.wall_all_liked_users[/groups]('{{ $row['rec_id'] }}', '', '{likes}')">@_e('ttt')Понравилось {likes-text}</div>
                        <div class="public_wall_likes_hidden">
                            <div class="public_wall_likes_hidden2">
                                <a href="/u{{ $viewer_id }}" id="like_user{viewer-id}_{{ $row['rec_id'] }}" class="no_display" onClick="Page.Go(this.href); return false">
                                    wallrecord comm_wr news_comm_wr <img src="{{ $viewer_ava }}" width="32"  alt=""/></a>
                                <div id="likes_users{{ $row['rec_id'] }}"></div>
                            </div>
                        </div>
                        <div class="public_like_strelka"></div>
                    </div>
                    <input type="hidden" id="update_like{{ $row['rec_id'] }}" value="0" />
                    <div class="fl_r public_wall_like cursor_pointer" onClick="{like-js-function}" onMouseOver="groups.wall_like_users_five('{{ $row['rec_id'] }}'[wall-func], 'uPages'[/wall-func])" onMouseOut="groups.wall_like_users_five_hide('{{ $row['rec_id'] }}')" id="wall_like_link{{ $row['rec_id'] }}">
                        <div class="fl_l" id="wall_like_active">Мне нравится</div>
                        <div class="public_wall_like_no {yes-like}" id="wall_active_ic{{ $row['rec_id'] }}"></div>
                        <b id="wall_like_cnt{{ $row['rec_id'] }}" class="{yes-like-color}">{likes}</b>
                    </div>
                </div>
            @endif
            @if($row['comments_link'])
                <hr>
                <div class="comment mb-3 ">
                    <div class="wall_fast_form no_display" id="fast_form_{{ $row['rec_id'] }}" style="margin-top:22px">
                        <div class="no_display wall_fast_texatrea" id="fast_textarea_{{ $row['rec_id'] }}">
                            <label for="fast_text_{{ $row['rec_id'] }}"></label>
                            <textarea class="wall_inpst fast_form_width wall_fast_text" style="height:33px;color:#000;margin:0px;;width:100%" id="fast_text_{{ $row['rec_id'] }}" onKeyPress="if(event.keyCode == 10 || (event.ctrlKey && event.keyCode == 13))[wall-func]wall.fast_send[/wall-func][groups]groups.wall_send_comm[/groups]('{{ $row['rec_id'] }}', '{{ $row['author_id'] }}', 1)"></textarea>
                            <div class="button_div fl_l margin_top_5">
                                <button id="fast_buts_{{ $row['rec_id'] }}" onClick="[wall-func]wall.fast_send[/wall-func][groups]groups.wall_send_comm[/groups]('{{ $row['rec_id'] }}', '{{ $row['author_id'] }}', 1); return false" >Отправить</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            {{--                            [/record]--}}


            @if($row['all_comm'])
                <div class="cursor_pointer" onClick="[wall-func]wall.all_comments('{{ $row['rec_id'] }}', '{{ $row['author_id'] }}', 1); return false[/wall-func][groups]groups.wall_all_comments('{{ $row['rec_id'] }}', '{{ $row['author_id'] }}'); return false[/groups]" id="wall_all_but_link_{{ $row['rec_id'] }}">
                    <div class="public_wall_all_comm" id="wall_all_comm_but_{{ $row['rec_id'] }}">Показать {{ $gram_record_all_comm }}</div>
                </div>
            @endif

            @if($row['comment'])
                <hr>
                <div class="comment mb-3" id="wall_fast_comment_{comm-id}" onMouseOver="ge('fast_del_{comm-id}').style.display = 'block'" onMouseOut="ge('fast_del_{comm-id}').style.display = 'none'">
                    <div class="row">
                        <div class="col-auto">
                            <a class="avatar" href="/u{user-id}">
                                <img src="{{ $row['ava'] }}" alt="{name}" class="avatar-img rounded-circle">
                            </a>
                        </div>
                        <div class="col ml-n2">
                            <div class="comment-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="comment-title">{name}</h5>
                                    </div>
                                    <div class="col-auto">
                                        [owner]
                                        <a href="/" class="size10 fl_r no_display" id="fast_del_{comm-id}" onClick="[wall-func]wall.fast_comm_del('{comm-id}')[/wall-func][groups]groups.comm_wall_delet('{comm-id}', '{public-id}')[/groups]; return false">@_e('ttt')Удалить</a>
                                        [/owner]
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
                </div>
            @endif
            @if(!$row['comment_form'])
                <hr>
                <div class="wall_fast_opened_formr">
                    <style>.form-control-flush {padding-left: 0;padding-right: 0;border-color: transparent!important;background-color: transparent!important;resize: none;}</style>
                    <div class="row">
                        <div class="col-auto">
                            <div class="avatar avatar-sm">
                                <img src="/images/no_ava_50.png" alt="..." class="avatar-img rounded-circle">
                            </div>
                        </div>
                        <div class="col ml-n2 form-control-flush"  id="fast_form">
                            <label for="fast_inpt_{{ $row['rec_id'] }}"></label>
                            <input type="text" class="wall_inpst fast_form_width wall_fast_input form-control-flush" value="Комментировать...r" id="fast_inpt_{{ $row['rec_id'] }}" onMouseDown="wall.fast_open_textarea('{{ $row['rec_id'] }}', 2); return false" style="margin:0px;width:100%" />
                            <div class="no_display wall_fast_texatrea" id="fast_textarea_{{ $row['rec_id'] }}">
                                <textarea class="wall_inpst fast_form_width wall_fast_text form-control-flush" style="height:33px;color:#000;margin:0px;;width:100%" id="fast_text_{{ $row['rec_id'] }}" onKeyPress="if(event.keyCode == 10 || (event.ctrlKey && event.keyCode == 13))[wall-func]wall.fast_send[/wall-func][groups]groups.wall_send_comm[/groups]('{{ $row['rec_id'] }}', '{{ $row['author_id'] }}', 1)"></textarea>
                                <div class="float-right mt-2">
                                    <button id="fast_buts_{{ $row['rec_id'] }}" class="btn btn-success" onClick="[wall-func]wall.fast_send[/wall-func][groups]groups.wall_send_comm[/groups]('{{ $row['rec_id'] }}', '{{ $row['author_id'] }}', 1); return false">@_e('ttt')Отправить</button>
                                </div>
                                <div class="wall_answer_for_comm fl_l">
                                    <a class="cursor_pointer answer_comm_for" id="answer_comm_for_{{ $row['rec_id'] }}"></a>
                                    <input type="hidden" class="answer_comm_id" id="answer_comm_id{{ $row['rec_id'] }}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-auto align-self-end">
                            <div class="text-muted mb-2">
                                <a class="text-reset mr-3" href="#!" data-toggle="tooltip" title="" data-original-title="Add photo">
                                    <i class="fe fe-camera"></i>
                                </a>
                                <a class="text-reset mr-3" href="#!" data-toggle="tooltip" title="" data-original-title="Attach file">
                                    <i class="fe fe-paperclip"></i>
                                </a>
                                <a class="text-reset" href="#!" data-toggle="tooltip" title="" data-original-title="Record audio">
                                    <i class="fe fe-mic"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endforeach