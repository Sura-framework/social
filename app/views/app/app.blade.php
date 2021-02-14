@if(!\Sura\Libs\Request::ajax())
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    {{ \App\Libs\Support::header() }}
    <noscript><meta http-equiv="refresh" content="0; URL=/badbrowser/"></noscript>
    <link media="screen" href="@asset('style/bootstrap.min.css?=3')" type="text/css" rel="stylesheet" />
{{--    <link media="screen" href="@asset('style/style.css?=3')" type="text/css" rel="stylesheet" />--}}
    <link media="screen" href="@asset('style/style.css?=3')" type="text/css" rel="stylesheet" />
    {{ \App\Libs\Support::head_script_uId() }}{{ \App\Libs\Support::head_js() }}
{{--    <script src="@asset('js/menu.js?=3')"></script>--}}
</head>
<body {{--onResize="onBodyResize()"--}} >
{{--<div class="scroll_fix_bg no_display" onMouseDown="myhtml.scrollTop()"><div class="scroll_fix_page_top">Наверх</div></div>--}}
<div id="theme">{{ \App\Libs\Support::theme() }}</div>
<div id="doLoad"></div>
<header class="fixed-top">
    <div class="ml-3 mr-3">
        <nav class="flex-nowrap navbar navbar-expand-lg navbar-light bg-light">
            <button class="m-menu mr-5" onclick="open_menu()">
                <span class="line"></span>
                <span class="line"></span>
                <span class="line"></span>
            </button>
            <a href="{{ $url }}/" onClick="Page.Go(this.href); return false;" class="navbar-brand logo">Sura</a>
            <form class="d-none d-sm-none d-lg-flex form-inline my-2 my-lg-0 mr-3" id="search_tab">
                <input class="form-control serch_inpt" type="search" placeholder="Search" aria-label="Search"
                       id="query" maxlength="65" onblur="this.style.color = '#c1cad0';"
{{--                       onfocus="if(this.value=='Поиск')this.value='';this.style.color = '#000'"--}}
                       onkeypress="if(event.keyCode == 13) gSearch.go();"
                       onkeyup="FSE.Txt()"
                       autocomplete="off"
                value="{{ App\Libs\Support::search() }}"
                >
                <div id="search_types">
                    <input type="hidden" value="3" id="se_type">
                    <div class="search_type d-none" id="search_selected_text" onclick="gSearch.open_types('#sel_types'); return false">@_e('by_people')</div>
                    <div class="search_alltype_sel no_display" id="sel_types" style="display: none;">
                        <div id="1" onclick="gSearch.select_type(this.id, 'по людям'); FSE.GoSe($('#query').val()); return false" class="search_type_selected">@_e('by_people')</div>
                        <div id="2" onclick="gSearch.select_type(this.id, 'по видеозаписям'); FSE.GoSe($('#query').val()); return false">@_e('by_videos')</div>
{{--                        <div id="3" onclick="gSearch.select_type(this.id, 'по заметкам');  FSE.GoSe($('#query').val()); return false">по заметкам</div>--}}
                        <div id="4" onclick="gSearch.select_type(this.id, 'по сообществам'); FSE.GoSe($('#query').val()); return false">@_e('by_groups')</div>
                        <div id="5" onclick="gSearch.select_type(this.id, 'по аудиозаписям');  FSE.GoSe($('#query').val()); return false">@_e('by_audios')</div>
                    </div>
                </div>
                <button class="btn btn-outline-primary my-2 my-sm-0" style="min-width: fit-content;" onclick="gSearch.go(); return false" id="se_but">@_e('find')</button>
                <div class="fast_search_bg " style="display: none;">
                    <a href="/" style="padding: 12px; background: rgb(238, 243, 245);" onclick="gSearch.go(); return false" onmouseover="FSE.ClrHovered(this.id)" id="all_fast_res_clr1">
                        <text>@_e('search')</text><b id="fast_search_txt"></b><div class="fl_r fast_search_ic"></div>
                    </a>
                    <span id="reFastSearch"></span>
                </div>
            </form>
            @include('app.head_menu')
        </nav>
    </div>
</header>
<link rel="stylesheet" href="@asset('style/plyr.css')" />
<script src="@asset('js/plyr.js')"></script>
<div style="margin-top:41px;"></div>
@include('app.menu')
<div class="">
    <div id="audioPlayer"></div>
    <div id="qnotifications_box" class="d-none justify-content-center">
        <div id="qnotifications_news" class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-6 mt-5">
            <div class="qnotifications_head"><span>@_e('all_notify')</span><span class="settings_icon" onclick="QNotifications.settings();"></span></div>
            <div id="qnotifications_content"></div>
        </div>
        <div id="qnotifications_settings" class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-6 mt-5" style="display:none;">
            <div class="qnotifications_head" style="color: #008bc8;cursor: pointer;" onclick="QNotifications.settings();"><span><img style="margin: -4px 6px 0 0;vertical-align: middle;" src="/images/left-arrow.png" alt="left-arrow"></span><span>@_e('back_to_my_notify')</span></div>
            <div id="qnotifications_settings_content"></div>
        </div>
        <div id="qnotifications_notification" class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-6 mt-5" style="display:none;">
            <div class="qnotifications_head" style="color: #008bc8;cursor: pointer;" onclick="QNotifications.close_notify();"><span><img style="margin: -4px 6px 0 0;vertical-align: middle;" src="/images/left-arrow.png" alt="left-arrow"></span><span>@_e('back')</span></div>
            <div id="qnotifications_notification_content"></div>
        </div>
    </div>
    <div id="audioPad"></div>
    <div id="tt_wind"></div>
    <div id="page">
        @endif
        @yield('content')
        @if(!\Sura\Libs\Request::ajax())
    </div>
    <div class="clear"></div>
</div>
@include('app.footer_logged')
<div class="clear"></div>
<footer>
    <div class="container">
        <div class="footer">
            @_e('Name') &copy; 2021 <a class="cursor_pointer" onClick="trsn.box()"
                onMouseOver="myhtml.title('1', '@_e('select_lang')', 'langTitle', 1)"
                id="langTitle1">@_e('lang')</a>
            <div class="fl_r">
                <a href="#">@_e('privacy')</a>
                <a href="#">@_e('terms')</a>
                <a href="#">@_e('developers')</a>
                <a href="#">@_e('help')</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>@endif