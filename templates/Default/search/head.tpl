<script type="text/javascript">
$(document).ready(function(){
	[search-tab]$('#page').css('min-height', '444px');
	$(window).scroll(function(){
		if($(window).scrollTop() > 103)
			$('.search_sotrt_tab').css('position', 'fixed').css('margin-top', '-10px');
		else
			$('.search_sotrt_tab').css('position', 'absolute').css('margin-top', '160px');
	});[/search-tab]
	myhtml.checked(['{checked-online}', '{checked-user-photo}']);	
	var query = $('#query_full').val();
	if(query == 'Начните вводить любое слово или имя')
		$('#query_full').css('color', '#c1cad0');
});
</script>
<div class="d-flex justify-content-between">

 <div class="col-2 d-lg-block ">

 <div class="search_form_tab position-absolute">
  <input type="text" value="{query}" class="fave_input" id="query_full"
         onBlur="if(this.value==''){this.value='Начните вводить любое слово или имя';this.style.color = '#c1cad0';}"
         onFocus="if(this.value=='Начните вводить любое слово или имя'){this.value='';this.style.color = '#000'}"
         onKeyPress="if(event.keyCode == 13)gSearch.go();"
         style="margin:0;color:#000"
         maxlength="65" />
  <div class="button_div fl_r"><button onClick="gSearch.go(); return false">Поиск</button></div>

  <div class="buttonsprofile albumsbuttonsprofile buttonsprofileSecond d-none" style="margin-top:10px;height:22px">
   <div class="{activetab-1}"><a href="/search/?{query-people}" onClick="Page.Go(this.href); return false;"><div><b>Люди</b></div></a></div>
   <div class="{activetab-4}"><a href="/search/{query-groups}" onClick="Page.Go(this.href); return false;"><div><b>Сообщества</b></div></a></div>
   <div class="{activetab-5}"><a href="/search/{query-audios}" onClick="Page.Go(this.href); return false;"><div><b>Аудиозаписи</b></div></a></div>
   <div class="{activetab-2}"><a href="/search/{query-videos}" onClick="Page.Go(this.href); return false;"><div><b>Видеозаписи</b></div></a></div>
   <div class="{activetab-3}"><a href="/search/{query-notes}" onClick="Page.Go(this.href); return false;"><div><b>Заметки</b></div></a></div>
  </div>



    <div class="site_menu_fix">
     <a href="/search/?{query-people}" onclick="Page.Go(this.href); return false;" class="left_row">
      <span class="left_label inl_bl "><span class="icon icon-user-7"></span>Люди</span>
     </a>
     <a href="/search/?{query-groups}" onclick="Page.Go(this.href); return false;" class="left_row">
      <span class="left_label inl_bl "><span class="icon icon-user-7"></span>Сообщества</span>
     </a>
     <a href="/search/?{query-audios}" onclick="Page.Go(this.href); return false;" class="left_row">
      <span class="left_label inl_bl "><span class="icon icon-user-7"></span>Аудиозаписи</span>
     </a>
     <a href="/search/?{query-videos}" onclick="Page.Go(this.href); return false;" class="left_row">
      <span class="left_label inl_bl "><span class="icon icon-user-7"></span>Видеозаписи</span>
     </a>

    </div>





  <input type="hidden" value="{type}" id="se_type_full" />
 </div>

[search-tab]
  <div class="search_sotrt_tab">
   
 <b>Основное</b>
 <div class="search_clear"></div>
   
 <div class="padstylej">
  <select name="country" id="country" class="inpst search_sel" onChange="Profile.LoadCity(this.value); gSearch.go();">
   <option value="0">Любая страна</option>{country}</select>
  <img src="/images/loading_mini.gif" alt="" class="load_mini" id="load_mini" />
 </div>
 <div class="search_clear"></div>

 <div class="padstylej">
  <select name="city" id="select_city" class="inpst search_sel" onChange="gSearch.go();">
   <option value="0">Любой город</option>{city}</select>
 </div>
 <div class="search_clear"></div>
 
 <div class="html_checkbox" id="online" onClick="myhtml.checkbox(this.id); gSearch.go();">сейчас на сайте</div>
 <div class="html_checkbox" id="user_photo" onClick="myhtml.checkbox(this.id); gSearch.go();" style="margin-top:9px">с фотографией</div>
 <div class="search_clear" style="margin-top:60px"></div>
 
 <b>Пол</b>
 <div class="search_clear"></div>
  
 <div class="padstylej"><select name="sex" id="sex" class="inpst search_sel" onChange="gSearch.go();"><option value="0">Все</option>{sex}</select></div>
 <div class="search_clear"></div>

 <b>День рождения</b>
 <div class="search_clear"></div>
 
 <div class="padstylej"><select name="day" class="inpst search_sel" id="day" onChange="gSearch.go();"><option value="0">Любой день</option>{day}</select>
 <div class="search_clear"></div>
  
 <select name="month" class="inpst search_sel" id="month" onChange="gSearch.go();"><option value="0">Любой месяц</option>{month}</select>
 <div class="search_clear"></div>
  
 <select name="year" class="inpst search_sel" id="year" onChange="gSearch.go();"><option value="0">Любой год</option>{year}</select></div>
 <div class="search_clear"></div>
  
</div>[/search-tab]

 <div class="clear"></div>
[yes]<div class="margin_top_10"></div><div class="search_result_title">Найдено {count}</div>[/yes]
<div id="jquery_jplayer"></div>
<input type="hidden" id="teck_id" value="0" />
<input type="hidden" id="typePlay" value="standart" />
<input type="hidden" id="teck_prefix" value="" />
 </div>
 <div class="col-12 col-sm-12 col-md-8 col-lg-4">