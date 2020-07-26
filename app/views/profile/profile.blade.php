@extends('app.app')
@section('content')
<script type="text/javascript">
    {{-- [group=0][after-auth]Profile.LoadPhoto();[/after-reg][/group] --}}
        var startResizeCss = false;
    var user_id = '{{ $user_id }}';
    $(document).ready(function(){
        $('#wall_text, .fast_form_width').autoResize();
        @if($owner_block_block)
        if($('.profile_onefriend_happy').size() > 4) $('#happyAllLnk').show();
        @endif
    });
    // $(document).click(function(event){
    // 	wall.event(event);
    // });
</script>


<div class="d-flex justify-content-center">
    <div class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-6 mt-2">

        <input type="hidden" id="type_page" value="profile" />
        <div id="jquery_jplayer"></div>
        <input type="hidden" id="teck_id" value="" />
        <input type="hidden" id="teck_prefix" value="" />
        <input type="hidden" id="typePlay" value="standart" />

        <div class="d-sm-flex justify-content-center">
            <div class="col-sm-4 m-2">

                <div class="card">
                    <div class="card-body">
                        <div class="">
                            @if($owner)
                            <div class="cover_newava">
   <span id="ava" class="d-flex justify-content-center">
    <img class="w-100" src="{{ $ava }}" alt="" title="" id="ava_{{ $user_id }}" />
   </span>
                            </div>

                            <div class="owner_photo_bubble_wrap">
                                <div class="owner_photo_bubble"><div class="owner_photo_bubble_action owner_photo_bubble_action_update" onclick="Profile.LoadPhoto()" tabindex="0" role="button">
                                        <span class="owner_photo_bubble_action_in">Обновить фотографию</span>
                                    </div><div class="owner_photo_bubble_action owner_photo_bubble_action_crop" onclick="Profile.miniature()" tabindex="0" role="button">
                                        <span class="owner_photo_bubble_action_in">Изменить миниатюру</span>
                                    </div></div>
                            </div>
                            @else
                                <div class="cover_newava" >
                                    <span id="ava">
                                        <img class="w-100" src="{{ $ava }}" alt="" title="" id="ava_{{ $user_id }}" />
                                    </span>
                                </div>
                            @endif
                            <hr>
                            <div class="row">
                                <div class="col-10">
                                    @if($not_owner)
                                    <div class="row">
                                        {{ $privacy_msg }}
                                        <a href="/" onClick="gifts.box('{{ $user_id }}'); return false">
                                            <svg class="bi bi-gift" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M2 6v8.5a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V6h1v8.5a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 1 14.5V6h1zm8-5a1.5 1.5 0 0 0-1.5 1.5c0 .098.033.16.12.227.103.081.272.15.49.2A3.44 3.44 0 0 0 9.96 3h.015L10 2.999l.025.002h.014A2.569 2.569 0 0 0 10.293 3c.17-.006.387-.026.598-.073.217-.048.386-.118.49-.199.086-.066.119-.13.119-.227A1.5 1.5 0 0 0 10 1zm0 3h-.006a3.535 3.535 0 0 1-.326 0 4.435 4.435 0 0 1-.777-.097c-.283-.063-.614-.175-.885-.385A1.255 1.255 0 0 1 7.5 2.5a2.5 2.5 0 0 1 5 0c0 .454-.217.793-.506 1.017-.27.21-.602.322-.885.385a4.434 4.434 0 0 1-1.104.099H10z"/>
                                                <path fill-rule="evenodd" d="M6 1a1.5 1.5 0 0 0-1.5 1.5c0 .098.033.16.12.227.103.081.272.15.49.2A3.44 3.44 0 0 0 5.96 3h.015L6 2.999l.025.002h.014l.053.001a3.869 3.869 0 0 0 .799-.076c.217-.048.386-.118.49-.199.086-.066.119-.13.119-.227A1.5 1.5 0 0 0 6 1zm0 3h-.006a3.535 3.535 0 0 1-.326 0 4.435 4.435 0 0 1-.777-.097c-.283-.063-.614-.175-.885-.385A1.255 1.255 0 0 1 3.5 2.5a2.5 2.5 0 0 1 5 0c0 .454-.217.793-.506 1.017-.27.21-.602.322-.885.385a4.435 4.435 0 0 1-1.103.099H6zm1.5 12V6h1v10h-1z"/>
                                                <path fill-rule="evenodd" d="M15 4H1v1h14V4zM1 3a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H1z"/>
                                            </svg>
                                            <span>Отправить подарок</span></a>
                                    </div>
                                    @endif
                                    @if($owner)

                                    <!--<a class="nav-link active w-100" href="/edit/" onClick="Page.Go(this.href); return false;"> -->
                                    <a class="nav-link active w-100"  onClick="Profile_edit.Open()">
                                        <svg class="bi bi-brush" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.213 1.018a.572.572 0 0 1 .756.05.57.57 0 0 1 .057.746C15.085 3.082 12.044 7.107 9.6 9.55c-.71.71-1.42 1.243-1.952 1.596-.508.339-1.167.234-1.599-.197-.416-.416-.53-1.047-.212-1.543.346-.542.887-1.273 1.642-1.977 2.521-2.35 6.476-5.44 7.734-6.411z"/>
                                            <path d="M7 12a2 2 0 0 1-2 2c-1 0-2 0-3.5-.5s.5-1 1-1.5 1.395-2 2.5-2a2 2 0 0 1 2 2z"/>
                                        </svg>
                                        Редактировать</a>
                                        @endif
                                        @if($no_friends_block AND  $blacklist_block AND $not_owner)
                                    <a class="nav-link active" href="/" onClick="friends.add({{ $user_id }}); return false">
                                        <svg class="bi bi-person-plus" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M11 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1h10zm-9.995-.944v-.002.002zM1.022 13h9.956a.274.274 0 0 0 .014-.002l.008-.002c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664a1.05 1.05 0 0 0 .022.004zm9.974.056v-.002.002zM6 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm4.5 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
                                            <path fill-rule="evenodd" d="M13 7.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0v-2z"/>
                                        </svg>
                                        <span>Добавить</span></a>
                                        @endif
                                        @if($yes_friends_block AND $not_owner)
                                        <a class="nav-link active" href="/" onClick="friends.delet({{ $user_id }}, 1); return false">
                                        <svg class="bi bi-person-dash" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M11 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1h10zm-9.995-.944v-.002.002zM1.022 13h9.956a.274.274 0 0 0 .014-.002l.008-.002c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664a1.05 1.05 0 0 0 .022.004zm9.974.056v-.002.002zM6 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm2 2.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5z"/>
                                        </svg>
                                        <span>Убрать</span></a>
                                        @endif
                                        @if($no_subscription_block AND  $blacklist_block AND $not_owner)
                                        <a class="nav-link active" href="/" onClick="subscriptions.add({{ $user_id }}); return false" id="lnk_unsubscription"><div>
                                                <span id="text_add_subscription">Подписаться на обновления</span>
                                                <img src="/images/loading_mini.gif" alt="" id="addsubscription_load" class="no_display" style="margin-right:-13px" />
                                            </div>
                                        </a>
                                        @endif
                                        @if($yes_subscription_block AND $not_owner)
                                            <a class="nav-link active" href="/" onClick="subscriptions.del({{ $user_id }}); return false" id="lnk_unsubscription"><div><span id="text_add_subscription">Отписаться от обновлений</span> <img src="/images/loading_mini.gif" alt="" id="addsubscription_load" class="no_display" style="margin-right:-13px" /></div></a>
                                        @endif
                                </div>
                                <div class="col-2">
                                    <div class="dropdown">
                                        <a href="#" onclick="openUserMenu(this);" onmouseout="hideUserMenu()" onmouseover="removeTimer('hideusermenu')" id="usermenubut" class="dropdown-ellipses dropdown-toggle bg-secondary rounded text-white pl-1 pr-1" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 2em">
                                            <svg class="bi bi-chat-dots" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"></path>
                                            </svg>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right user_menu" onmouseover="removeTimer('hideusermenu')" onmouseout="hideUserMenu()">@if($owner)
                                            <a href="/my_stats/" class="dropdown-item">Статистика страницы</a>
                                            <a href="/docs/" class="dropdown-item">Мои документы</a>
                                            <a href="/edit/" class="dropdown-item">Редактировать страницу</a>
                                            <a href="#" onClick="Profile.LoadPhoto(); return false;" class="dropdown-item">Изменить фотографию</a>
                                            <a href="#" onClick="Profile.LoadPhoto(); return false;" class="dropdown-item">Изменить миниатюру</a>
                                            <a href="#" onClick="Profile.DelPhoto(); return false;" id="del_pho_but" {{ $display_ava }} class="dropdown-item">Удалить миниатюру</a>
                                            @else
                                            [no-fave]<a href="/" class="dropdown-item" onClick="fave.add({{ $user_id }}); return false" id="addfave_but">
                                                <svg class="bi bi-star-fill" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.283.95l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                                                </svg>
                                                <span><span id="text_add_fave">Добавить в закладки</span> <img src="/images/loading_mini.gif" alt="" id="addfave_load" class="no_display" /></span></a>[/no-fave]
                                            [yes-fave]<a href="/" class="dropdown-item" onClick="fave.delet({{ $user_id }}); return false" id="addfave_but">
                                                <svg class="bi bi-star" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.523-3.356c.329-.314.158-.888-.283-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.767l-3.686 1.894.694-3.957a.565.565 0 0 0-.163-.505L1.71 6.745l4.052-.576a.525.525 0 0 0 .393-.288l1.847-3.658 1.846 3.658a.525.525 0 0 0 .393.288l4.052.575-2.906 2.77a.564.564 0 0 0-.163.506l.694 3.957-3.686-1.894a.503.503 0 0 0-.461 0z"/>
                                                </svg>
                                                <div><span id="text_add_fave">Удалить из закладок</span> <img src="/images/loading_mini.gif" alt="" id="addfave_load" class="no_display" /></div></a>[/yes-fave]
                                            [no-blacklist]<a href="/" class="dropdown-item" onClick="settings.addblacklist({{ $user_id }}); return false" id="addblacklist_but">
                                                <svg class="bi bi-exclamation-circle-fill" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                                </svg>
                                                <span><span id="text_add_blacklist">Заблокировать</span> <img src="/images/loading_mini.gif" alt="" id="addblacklist_load" class="no_display" /></span></a>[/no-blacklist]
                                            [yes-blacklist]<a href="/" class="dropdown-item" onClick="settings.delblacklist({{ $user_id }}, 1); return false" id="addblacklist_but">
                                                <svg class="bi bi-exclamation-circle" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                                                </svg>
                                                <span><span id="text_add_blacklist">Разблокировать</span> <img src="/images/loading_mini.gif" alt="" id="addblacklist_load" class="no_display" /></span></a>[/yes-blacklist]

                                            [no-friends][blacklist]<a class="nav-link active" href="/" onClick="friends.add({{ $user_id }}); return false">
                                                <svg class="bi bi-person-plus" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M11 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1h10zm-9.995-.944v-.002.002zM1.022 13h9.956a.274.274 0 0 0 .014-.002l.008-.002c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664a1.05 1.05 0 0 0 .022.004zm9.974.056v-.002.002zM6 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm4.5 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
                                                    <path fill-rule="evenodd" d="M13 7.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0v-2z"/>
                                                </svg>
                                                <span>Добавить</span></a>[/blacklist][/no-friends]
                                            [yes-friends]<a class="nav-link active" href="/" onClick="friends.delet({{ $user_id }}, 1); return false">
                                                <svg class="bi bi-person-dash" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M11 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1h10zm-9.995-.944v-.002.002zM1.022 13h9.956a.274.274 0 0 0 .014-.002l.008-.002c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664a1.05 1.05 0 0 0 .022.004zm9.974.056v-.002.002zM6 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm2 2.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5z"/>
                                                </svg>
                                                <span>Убрать</span></a>[/yes-friends]
                                            [blacklist][no-subscription]<a class="nav-link active" href="/" onClick="subscriptions.add({{ $user_id }}); return false" id="lnk_unsubscription"><div><span id="text_add_subscription">Подписаться на обновления</span> <img src="/images/loading_mini.gif" alt="" id="addsubscription_load" class="no_display" style="margin-right:-13px" /></div></a>[/no-subscription][/blacklist]
                                            [yes-subscription]<a class="nav-link active" href="/" onClick="subscriptions.del({{ $user_id }}); return false" id="lnk_unsubscription"><div><span id="text_add_subscription">Отписаться от обновлений</span> <img src="/images/loading_mini.gif" alt="" id="addsubscription_load" class="no_display" style="margin-right:-13px" /></div></a>[/yes-subscription]

                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- [blacklist] --}}
                <div class="mt-2">
                    <div class="">
                        <div class="row">
                            @if($happy_friends_block AND $blacklist_block)<div id="happyBLockSess"><div class="albtitle">Дни рожденья друзей <span>{happy-friends-num}</span><div class="profile_happy_hide"><img src="/images/hide_lef.gif" onMouseOver="myhtml.title('1', 'Скрыть', 'happy_block_')" id="happy_block_1" onClick="HappyFr.HideSess(); return false" /></div></div>
                                <div class="newmesnobg profile_block_happy_friends" style="padding:0px;padding-top:10px;">{happy-friends}<div class="clear"></div></div>
                                <div class="cursor_pointer no_display" onMouseDown="HappyFr.Show(); return false" id="happyAllLnk"><div class="public_wall_all_comm profile_block_happy_friends_lnk">Показать все</div></div></div>
                            @endif
                            @if($mutual_friends AND $blacklist_block)
                                <div class="col-12 mt-3">
                                    <div class="card">
                                    <a href="/friends/common/{{ $user_id }}/" style="text-decoration:none" onClick="Page.Go(this.href); return false">
                                    <div class="albtitle">Общие друзья <span>{{ $mutual_num }}</span></div></a>
                                    <div class="newmesnobg" style="padding:0px;padding-top:10px;">
                                        @foreach($mutual_friends as $row)
                                            <div class="onefriend">
                                                <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">
                                                    <div>
                                                        <img src="{{ $row['ava'] }}" alt="" />
                                                    </div>{{ $row['name'] }}<br /><span>{{ $row['last_name'] }}</span>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                </div>
                                @endif
                            @if($all_friends AND $blacklist_block)
                                <div class="col-12 mt-3">
                                    <div class="card">
                                    <a href="/friends/common/{{ $user_id }}/" style="text-decoration:none" onClick="Page.Go(this.href); return false">
                                        <div class="albtitle">Друзья <span>{{ $all_friends_num }}</span></div>
                                    </a>
                                    <div class="newmesnobg" style="padding:0px;padding-top:10px;">
                                        @foreach($all_friends as $row)
                                            <div class="onefriend">
                                                <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">
                                                    <div>
                                                        <img src="{{ $row['ava'] }}" alt="" />
                                                    </div>{{ $row['name'] }}<br /><span>{{ $row['last_name'] }}</span>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                    </div>
                                </div>
                            @endif

                            @if($all_online_friends AND $blacklist_block)
                                <div class="col-12 mt-3">
                                    <div class="card">
                                    <a href="/friends/common/{{ $user_id }}/" style="text-decoration:none" onClick="Page.Go(this.href); return false">
                                        <div class="albtitle">Друзья онлайн <span>{{ $all_online__friends_num }}</span></div>
                                    </a>
                                    <div class="newmesnobg" style="padding:0px;padding-top:10px;">
                                        @foreach($all_online_friends as $row)
                                            <div class="onefriend">
                                                <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">
                                                    <div>
                                                        <img src="{{ $row['ava'] }}" alt="" />
                                                    </div>{{ $row['name'] }}<br /><span>{{ $row['last_name'] }}</span>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                </div>
                            @endif
                            @if($subscriptions AND $blacklist_block)
                                <div class="col-12 mt-3">
                                    <div class="card">
                                    <a href="/friends/common/{{ $user_id }}/" style="text-decoration:none" onClick="Page.Go(this.href); return false">
                                        <div class="albtitle">Подписки <span>{{ $subscriptions_num }}</span></div>
                                    </a>
                                    <div class="newmesnobg" style="padding:0px;padding-top:10px;">
                                        @foreach($subscriptions as $row)
                                            <div class="onesubscription onesubscriptio2n">
                                                <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">
                                                    <img src="{{ $row['ava'] }}" alt="" />
                                                    <div class="onesubscriptiontitle">{{ $row['name'] }}</div>
                                                </a>
                                                <div class="nesubscriptstatus">{{ $row['info'] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                </div>
                            @endif

                            @if($groups AND $blacklist_block)
                                <div class="col-12 mt-3" >
                                    <div class="card">
                                    <div class="albtitle cursor_pointer" onClick="groups.all_groups_user('{{ $user_id }}')">Сообщества <span id="groups_num">{{ $groups_num }}</span></div>
                                          <div class="newmesnobg" style="padding:0px;padding-top:10px;">
                                        @foreach($groups as $row)
                                            <div class="onesubscription onesubscriptio2n">
                                                <a href="/u{{ $row['user_id'] }}" onClick="Page.Go(this.href); return false">
                                                    <img src="{{ $row['ava'] }}" alt="" />
                                                    <div class="onesubscriptiontitle">{{ $row['name'] }}</div>
                                                </a>
                                                <div class="nesubscriptstatus">{{ $row['info'] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                </div>
                            @endif
                            @if($videos AND $blacklist_block)
                                <div class="col-12 mt-3">
                                    <div class="card">
                                    <a href="/videos/{{ $user_id }}/" style="text-decoration:none" onClick="Page.Go(this.href); return false">
                                        <div class="albtitle">Видеозаписи <span>{{ $avideos_num }}</span></div>
                                    </a>
                                    <div class="newmesnobg" style="padding:0px;padding-top:10px;">
                                        @foreach($videos as $row)
                                            <div class="profile_one_video">
                                                <a href="/video/{{ $row['user_id'] }}/{id}/wall/{{ $row['user_id'] }}" onClick="videos.show({id}, this.href, '/u{{ $row['user_id'] }}'); return false">
                                                    <img src="{{ $row['photo'] }}" alt="" />
                                                </a>
                                                <div class="video_profile_title">
                                                    <a href="/video/{{ $row['user_id'] }}/{id}/wall/{{ $row['user_id'] }}/" onClick="videos.show({id}, this.href, '/u{{ $row['user_id']}}'); return false">{{ $row['title'] }}</a>
                                                </div>
                                                <div class="nesubscriptstatus">{{ $row['date'] }} | <a href="/video/{{ $row['user_id'] }}/{id}/wall/{{ $row['user_id'] }}/" onClick="videos.show({id}, this.href, '/u{{ $row['user_id'] }}'); return false">{{ $row['comm_num'] }}</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                </div>
                            @endif
                            <span id="fortoAutoSizeStyleProfile"></span>
                        </div>
                    </div>
                </div>
              {{-- [/blacklist] --}}

            </div>
            <div class="col-sm-8 m-2">
                <div class="card">
                    <div class="card-body">
                        <div class="padcont2 mt-3 shadow2">
                            <div class="profiewr">
                                @if($owner)<div class="set_status_bg no_display" id="set_status_bg">
                                    <label for="status_text"></label>
                                    <input type="text" id="status_text" class="status_inp" value="{status-text}" style="width:500px;" maxlength="255" onKeyPress="if(event.keyCode == 13)gStatus.set()" />
                                    <div class="fl_l status_text"><span class="no_status_text [status]no_display[/status]">Введите здесь текст Вашего статуса.</span><a href="/" class="yes_status_text [no-status]no_display[/no-status]" onClick="gStatus.set(1); return false">Удалить статус</a></div>
                                    {{ $status_block2 }}
                                    <div class="fl_r status_but"><button class="btn btn-secondary" id="status_but" onClick="gStatus.set()">Сохранить</button></div>
                                </div>@endif
                                <div class="titleu">{{ $name }} {{ $lastname }} <a class="fl_r color777" style="text-decoration:none"><b>{{ $online }}</b></a></div>
                                <div class="status">
                                    {{ $status_text }}
                                    @if($owner)
                                    <span id="tellBlockPos"></span>
                                    <div class="status_tell_friends no_display">
                                        <div class="status_str"></div>
                                        <div class="html_checkbox" id="tell_friends" onClick="myhtml.checkbox(this.id); gStatus.startTell()">Рассказать друзьям</div>
                                    </div>
                                    <a href="#" onClick="gStatus.open(); return false" id="status_link" {{ $status_block }}>установить статус</a>
                                    @endif
                                </div>
                                <div class="profile_rate_pos">
                                    <div class="profile_rate_text">Рейтинг</div>
                                    @if($owner)
                                        <a class="cursor_pointer" onClick="doLoad.data(1); rating.view()">
                                            <div class="profile_rate_100_left {{ $rating_class_left }}"></div>
                                        </a>
                                        <div class="profile_rate_add" onClick="doLoad.data(1); rating.addbox('{{ $user_id }}')" onMouseOver="myhtml.title('1', 'Повысить рейтинг', 'rate', 1)" id="rate1">
                                            <img src="/images/icons/rate_ic.png"  alt=\"\" />
                                        </div>
                                        <a class="cursor_pointer" onClick="doLoad.data(1); rating.view()" style="text-decoration:none">
                                            <div class="profile_rate_100_right {{ $rating_class_right }}"></div>
                                            <div class="profile_rate_100_head {{ $rating_class_head }}" id="profile_rate_num">{{ $rating }}</div>
                                        </a>
                                    @else
                                            <div class="profile_rate_100_left {{ $rating_class_left }}"></div>
                                        <div class="profile_rate_add" onClick="doLoad.data(1); rating.addbox('{{ $user_id }}')" onMouseOver="myhtml.title('1', 'Повысить рейтинг', 'rate', 1)" id="rate1">
                                            <img src="/images/icons/rate_ic.png"  alt=\"\" />
                                        </div>
                                            <div class="profile_rate_100_right {{ $rating_class_right }}"></div>
                                            <div class="profile_rate_100_head {{ $rating_class_head }}" id="profile_rate_num">{{ $rating }}</div>
                                    @endif
                                </div>
                                <div style="min-height:50px">
                                    @if($not_all_country_block)
                                    <div class="flpodtext">Страна:</div> <div class="flpodinfo">
                                        <a href="/search/?country={country-id}" onClick="Page.Go(this.href); return false">{country}</a>
                                        </div>
                                        </div>
                                    @endif
                                    @if($not_all_city_block)
                                    <div class="flpodtext">Город:</div> <div class="flpodinfo">
                                        <a href="/search/?country={country-id}&city={city-id}" onClick="Page.Go(this.href); return false">{city}</a>
                                    </div></div>
                                    @endif
                                    @if($not_all_birthday_block_block AND $blacklist_block)
                                        <div class="flpodtext">День рождения:</div> <div class="flpodinfo">{{ $birth_day }}</div>
                                    @endif
                                    @if($sp AND $privacy_info_block)
                                    <div class="flpodtext">Семейное положение:</div> <div class="flpodinfo">{{ $sp }}</div>
                                    @endif
                                </div>
                                <div class="cursor_pointer" onClick="Profile.MoreInfo(); return false" id="moreInfoLnk"><div class="public_wall_all_comm profile_hide_opne" id="moreInfoText">Показать подробную информацию</div></div>
                                <div id="moreInfo" class="no_display">
                                @if($privacy_info AND $not_block_contact AND $not_owner)
                                <div class="fieldset"><div class="w2_a" >Контактная информация </div></div>
                                @elseif($owner)
                                <div class="fieldset"><div class="w2_a" >Контактная информация <span><a href="/edit/contact/" onClick="Page.Go(this.href); return false;">редактировать</a></span></div></div>
                                @endif
                                @if($privacy_info AND $not_block_contact AND $not_contact_phone)
                                <div class="flpodtext">Моб. телефон:</div> <div class="flpodinfo">{phone}</div>
                                @endif
                                @if($privacy_info AND $not_block_contact AND $not_contact_vk)
                                <div class="flpodtext">В контакте:</div> <div class="flpodinfo">{{ $vk }}</div>
                                @endif
                                @if($privacy_info AND $not_block_contact AND $not_contact_od)
                                <div class="flpodtext">Одноклассники:</div> <div class="flpodinfo">{{ $od }}</div>
                                @endif
                                @if($privacy_info AND $not_block_contact AND $not_contact_fb)
                                <div class="flpodtext">FaceBook:</div> <div class="flpodinfo">{{ $fb }}</div>
                                @endif
                                @if($privacy_info AND $not_block_contact AND $not_contact_skype)
                                <div class="flpodtext">Skype:</div> <div class="flpodinfo"><a href="skype:{{ $skype }}">{{ $skype }}</a></div>
                                @endif
                                @if($privacy_info AND $not_block_contact AND $not_contact_icq)
                                <div class="flpodtext">ICQ:</div> <div class="flpodinfo">{{ $icq }}</div>
                                @endif
                                @if($privacy_info AND $not_block_contact AND $not_contact_site)
                                <div class="flpodtext">Веб_сайт:</div> <div class="flpodinfo">{{ $site }}</div>
                                @endif

                                @if($privacy_info AND $not_block_info AND $not_owner)
                                <div class="fieldset"><div class="w2_b" >Личная информация </div></div>
                                @elseif($owner)
                                <div class="fieldset"><div class="w2_b" style="width:200px;">Личная информация <span>
                                <a href="/edit/interests/" onClick="Page.Go(this.href); return false;">редактировать</a></span></div></div>
                                @endif
                                @if($privacy_info AND $not_block_info AND $not_info_activity)
                                    <div class="flpodtext">Деятельность:</div> <div class="flpodinfo">{{ $activity }}</div>
                                @endif
                                @if($privacy_info AND $not_block_info AND $not_info_interests)
                                    <div class="flpodtext">Интересы:</div> <div class="flpodinfo">{{ $interests }}</div>
                                @endif
                                @if($privacy_info AND $not_block_info AND $not_info_music)
                                    <div class="flpodtext">Любимая музыка:</div> <div class="flpodinfo">{{ $music }}</div>
                                @endif
                                @if($privacy_info AND $not_block_info AND $not_info_kino)
                                    <div class="flpodtext">Любимые фильмы:</div> <div class="flpodinfo">{{ $kino }}</div>
                                @endif
                                @if($privacy_info AND $not_block_info AND $not_info_books)
                                    <div class="flpodtext">Любимые книги:</div> <div class="flpodinfo">{{ $books }}</div>
                                @endif
                                @if($privacy_info AND $not_block_info AND $not_info_games)
                                    <div class="flpodtext">Любимые игры:</div> <div class="flpodinfo">{{ $games }}</div>
                                @endif
                                @if($privacy_info AND $not_block_info AND $not_info_quote)
                                    <div class="flpodtext">Любимые цитаты:</div> <div class="flpodinfo">{{ $quote }}</div>
                                @endif
                                @if($privacy_info AND $not_block_info AND $not_info_myinfo)
                                    <div class="flpodtext">О себе:</div> <div class="flpodinfo">{{ $myinfo }}</div>
                                @endif
                        </div>

                        @if($albums AND $blacklist_block)
                            <a href="/albums/{{ $user_id }}" onClick="Page.Go(this.href); return false" style="text-decoration:none">
                                <div class="albtitle" style="margin-top:5px">Альбомы <span>{{ $albums_num }}</span>
                                    <div><b>Все</b></div>
                                </div>
                            </a>
                            @foreach($albums as $row)
                                <a href="/albums/view/{{ $row['aid'] }}" onClick="Page.Go(this.href); return false" style="text-decoration:none">
                                    <div class="profile_albums">
                                        <img src="{{ $row['album_cover'] }}"  alt="{{ $row['name'] }}"/>
                                        <div class="profile_title_album">{{ $row['name'] }}</div>{{ $row['photo_num'] }} {{ $row['albums_photonums'] }}<br />Обновлён {{ $row['date'] }}
                                        <div class="clear"></div>
                                    </div>
                                </a>
                            @endforeach
                        @endif

                        @if($audios AND $blacklist_block)
                        <div id="jquery_jplayer"></div>
                        <input type="hidden" id="teck_id" value="1" />
                        <a href="/audio{{ $user_id }}" onClick="Page.Go(this.href); return false" style="text-decoration:none">
                            <div class="albtitle" style="margin-top:5px">{audios-num}{{ $audios_num }}
                                <div><b>Все</b></div>
                            </div>
                        </a>
                        @foreach($audios as $row)
                            {{-- Deprecated html tags!!! --}}
                                <div class="audioPage audioElem" id="audio_{$row_audio['id']}_{$id}_{$plname}"
                                     onclick="playNewAudio('{$row_audio[\'id\']}_{$id}_{$plname}', event);">
                                    <div class="area">
                                        <table cellspacing="0" cellpadding="0" width="100%">
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <div class="audioPlayBut new_play_btn"><div class="bl"><div class="figure"></div></div></div>
                                                    <input type="hidden" value="{{ $row['url'] }},{{ $row['duration'] }},page" id="audio_url_{{ $row['id'] }}_{{ $user_id }}_{{ $row['plname'] }}">
                                                </td>
                                                <td class="info">
                                                    <div class="audioNames"><b class="author" onclick="Page.Go('/?go=search&query={{ $row['search_artist'] }}&type=5&n=1'); return false;"
                                                                               id="artist">{{ $row['artist'] }}</b> –
                                                        <span class="name" id="name">{{ $row['title'] }}</span> <div class="clear"></div></div>
                                                    <div class="audioElTime" id="audio_time_{{ $row['id'] }}_{{ $user_id }}_{{ $row['plname'] }}">{{ $row['stime'] }}</div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <div id="player{{ $row['id'] }}_{{ $user_id }}_{{ $row['plname'] }}" class="audioPlayer" border="0"
                                             cellpadding="0">
                                            <table cellspacing="0" cellpadding="0" width="100%">
                                                <tbody>
                                                <tr>
                                                    <td style="width: 100%;">
                                                        <div class="progressBar fl_l" style="width: 100%;" onclick="cancelEvent(event);"
                                                             onmousedown="audio_player.progressDown(event, this);" id="no_play"
                                                             onmousemove="audio_player.playerPrMove(event, this)"
                                                             onmouseout="audio_player.playerPrOut()">
                                                            <div class="audioTimesAP" id="main_timeView">
                                                                <div class="audioTAP_strlka">100%</div>
                                                            </div>
                                                            <div class="audioBGProgress"></div>
                                                            <div class="audioLoadProgress"></div>
                                                            <div class="audioPlayProgress" id="playerPlayLine"><div class="audioSlider"></div></div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="audioVolumeBar fl_l ml-2" onclick="cancelEvent(event);"
                                                             onmousedown="audio_player.volumeDown(event, this);" id="no_play">
                                                            <div class="audioTimesAP"><div class="audioTAP_strlka">100%</div></div>
                                                            <div class="audioBGProgress"></div>
                                                            <div class="audioPlayProgress" id="playerVolumeBar"><div class="audioSlider"></div></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                        @endforeach
                        <div class="clear"></div>
                        @endif

                        @if($gifts AND $blacklist_block)
                            <a href="/gifts{{ $user_id }}" onClick="Page.Go(this.href); return false" style="text-decoration:none">
                                <div class="albtitle" style="margin-top:5px">{{ $gifts_num }}<div>
                                        <b>Все</b>
                                    </div>
                                </div>
                                <div class="text-center">
                                    @foreach($gifts as $row)
                                        <img src="/uploads/gifts/{{ $row['gift'] }}.png" class="gift_onepage"  alt="{{ $row['gift'] }}" />
                                    @endforeach
                                </div>
                                <div class="clear"></div>
                            </a>
                        @endif
                    </div>
                    <div class="clear"></div>
                </div>
            </div>

        </div>
        <!-- </div> -->


        <div class="padcont2 mt-3 shadow2">
            <div class="profiewr">
                <a href="/wall/{{ $user_id }}/" onClick="Page.Go(this.href); return false" style="text-decoration:none"><div class="albtitle" style="border-bottom:0px">Публикации <span id="wall_rec_num">{{ $wall_rec_num }}</span></div></a>
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
                        <div class="attach_link_block_ic fl_l"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/" id="attatch_link_url" target="_blank"></a></div><img class="fl_l cursor_pointer" style="margin-top:2px;margin-left:5px" src="/images/close_a.png" onMouseOver="myhtml.title('1', 'Не прикреплять', 'attach_lnk_')" id="attach_lnk_1" onClick="wall.RemoveAttachLnk()" /></div>
                        <input type="hidden" id="attach_lnk_stared" />
                        <input type="hidden" id="teck_link_attach" />
                        <span id="urlParseImgs" class="no_display"></span>
                    </div>
                    <div class="clear"></div>
                    <div id="attach_block_vote" class="no_display">
                        <div class="attach_link_bg">
                            <div class="texta">Тема опроса:</div>
                            <label for="vote_title"></label>
                            <input type="text" id="vote_title" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px"
                                                                   onKeyUp="$('#attatch_vote_title').text(this.value)"
                            /><div class="mgclr"></div>
                            <div class="texta">Варианты ответа:<br /><small><span id="addNewAnswer"><a class="cursor_pointer" onClick="Votes.AddInp()">добавить</a></span> | <span id="addDelAnswer">удалить</span></small></div><input type="text" id="vote_answer_1" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px" /><div class="mgclr"></div>
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
                    <div class=" fl_l margin_top_10"><button onClick="wall.send(); return false" id="wall_send" class="btn btn-secondary">Отправить</button></div>
                    <div class="wall_attach fl_r" onClick="wall.attach_menu('open', this.id, 'wall_attach_menu')" onMouseOut="wall.attach_menu('close', this.id, 'wall_attach_menu')" id="wall_attach">Прикрепить</div>
                    <div class="wall_attach_menu no_display" onMouseOver="wall.attach_menu('open', 'wall_attach', 'wall_attach_menu')" onMouseOut="wall.attach_menu('close', 'wall_attach', 'wall_attach_menu')" id="wall_attach_menu">
                        <div class="wall_attach_icon_smile" id="wall_attach_link" onClick="wall.attach_addsmile()">Смайлик</div>
                        <div class="wall_attach_icon_photo" id="wall_attach_link" onClick="wall.attach_addphoto()">Фотографию</div>
                        <div class="wall_attach_icon_video" id="wall_attach_link" onClick="wall.attach_addvideo()">Видеозапись</div>
                        <div class="wall_attach_icon_audio" id="wall_attach_link" onClick="wall.attach_addaudio()">Аудиозапись</div>
                        <div class="wall_attach_icon_doc" id="wall_attach_link" onClick="wall.attach_addDoc()">Документ</div>
                        <div class="wall_attach_icon_vote" id="wall_attach_link" onClick="$('#attach_block_vote').slideDown('fast');wall.attach_menu('close', 'wall_attach', 'wall_attach_menu');$('#vote_title').focus();$('#vaLattach_files').val($('#vaLattach_files').val()+'vote|start||')">Опрос</div>
                    </div>
                    </div>
                    <div class="clear"></div>
                </div>
                @endif
                <div id="wall_records">
                @if($wall_rec_num_block AND $blacklist_block)
                    @include('wall.one_record', array(
    'wall_records' => $wall_records,
))

                @else
                   <div class="wall_none" >На стене пока нет ни одной записи.</div>
                @endif
                </div>
                @if($wall_link_block AND $blacklist_block)
                <span id="wall_all_record"></span>
                <div onClick="wall.page('{{ $user_id }}'); return false" id="wall_l_href" class="cursor_pointer">
                    <div class="photo_all_comm_bg wall_upgwi" id="wall_link">к предыдущим записям</div>
                </div>
                @endif
                @if(!$blacklist_block)
                <div class="err_yellow" style="font-weight:normal;margin-top:5px">{name} ограничила доступ к своей странице.</div>
                @endif
        </div>
        <div class="clear"></div>
    </div>

</div>
</div>

</div>
</div>
@endsection