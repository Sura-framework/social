<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">{header}
    <noscript><meta http-equiv="refresh" content="0; URL=/badbrowser/"></noscript>
    <link media="screen" href="/style/bootstrap.min.css" type="text/css" rel="stylesheet" />
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
    <a class="nav-link icon-bell"   onClick="QNotifications.box();" ><span class="badge badge-secondary" id="new_notifications">{new-news}</span></a>
  </li>
</ul>   
<div id="audioMP" class="d-sm-none d-lg-block"></div>
<ul class="flex-row flex-sm-row navbar-nav ml-auto ml-sm-0 ml-lg-auto d-sm">
  <li class="nav-item active">
    <a class="nav-link icon-music-2" href="/audio/"  onClick="Page.Go(this.href); return false;"></a>
  </li>
  <li class="nav-item active">
    <a class="nav-link icon-down-dir" onclick="openTopMenu(this);" onmouseout="hideTopMenu()" onmouseover="removeTimer('hidetopmenu')" id="topmenubut"></a>
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
    <a href="/edit/" onclick="Page.Go(this.href); return false;"><div class="icon-edit">Редактировать страницу</div></a>
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
                <a href="/change_mobile/">мобильная версия</a>
                <a href="/search/?online=1" onClick="Page.Go(this.href); return false">люди</a>
                <a href="/search/?type=2" onClick="Page.Go(this.href); return false">видео</a>
                <a href="/search/?type=5" onClick="Page.Go(this.href); return false">музыка</a>
                <a href="/support/new/" onClick="Page.Go(this.href); return false">помощь</a>
                <a href="/reviews/" onClick="Page.Go(this.href); return false">отзывы</a>
                <a href="/blog/" onClick="Page.Go(this.href); return false">блог</a>
                <a href="/bugs/" onClick="Page.Go(this.href); return false">bugs</a>
                <a href="/fave/" onClick="Page.Go(this.href); return false">закладки</a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>