@extends('app.app')
@section('content')
<script type="text/javascript">
    $(document).ready(function(){
        @if($search_tab || $type == 1)
        $('#page').css('min-height', '444px');
        $(window).scroll(function(){
            if($(window).scrollTop() > 103)
                $('.search_sotrt_tab').css('position', 'fixed').css('margin-top', '-10px');
            else
                $('.search_sotrt_tab').css('position', 'absolute').css('margin-top', '160px');
        });
        @endif
{{--            myhtml.checked(['{{ $checked_online }}', '{{ $checked_user_photo }}']);--}}
        var query = $('#query_full').val();
        if(query == 'Начните вводить любое слово или имя')
            $('#query_full').css('color', '#c1cad0');
    });
</script>
<style>.site_menu_fix{overflow-y: auto;}</style>
<div class="d-block d-lg-flex justify-content-between">
        <div class="col-12 col-lg-3 d-lg-block mt-3">
            <div class="search_form_tab">
{{--                <div class="col-12 d-flex justify-content-around mt-3">--}}
{{--                    <input type="text" value="{{ $query_search }}" class="fave_input" id="query_full"--}}
{{--                           onBlur="if(this.value===''){this.value='Начните вводить любое слово или имя';this.style.color = '#c1cad0';}"--}}
{{--                           onFocus="if(this.value==='Начните вводить любое слово или имя'){this.value='';this.style.color = '#000'}"--}}
{{--                           onKeyPress="if(event.keyCode === 13)gSearch.go();"--}}
{{--                           style="margin:0;color:#000"--}}
{{--                           maxlength="65" />--}}
{{--                    <div class="button_div fl_r"><button onClick="gSearch.go(); return false">Поиск</button></div>--}}
{{--                </div>--}}

{{--                <div class="buttonsprofile albumsbuttonsprofile buttonsprofileSecond d-none" style="margin-top:10px;height:22px">--}}
{{--                    <div class="{activetab-1}"><a href="/search/?{{ $query_people }}" onClick="Page.Go(this.href); return false;"><div><b>Все</b></div></a></div>--}}
{{--                    <div class="{activetab-1}"><a href="/search/?{{ $query_people }}" onClick="Page.Go(this.href); return false;"><div><b>Люди</b></div></a></div>--}}
{{--                    <div class="{activetab-4}"><a href="/search/?{{ $query_groups }}" onClick="Page.Go(this.href); return false;"><div><b>Сообщества</b></div></a></div>--}}
{{--                    <div class="{activetab-5}"><a href="/search/?{{ $query_audios }}" onClick="Page.Go(this.href); return false;"><div><b>Аудиозаписи</b></div></a></div>--}}
{{--                    <div class="{activetab-2}"><a href="/search/?{{ $query_videos }}" onClick="Page.Go(this.href); return false;"><div><b>Видеозаписи</b></div></a></div>--}}
{{--                </div>--}}
                <div class="site_menu_fix">
                    <a href="/search/?query={{ $query_people }}" onclick="Page.Go(this.href); return false;" class="left_row">
      <span class="left_label inl_bl ml-5">
       <svg class="bi bi-files" width="24" height="24" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M3 2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H3z"/>
  <path d="M5 0h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2v-1a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1H3a2 2 0 0 1 2-2z"/>
</svg>@_e('all')</span>
                    </a>
                    <a href="/search/?query={{ $query_people }}" onclick="Page.Go(this.href); return false;" class="left_row">
      <span class="left_label inl_bl  ml-5">
                 <svg class="bi bi-files" width="24" height="24" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M3 2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H3z"/>
  <path d="M5 0h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2v-1a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1H3a2 2 0 0 1 2-2z"/>
</svg>@_e('people')</span>
                    </a>

                    @if($search_tab || $type == 1)
                    <div class="ml-5 mb-3">

                        <b>Основное</b>
                        <div class="search_clear"></div>

                        <div class="padstylej">
                            <label for="country"></label>
                            <select name="country" id="country" class="inpst search_sel" onChange="Profile.LoadCity(this.value); gSearch.go();">
                                <option value="0">Любая страна</option>{{ $country }}</select>
                            <img src="/images/loading_mini.gif" alt="" class="load_mini" id="load_mini" />
                        </div>
                        <div class="search_clear"></div>

                        <div class="padstylej">
                            <label for="select_city"></label>
                            <select name="city" id="select_city" class="inpst search_sel" onChange="gSearch.go();">
                                <option value="0">Любой город</option>{{ $city }}</select>
                        </div>
                        <div class="search_clear"></div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-check form-switch mb-3" id="Online">
                                    <input class="form-check-input" type="checkbox" id="SwitchCheckOnline" onChange="/*myhtml.checkbox(this.id);*/ gSearch.go()" {{ $checked_online }}>
                                    <input type="hidden" id="SwitchCheckOnline_r" value="0" />
                                    <label class="form-check-label" for="SwitchCheckOnline">сейчас на сайте</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch mb-3" id="UserPhoto">
                                    <input class="form-check-input" type="checkbox" id="SwitchCheckUserPhoto" onChange="/*checkbox(this.id);*/ gSearch.go()" {{ $checked_user_photo }}>
                                    <label class="form-check-label" for="SwitchCheckUserPhoto">с фотографией</label>
                                </div>
                            </div>
                        </div>

                        <div class="search_clear" ></div>

                        <b>Пол</b>
                        <div class="search_clear"></div>

                        <div class="padstylej">
                            <label for="sex"></label>
                            <select name="sex" id="sex" class="inpst search_sel" onChange="gSearch.go();"><option value="0">Все</option>{{ $sex }}</select></div>
                        <div class="search_clear"></div>

                        <b>День рождения</b>
                        <div class="search_clear"></div>

                        <div class="padstylej">
                            <label for="day"></label>
                            <select name="day" class="inpst search_sel" id="day" onChange="gSearch.go();"><option value="0">Любой день</option>{{ $day }}</select>
                            <div class="search_clear"></div>

                            <label for="month"></label>
                            <select name="month" class="inpst search_sel" id="month" onChange="gSearch.go();"><option value="0">Любой месяц</option>{{ $month }}</select>
                            <div class="search_clear"></div>

                            <label for="year"></label>
                            <select name="year" class="inpst search_sel" id="year" onChange="gSearch.go();"><option value="0">Любой год</option>{{ $year }}</select></div>
                        <div class="search_clear"></div>

                    </div>@endif

                    <a href="/search/?{{ $query_groups }}" onclick="Page.Go(this.href); return false;" class="left_row">
      <span class="left_label inl_bl  ml-5">
                 <svg class="bi bi-files" width="24" height="24" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M3 2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H3z"/>
  <path d="M5 0h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2v-1a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1H3a2 2 0 0 1 2-2z"/>
</svg>
          Сообщества</span>
                    </a>
                    <a href="/search/?{{ $query_audios }}" onclick="Page.Go(this.href); return false;" class="left_row">
      <span class="left_label inl_bl  ml-5">
          <svg class="bi bi-files" width="24" height="24" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M3 2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H3z"/>
  <path d="M5 0h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2v-1a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1H3a2 2 0 0 1 2-2z"/>
</svg>
          Аудиозаписи</span>
                    </a>
                    <a href="/search/?{{ $query_videos }}" onclick="Page.Go(this.href); return false;" class="left_row">
      <span class="left_label inl_bl  ml-5">
                 <svg class="bi bi-files" width="24" height="24" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M3 2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H3z"/>
  <path d="M5 0h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2v-1a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1H3a2 2 0 0 1 2-2z"/>
</svg>
          Видеозаписи</span>
                    </a>
                </div>

                <input type="hidden" value="{{ $type }}" id="se_type_full" />
            </div>

            <div class="clear"></div>
            @if($search)<div class="margin_top_10"></div><div class="search_result_title">Найдено {{ $count }}</div>@endif
            <div id="jquery_jplayer"></div>
            <input type="hidden" id="teck_id" value="0" />
            <input type="hidden" id="typePlay" value="standart" />
            <input type="hidden" id="teck_prefix" value="" />
        </div>
        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
            @if($search)
                @if($type == 1)
                    @include('search.people', $search)
                @elseif($type == 2)
                    @include('search.video', $search)
                @elseif($type == 4)
                    @include('search.groups', $search)
                @elseif($type == 5)
                    @include('search.audios', $search)
                @endif
                {{ $navigation }}
            @endif
        </div>

    <div class="col-2 d-none d-sm-none d-md-block  col-md-4 col-lg-2"></div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        setInterval('gSearch.resize()', 300);
    });
</script>
@endsection