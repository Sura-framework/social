<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">{header}
    <noscript><meta http-equiv="refresh" content="0; URL=/badbrowser/"></noscript>
    [group=0]<link media="screen" href="/style/bootstrap.min.css" type="text/css" rel="stylesheet" />[/group]
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I" crossorigin="anonymous">
    <link media="screen" href="/style/style.css" type="text/css" rel="stylesheet" />
    <link href="/style/fontello.css" type="text/css" rel="stylesheet"/>
    [logged]<script>var kj = {uid:'{my-id}'}</script>[/logged]{js}
</head>
<body onResize="onBodyResize()" class="no_display">
<div class="scroll_fix_bg no_display" onMouseDown="myhtml.scrollTop()"><div class="scroll_fix_page_top">Наверх</div></div>
<div id="doLoad"></div>
<header class="fixed-top">
  <div class="ml-3 mr-3">
    <nav class="flex-nowrap navbar navbar-expand-lg navbar-dark">
        <button class="m-menu">
            <span class="line"></span>
            <span class="line"></span>
            <span class="line"></span>
        </button>
        <a href="/"  onClick="Page.Go(this.href); return false;" class="navbar-brand logo">Sura</a>
        <form class="d-none d-sm-none d-lg-flex form-inline my-2 my-lg-0" id="/* search_tab */">
          <input class="form-control serch_inpt" type="search" placeholder="Search" aria-label="Search" style="width: 50%;" id="query" maxlength="65" onblur="if(this.value=='') this.value='Поиск';this.style.color = '#c1cad0';" onfocus="if(this.value=='Поиск')this.value='';this.style.color = '#000'" onkeypress="if(event.keyCode == 13) gSearch.go();" onkeyup="FSE.Txt()">
          <div id="search_types">
             <input type="hidden" value="1" id="se_type">
             <div class="search_type" id="search_selected_text" onclick="gSearch.open_types('#sel_types'); return false">по людям</div>
             <div class="search_alltype_sel no_display" id="sel_types" style="display: none;">
              <div id="1" onclick="gSearch.select_type(this.id, 'по людям'); FSE.GoSe($('#query').val()); return false" class="search_type_selected">по людям</div>
              <div id="2" onclick="gSearch.select_type(this.id, 'по видеозаписям'); FSE.GoSe($('#query').val()); return false">по видеозаписям</div>
              <div id="3" onclick="gSearch.select_type(this.id, 'по заметкам');  FSE.GoSe($('#query').val()); return false">по заметкам</div>
              <div id="4" onclick="gSearch.select_type(this.id, 'по сообществам'); FSE.GoSe($('#query').val()); return false">по сообществам</div>
              <div id="5" onclick="gSearch.select_type(this.id, 'по аудиозаписям');  FSE.GoSe($('#query').val()); return false">по аудиозаписям</div>
             </div>
            </div>
          <button class="btn btn-outline-light my-2 my-sm-0"  onclick="gSearch.go(); return false" id="se_but">Найти</button>
          <div class="fast_search_bg " style="display: none;">
            <a href="/" style="padding: 12px; background: rgb(238, 243, 245);" onclick="gSearch.go(); return false" onmouseover="FSE.ClrHovered(this.id)" id="all_fast_res_clr1">
              <text>Искать</text><b id="fast_search_txt"></b><div class="fl_r fast_search_ic"></div>
            </a>
             <span id="reFastSearch"></span>
          </div>
        </form>
[logged]
<ul class="navbar-nav ml-3">
  <li class="nav-item active">
    <a class="nav-link"   onClick="QNotifications.box();" >
        <svg class="bi bi-bell" width="25" height="25" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2z"/>
            <path fill-rule="evenodd" d="M8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
        </svg>
        <span class="badge badge-secondary" id="new_notifications">{new-news}</span></a>
  </li>
</ul>   
<div id="audioMP" class="d-sm-none d-lg-block"></div>
<ul class="flex-row flex-sm-row navbar-nav ml-auto ml-sm-0 ml-lg-auto d-sm">
  <li class="nav-item active">
    <a class="nav-link" href="/audio/"  onClick="Page.Go(this.href); return false;">
        <svg class="bi bi-music-note-beamed" width="25" height="25" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 13c0 1.105-1.12 2-2.5 2S1 14.105 1 13c0-1.104 1.12-2 2.5-2s2.5.896 2.5 2zm9-2c0 1.105-1.12 2-2.5 2s-2.5-.895-2.5-2 1.12-2 2.5-2 2.5.895 2.5 2z"/>
            <path fill-rule="evenodd" d="M14 11V2h1v9h-1zM6 3v10H5V3h1z"/>
            <path d="M5 2.905a1 1 0 0 1 .9-.995l8-.8a1 1 0 0 1 1.1.995V3L5 4V2.905z"/>
        </svg>
    </a>
  </li>
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
    <a href="{my-page-link}" class="d-flex m-2" onclick="Page.Go(this.href); return false;">
        <div class="row">
            <div class="col-3"><img src="{my-ava}" class="rounded-circle" alt=""></div>
            <div class="col"><h2>{my-name}</h2><p class="text-muted">Посмотреть свой профиль</p></div>
        </div>
    </a>
    <div class="explode"></div>
    [group=0]<a href="/edit/" onclick="Page.Go(this.href); return false;"><div class="icon-edit">Редактировать страницу</div></a>[/group]
    <a href="/settings/" onclick="Page.Go(this.href); return false;"><div class="icon-cog-4">Настройки</div></a>
    <div class="explode"></div>
    <a href="/balance/" onclick="Page.Go(this.href); return false;" id="ubm_link"><div class="icon-money">Баланс <span id="new_ubm" class="drop-nemu_new"></span></div></a>
    <a href="/ads/" onclick="Page.Go(this.href); return false;"><div class="icon-megaphone-3">Реклама</div></a>
    <a href="/support/" onclick="Page.Go(this.href); return false;"><div class="icon-help">Помощь <span id="new_support" class="drop-nemu_new"></span></div></a>
    <a href="/logout/"><div class="icon-off-1">Выход</div></a>
</div>
</div>

[/logged][not-logged]
<div class="hederspace"></div>
<ul class="navbar-nav mr-auto">
  <li class="nav-item active">
    <a class="nav-link icon-users-3" href="/search/?query=&type=1"  onClick="Page.Go(this.href); return false;"></a>
  </li>
</ul>
<style>.hederspace{width: 300px;}</style>
[/not-logged]
    </nav>
  </div>
</header>
<link rel="stylesheet" href="/style/plyr.css" />
   <script src="/js/plyr.js"></script>
<div class="clear"></div>
<div style="margin-top:41px;"></div>
[logged]
<div class="menu-sidebar">

    <div class="site_menu_fix d-flex flex-column" style="left: 490px;z-index: 10000">
        <a href="{my-page-link}" onclick="Page.Go(this.href); return false;" class="left_row">
            <svg class="bi bi-chevron-right" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M13 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1h10zm-9.995-.944v-.002.002zM3.022 13h9.956a.274.274 0 0 0 .014-.002l.008-.002c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664a1.05 1.05 0 0 0 .022.004zm9.974.056v-.002.002zM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
            </svg>

            <span class="left_label inl_bl ">Моя Страница</span>
        </a>
        <a href="/messages/" onclick="Page.Go(this.href); return false;" class="left_row">
            <svg class="bi bi-chat" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M2.678 11.894a1 1 0 0 1 .287.801 10.97 10.97 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8.06 8.06 0 0 0 8 14c3.996 0 7-2.807 7-6 0-3.192-3.004-6-7-6S1 4.808 1 8c0 1.468.617 2.83 1.678 3.894zm-.493 3.905a21.682 21.682 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a9.68 9.68 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.105z"/>
            </svg>
            <span class="left_label inl_bl ">Сообщения <span id="new_msg">{msg}</span></span>
        </a>
        <a href="/friends/{requests-link}" onclick="Page.Go(this.href); return false;" class="left_row" id="requests_link">
            <svg class="bi bi-people" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.995-.944v-.002.002zM7.022 13h7.956a.274.274 0 0 0 .014-.002l.008-.002c-.002-.264-.167-1.03-.76-1.72C13.688 10.629 12.718 10 11 10c-1.717 0-2.687.63-3.24 1.276-.593.69-.759 1.457-.76 1.72a1.05 1.05 0 0 0 .022.004zm7.973.056v-.002.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10c-1.668.02-2.615.64-3.16 1.276C1.163 11.97 1 12.739 1 13h3c0-1.045.323-2.086.92-3zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
            </svg>
            <span class="left_label inl_bl "></span>Друзья <span id="new_requests">{demands}</span></span>
        </a>
        <a href="/albums/{my-id}/" onclick="Page.Go(this.href); return false;" class="left_row" id="requests_link_new_photos">
            <svg class="bi bi-image-alt" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M10.648 6.646a.5.5 0 0 1 .577-.093l4.777 3.947V15a1 1 0 0 1-1 1h-14a1 1 0 0 1-1-1v-2l3.646-4.354a.5.5 0 0 1 .63-.062l2.66 2.773 3.71-4.71z"/>
                <path fill-rule="evenodd" d="M4.5 5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
            </svg>
            <span class="left_label inl_bl ">Фото <span id="new_photos">{new_photos}</span></span>
        </a>
        <a href="/fave/" onclick="Page.Go(this.href); return false;" class="left_row">
            <svg class="bi bi-bookmarks" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M7 13l5 3V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12l5-3zm-4 1.234l4-2.4 4 2.4V4a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10.234z"/>
                <path d="M14 14l-1-.6V2a1 1 0 0 0-1-1H4.268A2 2 0 0 1 6 0h6a2 2 0 0 1 2 2v12z"/>
            </svg>
            <span class="left_label inl_bl ">Закладки</span>
        </a>
        <a href="/videos/" onclick="Page.Go(this.href); return false;" class="left_row">
            <svg class="bi bi-camera-video" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M2.667 3.5c-.645 0-1.167.522-1.167 1.167v6.666c0 .645.522 1.167 1.167 1.167h6.666c.645 0 1.167-.522 1.167-1.167V4.667c0-.645-.522-1.167-1.167-1.167H2.667zM.5 4.667C.5 3.47 1.47 2.5 2.667 2.5h6.666c1.197 0 2.167.97 2.167 2.167v6.666c0 1.197-.97 2.167-2.167 2.167H2.667A2.167 2.167 0 0 1 .5 11.333V4.667z"/>
                <path fill-rule="evenodd" d="M11.25 5.65l2.768-1.605a.318.318 0 0 1 .482.263v7.384c0 .228-.26.393-.482.264l-2.767-1.605-.502.865 2.767 1.605c.859.498 1.984-.095 1.984-1.129V4.308c0-1.033-1.125-1.626-1.984-1.128L10.75 4.785l.502.865z"/>
            </svg>
            <span class="left_label inl_bl ">Видео</span>
        </a>
        <a href="/audio/" onclick="Page.Go(this.href); return false;" class="left_row">
            <svg class="bi bi-music-note-list" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 13c0 1.105-1.12 2-2.5 2S7 14.105 7 13s1.12-2 2.5-2 2.5.895 2.5 2z"/>
                <path fill-rule="evenodd" d="M12 3v10h-1V3h1z"/>
                <path d="M11 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 16 2.22V4l-5 1V2.82z"/>
                <path fill-rule="evenodd" d="M0 11.5a.5.5 0 0 1 .5-.5H4a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 .5 7H8a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 .5 3H8a.5.5 0 0 1 0 1H.5a.5.5 0 0 1-.5-.5z"/>
            </svg>
            <span class="left_label inl_bl ">Музыка</span>
        </a>
        <a href="{groups-link}/" onclick="Page.Go(this.href); return false;" class="left_row"  id="new_groups_lnk">
            <svg class="bi bi-flag" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M3.5 1a.5.5 0 0 1 .5.5v13a.5.5 0 0 1-1 0v-13a.5.5 0 0 1 .5-.5z"/>
                <path fill-rule="evenodd" d="M3.762 2.558C4.735 1.909 5.348 1.5 6.5 1.5c.653 0 1.139.325 1.495.562l.032.022c.391.26.646.416.973.416.168 0 .356-.042.587-.126a8.89 8.89 0 0 0 .593-.25c.058-.027.117-.053.18-.08.57-.255 1.278-.544 2.14-.544a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-.5.5c-.638 0-1.18.21-1.734.457l-.159.07c-.22.1-.453.205-.678.287A2.719 2.719 0 0 1 9 9.5c-.653 0-1.139-.325-1.495-.562l-.032-.022c-.391-.26-.646-.416-.973-.416-.833 0-1.218.246-2.223.916a.5.5 0 1 1-.515-.858C4.735 7.909 5.348 7.5 6.5 7.5c.653 0 1.139.325 1.495.562l.032.022c.391.26.646.416.973.416.168 0 .356-.042.587-.126.187-.068.376-.153.593-.25.058-.027.117-.053.18-.08.456-.204 1-.43 1.64-.512V2.543c-.433.074-.83.234-1.234.414l-.159.07c-.22.1-.453.205-.678.287A2.719 2.719 0 0 1 9 3.5c-.653 0-1.139-.325-1.495-.562l-.032-.022c-.391-.26-.646-.416-.973-.416-.833 0-1.218.246-2.223.916a.5.5 0 0 1-.554-.832l.04-.026z"/>
            </svg>
            <span class="left_label inl_bl ">Группы <span id="new_groups">{new_groups}</span></span>
        </a>
        <a href="/news/{news-link}" onclick="Page.Go(this.href); return false;" class="left_row"  id="news_link">
            <svg class="bi bi-newspaper" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M0 2A1.5 1.5 0 0 1 1.5.5h11A1.5 1.5 0 0 1 14 2v12a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 0 14V2zm1.5-.5A.5.5 0 0 0 1 2v12a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5V2a.5.5 0 0 0-.5-.5h-11z"/>
                <path fill-rule="evenodd" d="M15.5 3a.5.5 0 0 1 .5.5V14a1.5 1.5 0 0 1-1.5 1.5h-3v-1h3a.5.5 0 0 0 .5-.5V3.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M2 3h10v2H2V3zm0 3h4v3H2V6zm0 4h4v1H2v-1zm0 2h4v1H2v-1zm5-6h2v1H7V6zm3 0h2v1h-2V6zM7 8h2v1H7V8zm3 0h2v1h-2V8zm-3 2h2v1H7v-1zm3 0h2v1h-2v-1zm-3 2h2v1H7v-1zm3 0h2v1h-2v-1z"/>
            </svg>
            <span class="left_label inl_bl ">Лента <span id="new_news">{new-news}</span></span>
        </a>
        <a href="/settings/" onclick="Page.Go(this.href); return false" class="left_row">
            <svg class="bi bi-gear" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M8.837 1.626c-.246-.835-1.428-.835-1.674 0l-.094.319A1.873 1.873 0 0 1 4.377 3.06l-.292-.16c-.764-.415-1.6.42-1.184 1.185l.159.292a1.873 1.873 0 0 1-1.115 2.692l-.319.094c-.835.246-.835 1.428 0 1.674l.319.094a1.873 1.873 0 0 1 1.115 2.693l-.16.291c-.415.764.42 1.6 1.185 1.184l.292-.159a1.873 1.873 0 0 1 2.692 1.116l.094.318c.246.835 1.428.835 1.674 0l.094-.319a1.873 1.873 0 0 1 2.693-1.115l.291.16c.764.415 1.6-.42 1.184-1.185l-.159-.291a1.873 1.873 0 0 1 1.116-2.693l.318-.094c.835-.246.835-1.428 0-1.674l-.319-.094a1.873 1.873 0 0 1-1.115-2.692l.16-.292c.415-.764-.42-1.6-1.185-1.184l-.291.159A1.873 1.873 0 0 1 8.93 1.945l-.094-.319zm-2.633-.283c.527-1.79 3.065-1.79 3.592 0l.094.319a.873.873 0 0 0 1.255.52l.292-.16c1.64-.892 3.434.901 2.54 2.541l-.159.292a.873.873 0 0 0 .52 1.255l.319.094c1.79.527 1.79 3.065 0 3.592l-.319.094a.873.873 0 0 0-.52 1.255l.16.292c.893 1.64-.902 3.434-2.541 2.54l-.292-.159a.873.873 0 0 0-1.255.52l-.094.319c-.527 1.79-3.065 1.79-3.592 0l-.094-.319a.873.873 0 0 0-1.255-.52l-.292.16c-1.64.893-3.433-.902-2.54-2.541l.159-.292a.873.873 0 0 0-.52-1.255l-.319-.094c-1.79-.527-1.79-3.065 0-3.592l.319-.094a.873.873 0 0 0 .52-1.255l-.16-.292c-.892-1.64.902-3.433 2.541-2.54l.292.159a.873.873 0 0 0 1.255-.52l.094-.319z"/>
                <path fill-rule="evenodd" d="M8 5.754a2.246 2.246 0 1 0 0 4.492 2.246 2.246 0 0 0 0-4.492zM4.754 8a3.246 3.246 0 1 1 6.492 0 3.246 3.246 0 0 1-6.492 0z"/>
            </svg>
            <span class="left_label inl_bl ">Настройки</span>
        </a>
        <a href="/support/" onclick="Page.Go(this.href); return false" class="left_row">
            <svg class="bi bi-question-circle" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M5.25 6.033h1.32c0-.781.458-1.384 1.36-1.384.685 0 1.313.343 1.313 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.007.463h1.307v-.355c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.326 0-2.786.647-2.754 2.533zm1.562 5.516c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/>
            </svg>
            <span class="left_label inl_bl ">Помощь <span id="new_support">{new-support}</span></span>
        </a>
        <a href="{ubm-link}" onclick="Page.Go(this.href); return false" class="left_row">
            <svg class="bi bi-wallet" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M2 4v8.5A1.5 1.5 0 0 0 3.5 14h10a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 1 1 0v8a1.5 1.5 0 0 1-1.5 1.5h-10A2.5 2.5 0 0 1 1 12.5V4h1z"/>
                <path fill-rule="evenodd" d="M1 4a2 2 0 0 1 2-2h11.5a.5.5 0 0 1 0 1H3a1 1 0 0 0 0 2h11.5v1H3a2 2 0 0 1-2-2z"/>
                <path fill-rule="evenodd" d="M13 5V3h1v2h-1z"/>
            </svg>
            <span class="left_label inl_bl ">Баланс <span id="new_ubm">{new-ubm}</span></span>
        </a>
    </div>


</div>
[/logged]
<div class="">
  <div class="speedbar no_display" id="">{speedbar}</div>
   <!-- <div id="audioPlayer"></div> -->
    <div id="audioPlayer"></div>
    <div id="qnotifications_box">
        <div id="qnotifications_news">
        <div class="qnotifications_head"><span>ВСЕ СОБЫТИЯ</span><span class="settings_icon" onclick="QNotifications.settings();"></span></div>
        <div id="qnotifications_content"></div>
        </div>
        <div id="qnotifications_settings" style="display:none;">
        <div class="qnotifications_head" style="color: #008bc8;cursor: pointer;" onclick="QNotifications.settings();"><span><img style="margin: -4px 6px 0 0;vertical-align: middle;" src="/images/left-arrow.png"></span><span>Вернуться к моим событиям</span></div>
        <div id="qnotifications_settings_content"></div>
        </div>
        <div id="qnotifications_notification" style="display:none;">
        <div class="qnotifications_head" style="color: #008bc8;cursor: pointer;" onclick="QNotifications.close_notify();"><span><img style="margin: -4px 6px 0 0;vertical-align: middle;" src="/images/left-arrow.png"></span><span>НАЗАД</span></div>
        <div id="qnotifications_notification_content"></div>
        </div>
    </div>
    <div id="audioPad"></div>
    <div id="tt_wind"></div>
    <div id="page">{info}{content}</div>
    <div class="clear"></div>
</div>
[logged]<script type="text/javascript" src="/js/push.js"></script>
<div class="no_display"><audio id="beep-three" controls preload="auto"><source src="/images/soundact.ogg"></source></audio></div>
<div id="audioPlayList"></div>
<audio id="audioplayer" preload="auto"></audio>
<div id="updates"></div>[/logged]
<div class="clear"></div>
<footer>
    <div class="container">
        <div class="footer">
            Sura &copy; 2020 <a class="cursor_pointer" onClick="trsn.box()"
            onMouseOver="myhtml.title('1', 'Выбор используемого языка на сайте', 'langTitle', 1)"
            id="langTitle1">{lang}</a>
            <div class="fl_r">
                <a href="#">Конфиденциальность</a>
                <a href="#">условия использования</a>
                <a href="#">Разработчикам</a>
                <a href="#">Справка</a>
                <a href="/search/?online=1" onClick="Page.Go(this.href); return false">люди</a>
                <a href="/search/?type=2" onClick="Page.Go(this.href); return false">видео</a>
                <a href="/search/?type=5" onClick="Page.Go(this.href); return false">музыка</a>
                <a href="/support/new/" onClick="Page.Go(this.href); return false">помощь</a>
                <a href="/fave/" onClick="Page.Go(this.href); return false">закладки</a>
            </div>
        </div>
    </div>
</footer>
<script src="/js/menu.js"></script>
</body>
</html>