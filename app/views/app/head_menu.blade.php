@auth('user')
<ul class="navbar-nav ml-3">
    <li class="nav-item active">
        <a class="nav-link"   onClick="QNotifications.box();" >
            <svg class="bi bi-bell" width="25" height="25" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2z"/>
                <path fill-rule="evenodd" d="M8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
            </svg>
            <span class="badge badge-secondary" id="new_notifications">{{--  --}} </span></a>
    </li>
</ul>
<div id="audioMP" class="d-sm-none d-lg-block"></div>
<ul class="flex-row flex-sm-row navbar-nav ml-auto ml-sm-0 ml-lg-auto d-sm">
{{--    <li class="nav-item active">--}}
{{--        <a class="nav-link" href="/audio/"  onClick="Page.Go(this.href); return false;">--}}
{{--            <svg class="bi bi-music-note-beamed" width="25" height="25" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">--}}
{{--                <path d="M6 13c0 1.105-1.12 2-2.5 2S1 14.105 1 13c0-1.104 1.12-2 2.5-2s2.5.896 2.5 2zm9-2c0 1.105-1.12 2-2.5 2s-2.5-.895-2.5-2 1.12-2 2.5-2 2.5.895 2.5 2z"/>--}}
{{--                <path fill-rule="evenodd" d="M14 11V2h1v9h-1zM6 3v10H5V3h1z"/>--}}
{{--                <path d="M5 2.905a1 1 0 0 1 .9-.995l8-.8a1 1 0 0 1 1.1.995V3L5 4V2.905z"/>--}}
{{--            </svg>--}}
{{--        </a>--}}
{{--    </li>--}}
    <li class="nav-item active">
        <a class="nav-link" onclick="openTopMenu(this);" onmouseout="hideTopMenu()" onmouseover="removeTimer('hidetopmenu')" id="topmenubut">
            <svg class="bi bi-chevron-down" width="25" height="25" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
            </svg>
        </a>
    </li>
</ul>
<div class="kj_head_menu d-none mr-2" onmouseover="removeTimer('hidetopmenu')" onmouseout="hideTopMenu()">
    <div class="kj_head_menu_arrow">
        <a href="{{ '/u'.$user['user_info']['user_id'] }}" class="d-flex m-2" onclick="Page.Go(this.href); return false;">
            <div class="row">
                <div class="col-3"><img src="{{ $user['user_info']['ava'] }}" class="rounded-circle" alt=""></div>
                <div class="col"><h2>{{ $user['user_info']['user_search_pref'] }} </h2><p class="text-muted">Посмотреть свой профиль</p></div>
            </div>
        </a>
        <div class="explode"></div>
        <a href="/settings/" onclick="Page.Go(this.href); return false;"><div class="icon-cog-4">Настройки</div></a>
        <div class="explode"></div>
        <a href="/balance/" onclick="Page.Go(this.href); return false;" id="ubm_link"><div class="icon-money">Баланс <span id="new_ubm" class="drop-nemu_new"></span></div></a>
{{--        <a href="/ads/" onclick="Page.Go(this.href); return false;"><div class="icon-megaphone-3">Реклама</div></a>--}}
        <a href="/support/" onclick="Page.Go(this.href); return false;"><div class="icon-help">Помощь <span id="new_support" class="drop-nemu_new"></span></div></a>
        <a href="/logout/"><div class="icon-off-1">Выход</div></a>
    </div>
</div>
@elseauth
<div class="hederspace"></div>
<ul class="navbar-nav mr-auto">
    <li class="nav-item active">

        <a class="nav-link" href="/search/?query=&type=1"  onClick="Page.Go(this.href); return false;">
            <svg width="1.2em" height="1.2em" viewBox="0 0 16 16" class="bi bi-people-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
            </svg>

        </a>
    </li>
</ul>
<style>.hederspace{width: 300px;}</style>
@endauth