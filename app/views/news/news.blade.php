@extends('app.app')
@section('content')
    <script type="text/javascript">
        var page_cnt = 1;
        $(document).ready(function(){
            // $('#wall_text, .fast_form_width').autoResize();
            $(window).scroll(function(){
                if($(document).height() - $(window).height() <= $(window).scrollTop()+($(document).height()/2-250)){
                    news.load();
                }
            });
        });
        $(document).click(function(event){
            wall.event(event);
        });
    </script>
<div class="d-flex justify-content-between">
    <div class="col-2 d-none d-sm-none d-md-none d-lg-flex flex-column align-content-between justify-content-between">
        <div class="d-flex flex-column align-content-between justify-content-between nav_menu">
            <div class="d-flex flex-column align-content-between justify-content-between ">
                <div class="d-flex flex-column p-3" >
{{--                    <img src='@asset("img/resource.jpg")' />--}}
                    <a href="/u{{ $user_id }}" onclick="Page.Go(this.href); return false;" class="left_row">
                        <svg class="bi bi-chevron-right" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M13 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1h10zm-9.995-.944v-.002.002zM3.022 13h9.956a.274.274 0 0 0 .014-.002l.008-.002c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664a1.05 1.05 0 0 0 .022.004zm9.974.056v-.002.002zM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        </svg>
                        <span class="left_label inl_bl ">@_e('my_page')</span>
                    </a>
                    <a href="/im/" onclick="Page.Go(this.href); return false;" class="left_row">
                        <svg class="bi bi-chat" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M2.678 11.894a1 1 0 0 1 .287.801 10.97 10.97 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8.06 8.06 0 0 0 8 14c3.996 0 7-2.807 7-6 0-3.192-3.004-6-7-6S1 4.808 1 8c0 1.468.617 2.83 1.678 3.894zm-.493 3.905a21.682 21.682 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a9.68 9.68 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.105z"/>
                        </svg>
                        <span class="left_label inl_bl ">@_e('im') <span id="new_msg">{{ $msg }}</span></span>
                    </a>
                    <a href="/friends/{{ $requests_link }}" onclick="Page.Go(this.href); return false;" class="left_row" id="requests_link">
                        <svg class="bi bi-people" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.995-.944v-.002.002zM7.022 13h7.956a.274.274 0 0 0 .014-.002l.008-.002c-.002-.264-.167-1.03-.76-1.72C13.688 10.629 12.718 10 11 10c-1.717 0-2.687.63-3.24 1.276-.593.69-.759 1.457-.76 1.72a1.05 1.05 0 0 0 .022.004zm7.973.056v-.002.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10c-1.668.02-2.615.64-3.16 1.276C1.163 11.97 1 12.739 1 13h3c0-1.045.323-2.086.92-3zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                        </svg>
                        <span class="left_label inl_bl ">@_e('friends') <span id="new_requests">{{ $demands }}</span></span>
                    </a>@if(\Sura\Libs\Settings::get('env') == 'debug')
                    <a href="/albums/{{ $my_id }}/" onclick="Page.Go(this.href); return false;" class="left_row" id="requests_link_new_photos">
                        <svg class="bi bi-image-alt" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.648 6.646a.5.5 0 0 1 .577-.093l4.777 3.947V15a1 1 0 0 1-1 1h-14a1 1 0 0 1-1-1v-2l3.646-4.354a.5.5 0 0 1 .63-.062l2.66 2.773 3.71-4.71z"/>
                            <path fill-rule="evenodd" d="M4.5 5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                        </svg>
                        <span class="left_label inl_bl ">@_e('menu_albums') <span id="new_photos">{{ $new_photos }}</span></span>
                    </a>
                    <a href="/fave/" onclick="Page.Go(this.href); return false;" class="left_row">
                        <svg class="bi bi-bookmarks" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M7 13l5 3V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12l5-3zm-4 1.234l4-2.4 4 2.4V4a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10.234z"/>
                            <path d="M14 14l-1-.6V2a1 1 0 0 0-1-1H4.268A2 2 0 0 1 6 0h6a2 2 0 0 1 2 2v12z"/>
                        </svg>
                        <span class="left_label inl_bl ">@_e('fave')</span>
                    </a>
                    <a href="/videos/{{ $user_id }}/" onclick="Page.Go(this.href); return false;" class="left_row">
                        <svg class="bi bi-camera-video" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M2.667 3.5c-.645 0-1.167.522-1.167 1.167v6.666c0 .645.522 1.167 1.167 1.167h6.666c.645 0 1.167-.522 1.167-1.167V4.667c0-.645-.522-1.167-1.167-1.167H2.667zM.5 4.667C.5 3.47 1.47 2.5 2.667 2.5h6.666c1.197 0 2.167.97 2.167 2.167v6.666c0 1.197-.97 2.167-2.167 2.167H2.667A2.167 2.167 0 0 1 .5 11.333V4.667z"/>
                            <path fill-rule="evenodd" d="M11.25 5.65l2.768-1.605a.318.318 0 0 1 .482.263v7.384c0 .228-.26.393-.482.264l-2.767-1.605-.502.865 2.767 1.605c.859.498 1.984-.095 1.984-1.129V4.308c0-1.033-1.125-1.626-1.984-1.128L10.75 4.785l.502.865z"/>
                        </svg>
                        <span class="left_label inl_bl ">@_e('video')</span>
                    </a>
                    <a href="/audio/" onclick="Page.Go(this.href); return false;" class="left_row">
                        <svg class="bi bi-music-note-list" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 13c0 1.105-1.12 2-2.5 2S7 14.105 7 13s1.12-2 2.5-2 2.5.895 2.5 2z"/>
                            <path fill-rule="evenodd" d="M12 3v10h-1V3h1z"/>
                            <path d="M11 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 16 2.22V4l-5 1V2.82z"/>
                            <path fill-rule="evenodd" d="M0 11.5a.5.5 0 0 1 .5-.5H4a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 .5 7H8a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 .5 3H8a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5z"/>
                        </svg>
                        <span class="left_label inl_bl ">@_e('music')</span>
                    </a>
                    <a href="{{ $new_groups_lnk }}" onclick="Page.Go(this.href); return false;" class="left_row"  id="new_groups_lnk">
                        <svg class="bi bi-flag" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M3.5 1a.5.5 0 0 1 .5.5v13a.5.5 0 0 1-1 0v-13a.5.5 0 0 1 .5-.5z"/>
                            <path fill-rule="evenodd" d="M3.762 2.558C4.735 1.909 5.348 1.5 6.5 1.5c.653 0 1.139.325 1.495.562l.032.022c.391.26.646.416.973.416.168 0 .356-.042.587-.126a8.89 8.89 0 0 0 .593-.25c.058-.027.117-.053.18-.08.57-.255 1.278-.544 2.14-.544a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-.5.5c-.638 0-1.18.21-1.734.457l-.159.07c-.22.1-.453.205-.678.287A2.719 2.719 0 0 1 9 9.5c-.653 0-1.139-.325-1.495-.562l-.032-.022c-.391-.26-.646-.416-.973-.416-.833 0-1.218.246-2.223.916a.5.5 0 1 1-.515-.858C4.735 7.909 5.348 7.5 6.5 7.5c.653 0 1.139.325 1.495.562l.032.022c.391.26.646.416.973.416.168 0 .356-.042.587-.126.187-.068.376-.153.593-.25.058-.027.117-.053.18-.08.456-.204 1-.43 1.64-.512V2.543c-.433.074-.83.234-1.234.414l-.159.07c-.22.1-.453.205-.678.287A2.719 2.719 0 0 1 9 3.5c-.653 0-1.139-.325-1.495-.562l-.032-.022c-.391-.26-.646-.416-.973-.416-.833 0-1.218.246-2.223.916a.5.5 0 0 1-.554-.832l.04-.026z"/>
                        </svg>
                        <span class="left_label inl_bl ">@_e('groups') <span id="new_groups">{{ $new_groups }}</span></span>
                    </a>@endif
                    <a href="/news/{{ $news_link }}" onclick="Page.Go(this.href); return false;" class="left_row"  id="news_link">
                        <svg class="bi bi-newspaper" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M0 2A1.5 1.5 0 0 1 1.5.5h11A1.5 1.5 0 0 1 14 2v12a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 0 14V2zm1.5-.5A.5.5 0 0 0 1 2v12a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V2a.5.5 0 0 0-.5-.5h-11z"/>
                            <path fill-rule="evenodd" d="M15.5 3a.5.5 0 0 1 .5.5V14a1.5 1.5 0 0 1-1.5 1.5h-3v-1h3a.5.5 0 0 0 .5-.5V3.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M2 3h10v2H2V3zm0 3h4v3H2V6zm0 4h4v1H2v-1zm0 2h4v1H2v-1zm5-6h2v1H7V6zm3 0h2v1h-2V6zM7 8h2v1H7V8zm3 0h2v1h-2V8zm-3 2h2v1H7v-1zm3 0h2v1h-2v-1zm-3 2h2v1H7v-1zm3 0h2v1h-2v-1z"/>
                        </svg>
                        <span class="left_label inl_bl ">@_e('news_feed') <span id="new_news">{{ $new_news }}</span></span>
                    </a>
{{--                    <a href="/settings/" onclick="Page.Go(this.href); return false" class="left_row">--}}
{{--                        <svg class="bi bi-gear" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">--}}
{{--                            <path fill-rule="evenodd" d="M8.837 1.626c-.246-.835-1.428-.835-1.674 0l-.094.319A1.873 1.873 0 0 1 4.377 3.06l-.292-.16c-.764-.415-1.6.42-1.184 1.185l.159.292a1.873 1.873 0 0 1-1.115 2.692l-.319.094c-.835.246-.835 1.428 0 1.674l.319.094a1.873 1.873 0 0 1 1.115 2.693l-.16.291c-.415.764.42 1.6 1.185 1.184l.292-.159a1.873 1.873 0 0 1 2.692 1.116l.094.318c.246.835 1.428.835 1.674 0l.094-.319a1.873 1.873 0 0 1 2.693-1.115l.291.16c.764.415 1.6-.42 1.184-1.185l-.159-.291a1.873 1.873 0 0 1 1.116-2.693l.318-.094c.835-.246.835-1.428 0-1.674l-.319-.094a1.873 1.873 0 0 1-1.115-2.692l.16-.292c.415-.764-.42-1.6-1.185-1.184l-.291.159A1.873 1.873 0 0 1 8.93 1.945l-.094-.319zm-2.633-.283c.527-1.79 3.065-1.79 3.592 0l.094.319a.873.873 0 0 0 1.255.52l.292-.16c1.64-.892 3.434.901 2.54 2.541l-.159.292a.873.873 0 0 0 .52 1.255l.319.094c1.79.527 1.79 3.065 0 3.592l-.319.094a.873.873 0 0 0-.52 1.255l.16.292c.893 1.64-.902 3.434-2.541 2.54l-.292-.159a.873.873 0 0 0-1.255.52l-.094.319c-.527 1.79-3.065 1.79-3.592 0l-.094-.319a.873.873 0 0 0-1.255-.52l-.292.16c-1.64.893-3.433-.902-2.54-2.541l.159-.292a.873.873 0 0 0-.52-1.255l-.319-.094c-1.79-.527-1.79-3.065 0-3.592l.319-.094a.873.873 0 0 0 .52-1.255l-.16-.292c-.892-1.64.902-3.433 2.541-2.54l.292.159a.873.873 0 0 0 1.255-.52l.094-.319z"/>--}}
{{--                            <path fill-rule="evenodd" d="M8 5.754a2.246 2.246 0 1 0 0 4.492 2.246 2.246 0 0 0 0-4.492zM4.754 8a3.246 3.246 0 1 1 6.492 0 3.246 3.246 0 0 1-6.492 0z"/>--}}
{{--                        </svg>--}}
{{--                        <span class="left_label inl_bl ">@_e('settings')</span>--}}
{{--                    </a>--}}
{{--                    <a href="/support/" onclick="Page.Go(this.href); return false" class="left_row">--}}
{{--                        <svg class="bi bi-question-circle" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">--}}
{{--                            <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>--}}
{{--                            <path d="M5.25 6.033h1.32c0-.781.458-1.384 1.36-1.384.685 0 1.313.343 1.313 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.007.463h1.307v-.355c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.326 0-2.786.647-2.754 2.533zm1.562 5.516c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/>--}}
{{--                        </svg>--}}
{{--                        <span class="left_label inl_bl ">Помощь <span id="new_support">{{ $support }}</span></span>--}}
{{--                    </a>--}}
{{--                    <a href="{{ $gifts_link }}" onclick="Page.Go(this.href); return false" class="left_row">--}}
{{--                        <svg class="bi bi-wallet" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">--}}
{{--                            <path fill-rule="evenodd" d="M2 4v8.5A1.5 1.5 0 0 0 3.5 14h10a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 1 1 0v8a1.5 1.5 0 0 1-1.5 1.5h-10A2.5 2.5 0 0 1 1 12.5V4h1z"/>--}}
{{--                            <path fill-rule="evenodd" d="M1 4a2 2 0 0 1 2-2h11.5a.5.5 0 0 1 0 1H3a1 1 0 0 0 0 2h11.5v1H3a2 2 0 0 1-2-2z"/>--}}
{{--                            <path fill-rule="evenodd" d="M13 5V3h1v2h-1z"/>--}}
{{--                        </svg>--}}
{{--                        <span class="left_label inl_bl ">@_e('balance') <span id="new_ubm">{{ $new_ubm }}</span></span>--}}
{{--                    </a>--}}
                </div>

            </div>

            <div class="ml-1 p-3">
                <div class="col-12">
                    <a href="#">@_e('privacy')</a>
                    <a href="#">@_e('terms')</a>
                    <a href="#">@_e('developers')</a>
                    <a href="#">@_e('help')</a>
                </div>
                <div class="col-12 footer">
                    @_e('Name') &copy; 2020 <a class="cursor_pointer" onClick="trsn.box()"
                       onMouseOver="myhtml.title('1', '@_e('select_lang')', 'langTitle', 1)"
                       id="langTitle1">@_e('lang')</a>
                </div>
            </div>
        </div>
        <style>.container>.footer{display: none;}.nav_menu{position: fixed;height: 95%;width: min-content;min-width: 280px;}</style>
    </div>
    <div class="col-12 col-sm-12 col-md-8 col-lg-4 ">
        @if(isset($stories) && $stories)
        <div class="col-12">
            <div class="col-12 mb-3">
                <div class="card" >
                    <div class="card-body">
                        @_e('ttt')Истории
                    </div>
                </div>

            </div>

            <div class=" mb-3 row ml-1" style="flex-wrap: nowrap;flex-direction: row;width: 100%;overflow-x: auto;overflow-y: hidden;">
                <div class="card m-1" style="width: 111px;height: 198px;background: blueviolet;border-radius: 6%;" onclick="Stories.CreatOpen()">
                    <div class="card-body">
                        <div class="p-3" style="margin-top: 120px;background-color: whitesmoke;width: 157.5%;margin-left: -20px;height: 68px;border-bottom-left-radius: 6%;border-bottom-right-radius: 6%;">
                            <div class="rounded-circle p-2" style="background-color: cornflowerblue;width: 42px;height: 42px;color: white;position: absolute;margin-top: -39px;margin-left: 26px;border: 4px solid whitesmoke;">
                                <svg width="2.2em" height="2.2em" viewBox="0 0 16 16" class="bi bi-plus" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M8 3.5a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5H4a.5.5 0 0 1 0-1h3.5V4a.5.5 0 0 1 .5-.5z"></path>
                                    <path fill-rule="evenodd" d="M7.5 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1H8.5V12a.5.5 0 0 1-1 0V8z"></path>
                                </svg>
                            </div>
                            @_e('story_create')</div>
                    </div>
                </div>
                {{--                <div class="card m-1" style="width: 111px; height: 198px;" onclick="Stories.Show('14')">
                    <div class="card-body" >
                        <div class="rounded-circle" style="width: 50px;height: 50px;background-color: whitesmoke;position: absolute;"></div>
                        <img src="{s_url}" alt="" style="width: 156%;height: 113%;margin-left: -20px;margin-top: -10px;">
                        <div class="d-none" style="margin-top: 100px;">@_e('story')</div>

                    </div>
                </div>--}}
                @if(!empty($stories))

                @foreach($stories as $item)
                    <div class="card m-1" style="width: 111px; height: 198px;" onclick="Stories.Show('{{$item['user_id']}}')">
                        <div class="card-body" >
                            <div class="rounded-circle" style="width: 50px;height: 50px;background-color: whitesmoke;position: absolute;"></div>
                            <img src="{{$item['url']}}" alt="" style="width: 156%;height: 113%;margin-left: -20px;margin-top: -10px;">
                            <div class="" style="margin-top: -20px;">{{$item['user']}} @_e('story')</div>

                        </div>
                    </div>
                @endforeach
                @endif
{{--                <div class="card m-1" style="width: 111px; height: 198px;">--}}
{{--                    <div class="card-body">--}}
{{--                        <div>--}}
{{--                            <div class="rounded-circle" style="width: 50px;height: 50px;background-color: whitesmoke;"></div>--}}
{{--                        </div>--}}
{{--                        <div class="d-none" style="margin-top: 100px;">@_e('story')</div>--}}

{{--                    </div>--}}
{{--                </div>--}}
            </div>
        </div>
        @endif
        @if(isset($new_post) && $new_post)
        <div class="card">
                    <div class="card-body">
                        <div class="bg_block" >
                            <div class="newmes mb-2" id="wall_tab">
                                <label for="wall_text"></label>
                                <textarea id="wall_text" onblur="if(this.value === '') this.value='Что у Вас нового?';this.style.color = '#909090';$('#wall_text').css('height', '33px');" onfocus="if(this.value==='Что у Вас нового?')this.value='';this.style.color = '#000000';$('#wall_text').css('height', '50px');" class="wall_inpst wall_fast_opened_texta"
                                          style="width: 100%;resize: none;overflow-y: hidden;border-bottom: 1px solid #E4E4E4;margin-top: -5px;color: #909090;font-weight: 500;"
                                          onkeyup="wall.CheckLinkText(this.value)" onblur="wall.CheckLinkText(this.value, 1)">@_e('wall_text_default')</textarea>
                                <div id="attach_files" class="margin_top_10 no_display"></div>
                                <div id="attach_block_lnk" class="no_display clear">
                                    <div class="attach_link_bg">
                                        <div class="text-center" id="loading_att_lnk"><img src="/images/loading_mini.gif" style="margin-bottom:-2px" alt=""></div>
                                        <img src="" id="attatch_link_img" class="no_display cursor_pointer text-left" onclick="wall.UrlNextImg()" alt="">
                                        <div id="attatch_link_title"></div>
                                        <div id="attatch_link_descr"></div>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="attach_toolip_but"></div>
                                    <div class="attach_link_block_ic fl_l"></div><div class="attach_link_block_te"><div class="fl_l">@_e('ttt')Ссылка: <a href="/" id="attatch_link_url" target="_blank"></a></div><img class="fl_l cursor_pointer" style="margin-top:2px;margin-left:5px" src="/images/close_a.png" onmouseover="myhtml.title('1', 'Не прикреплять', 'attach_lnk_')" id="attach_lnk_1" onclick="wall.RemoveAttachLnk()"></div>
                                    <input type="hidden" id="attach_lnk_stared">
                                    <input type="hidden" id="teck_link_attach">
                                    <span id="urlParseImgs" class="no_display"></span>
                                </div>
                                <div class="clear"></div>
                                <div id="attach_block_vote" class="no_display">
                                    <div class="attach_link_bg">
                                        <div class="texta">Тема опроса:</div>
                                        <label for="vote_title"></label>
                                        <input type="text" id="vote_title" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px" onkeyup="$('#attatch_vote_title').text(this.value)"><div class="mgclr"></div>
                                        <div class="texta">Варианты ответа:<br><small><span id="addNewAnswer"><a class="cursor_pointer" onclick="Votes.AddInp()">добавить</a></span> | <span id="addDelAnswer">удалить</span></small></div><input type="text" id="vote_answer_1" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px"><div class="mgclr"></div>
                                        <div class="texta">&nbsp;</div>
                                        <label for="vote_answer_2"></label>
                                        <input type="text" id="vote_answer_2" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px">
                                        <div class="mgclr"></div>
                                        <div id="addAnswerInp"></div>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="attach_toolip_but"></div>
                                    <div class="attach_link_block_ic fl_l"></div><div class="attach_link_block_te"><div class="fl_l">Опрос: <a id="attatch_vote_title" style="text-decoration:none;cursor:default"></a></div>
                                        <img class="fl_l cursor_pointer" style="margin-top:2px;margin-left:5px" src="/images/close_a.png" onmouseover="myhtml.title('1', 'Не прикреплять', 'attach_vote_')" id="attach_vote_1" onclick="Votes.RemoveForAttach()" alt="">
                                    </div>
                                    <input type="hidden" id="answerNum" value="2">
                                </div>
                                <div class="clear"></div>
                                <input id="vaLattach_files" type="hidden">
                                <div class="clear"></div>
                                <div class="fl_l mt-3" id="wall_attach_link" onclick="wall.attach_addphoto()">
                                    <svg class="bi bi-image-fill" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M.002 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-12a2 2 0 0 1-2-2V3zm1 9l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094L15.002 9.5V13a1 1 0 0 1-1 1h-12a1 1 0 0 1-1-1v-1zm5-6.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                                    </svg>
                                    Фотография</div>
                                <div class=" fl_l mt-3" id="wall_attach_link" onclick="wall.attach_addDoc()">
                                    <svg class="bi bi-file-text" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M4 1h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H4z"/>
                                        <path fill-rule="evenodd" d="M4.5 10.5A.5.5 0 0 1 5 10h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 8h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 4h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5z"/>
                                    </svg>
                                    Документ</div>
                                <div class=" fl_l mt-3" id="wall_attach_link" onclick="wall.attach_addvideo()">
                                    <svg class="bi bi-film" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M0 1a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V1zm4 0h8v6H4V1zm8 8H4v6h8V9zM1 1h2v2H1V1zm2 3H1v2h2V4zM1 7h2v2H1V7zm2 3H1v2h2v-2zm-2 3h2v2H1v-2zM15 1h-2v2h2V1zm-2 3h2v2h-2V4zm2 3h-2v2h2V7zm-2 3h2v2h-2v-2zm2 3h-2v2h2v-2z"/>
                                    </svg>
                                </div>
                                <div class=" fl_l mt-3" id="wall_attach_link" onclick="wall.attach_addaudio()">
                                    <svg class="bi bi-music-note-beamed" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 13c0 1.105-1.12 2-2.5 2S1 14.105 1 13c0-1.104 1.12-2 2.5-2s2.5.896 2.5 2zm9-2c0 1.105-1.12 2-2.5 2s-2.5-.895-2.5-2 1.12-2 2.5-2 2.5.895 2.5 2z"/>
                                        <path fill-rule="evenodd" d="M14 11V2h1v9h-1zM6 3v10H5V3h1z"/>
                                        <path d="M5 2.905a1 1 0 0 1 .9-.995l8-.8a1 1 0 0 1 1.1.995V3L5 4V2.905z"/>
                                    </svg>
                                </div>
                                <div class="fl_l mt-3" id="wall_attach_link" onclick="$('#attach_block_vote').slideDown('fast');wall.attach_menu('close', 'wall_attach', 'wall_attach_menu');$('#vote_title').focus();$('#vaLattach_files').val($('#vaLattach_files').val()+'vote|start||')">
                                    <svg class="bi bi-pie-chart" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path fill-rule="evenodd" d="M7.5 7.793V1h1v6.5H15v1H8.207l-4.853 4.854-.708-.708L7.5 7.793z"/>
                                    </svg>
                                </div>

                                <div class="fl_r mt-3"><button class="btn btn-secondary" onclick="wall.send_news(); return false" id="wall_send">Отправить</button></div>

                            </div>

                            <div class="clear"></div>

                        </div>


                    </div>
                </div>
        @endif
            <span id="news">
            @if($news)
                @include('wall.one_record', array('wall_records' => $news))
            @endif
{{--        @if($bottom)--}}
        </span>
{{--            <nav aria-label="Page navigation example">--}}
{{--            {{ $nav }}--}}
{{--            </nav>--}}
        <div onClick="news.load()" id="wall_l_href_news" class="cursor_pointer">
            <div class="text-center p-3 mt-3" id="loading_news" >@_e('news_next')</div>
        </div>
{{--        @endif--}}
    </div>

    <div class="col-2 d-none d-sm-none d-md-block  col-md-4 col-lg-2">
{{--        <style>.nav-item > .nav-link {font-size: 20px;color: #21578b;}</style>--}}
{{--        <div class="card">--}}
{{--            <div class="card-body">--}}
{{--                <div id="jquery_jplayer"></div>--}}
{{--                <input type="hidden" id="teck_id" value="" />--}}
{{--                <input type="hidden" id="teck_prefix" value="" />--}}
{{--                <input type="hidden" id="typePlay" value="standart" />--}}
{{--                <input type="hidden" id="type" value="{{ $type }}" />--}}

{{--                <ul class="nav flex-column">--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link active" href="/news/" onClick="Page.Go(this.href); return false;">Новости</a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link" href="/news/notifications/" onClick="Page.Go(this.href); return false;">Ответы</a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link" href="/news/photos/" onClick="Page.Go(this.href); return false;">Фотографии</a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link" href="/news/videos/" onClick="Page.Go(this.href); return false;">Видеозаписи</a>--}}
{{--                    </li>--}}
{{--                    <li class="nav-item">--}}
{{--                        <a class="nav-link" href="/news/updates/" onClick="Page.Go(this.href); return false;">Обновления</a>--}}
{{--                    </li>--}}
{{--                </ul>--}}
{{--            </div>--}}
{{--        </div>--}}
    </div>

</div>
@endsection