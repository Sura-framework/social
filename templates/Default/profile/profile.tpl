<script type="text/javascript">
 [group=0][after-auth]Profile.LoadPhoto();[/after-reg][/group]
var startResizeCss = false;
var user_id = '{user-id}';
$(document).ready(function(){
	$('#wall_text, .fast_form_width').autoResize();
	[owner]if($('.profile_onefriend_happy').size() > 4) $('#happyAllLnk').show();
	[/owner]
});
// $(document).click(function(event){
// 	wall.event(event);
// });
</script>


<div class="d-flex justify-content-center">
 <div class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-6 mt-2">

<input type="hidden" id="type_page" value="profile" />
<style>.newcolor000{color:#000}</style>
<div id="jquery_jplayer"></div>
<input type="hidden" id="teck_id" value="" />
<input type="hidden" id="teck_prefix" value="" />
<input type="hidden" id="typePlay" value="standart" />

<div class="d-sm-flex justify-content-center">
<div class="col-sm-4">

<div class="card">
<div class="card-body">
<div class="">
 [owner]
   <div class="cover_newava" {cover-param-7}>
   <span id="ava" class="d-flex justify-content-center">
    <img class="w-100" src="{ava}" alt="" title="" id="ava_{user-id}" />
   </span>
  </div>

   <div class="owner_photo_bubble_wrap">
    <div class="owner_photo_bubble"><div class="owner_photo_bubble_action owner_photo_bubble_action_update" onclick="Profile.LoadPhoto()" tabindex="0" role="button">
      <span class="owner_photo_bubble_action_in">Обновить фотографию</span>
     </div><div class="owner_photo_bubble_action owner_photo_bubble_action_crop" onclick="Profile.miniature()" tabindex="0" role="button">
      <span class="owner_photo_bubble_action_in">Изменить миниатюру</span>
     </div></div>
   </div>
 [/owner]
 [not-owner][cover]<div class="cover_newava" {cover-param-7}>[/cover]<span id="ava"><img src="{ava}" alt="" title="" id="ava_{user-id}" /></span>[cover]</div>[/cover][/not-owner]
  <hr>
  [not-owner][blacklist][privacy-msg]<a href="/" onClick="messages.new_({user-id}); return false"><img class="icon mail_ic" src="/images/spacer.gif" alt="" /><div>Отправить сообщение</div></a>[/privacy-msg][/blacklist]
  <a href="/" onClick="gifts.box('{user-id}'); return false"><img class="icon new_gift" src="/images/spacer.gif" alt="" /><div>Отправить подарок</div></a>
  [/not-owner]
  <div class="row">
   <div class="col-10">
    <nav class="nav">
     [owner]<a class="nav-link active w-100" href="/edit/" onClick="Page.Go(this.href); return false;"><img class="icon edit_ic" src="/images/spacer.gif" alt="" /><div>Редактировать</div></a>[/owner]
     [not-owner]
     [no-friends][blacklist]<a class="nav-link active" href="/" onClick="friends.add({user-id}); return false"><img class="icon addfr_ic" src="/images/spacer.gif" alt="" /><div>Добавить в друзья</div></a>[/blacklist][/no-friends]
     [yes-friends]<a class="nav-link active" href="/" onClick="friends.delet({user-id}, 1); return false"><img class="icon del_friends" src="/images/spacer.gif" alt="" /><div>Убрать из друзей</div></a>[/yes-friends]
     [blacklist][no-subscription]<a class="nav-link active" href="/" onClick="subscriptions.add({user-id}); return false" id="lnk_unsubscription"><img class="icon subs_ic" src="/images/spacer.gif" alt="" /><div><span id="text_add_subscription">Подписаться на обновления</span> <img src="/images/loading_mini.gif" alt="" id="addsubscription_load" class="no_display" style="margin-right:-13px" /></div></a>[/no-subscription][/blacklist]
     [yes-subscription]<a class="nav-link active" href="/" onClick="subscriptions.del({user-id}); return false" id="lnk_unsubscription"><img class="icon subs_ic" src="/images/spacer.gif" alt="" /><div><span id="text_add_subscription">Отписаться от обновлений</span> <img src="/images/loading_mini.gif" alt="" id="addsubscription_load" class="no_display" style="margin-right:-13px" /></div></a>[/yes-subscription]
     [/not-owner]
    </nav>
   </div>
    <div class="col-2">
 <div class="dropdown">
  <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
   <i class="fe fe-more-vertical"></i>
  </a>
  <div class="dropdown-menu dropdown-menu-right">[owner]
   <a href="/my_stats/" class="dropdown-item">Статистика страницы</a>
   <a href="/docs/" class="dropdown-item">Мои документы</a>
   <a href="/edit/" class="dropdown-item">Редактировать страницу</a>
   <a href="#" onClick="Profile.LoadPhoto(); return false;" class="dropdown-item">Изменить фотографию</a>
   <a href="#" onClick="Profile.LoadPhoto(); return false;" class="dropdown-item">Изменить миниатюру</a>
   <a href="#" onClick="Profile.DelPhoto(); return false;" id="del_pho_but" {display-ava} class="dropdown-item">Удалить миниатюру</a>
 [/owner]
 [not-owner]
 [no-fave]<a href="/" class="dropdown-item" onClick="fave.add({user-id}); return false" id="addfave_but"><img class="icon fav_ic" src="/images/spacer.gif" alt="" /><div><span id="text_add_fave">Добавить в закладки</span> <img src="/images/loading_mini.gif" alt="" id="addfave_load" class="no_display" /></div></a>[/no-fave]
 [yes-fave]<a href="/" class="dropdown-item" onClick="fave.delet({user-id}); return false" id="addfave_but"><img class="icon fav_ic" src="/images/spacer.gif" alt="" /><div><span id="text_add_fave">Удалить из закладок</span> <img src="/images/loading_mini.gif" alt="" id="addfave_load" class="no_display" /></div></a>[/yes-fave]
 [no-blacklist]<a href="/" class="dropdown-item" onClick="settings.addblacklist({user-id}); return false" id="addblacklist_but"><img class="icon compla_ic" src="/images/spacer.gif" alt="" /><div><span id="text_add_blacklist">Заблокировать</span> <img src="/images/loading_mini.gif" alt="" id="addblacklist_load" class="no_display" /></div></a>[/no-blacklist]
 [yes-blacklist]<a href="/" class="dropdown-item" onClick="settings.delblacklist({user-id}, 1); return false" id="addblacklist_but"><img class="icon compla_ic" src="/images/spacer.gif" alt="" /><div><span id="text_add_blacklist">Разблокировать</span> <img src="/images/loading_mini.gif" alt="" id="addblacklist_load" class="no_display" /></div></a>[/yes-blacklist]
 [/not-owner]
   </div>
  </div>
    </div>
  </div>

</div>
</div>
</div>
 <div class="card mt-2">
  <div class="card-body">

[blacklist]<div class="leftcbor">
 [owner][happy-friends]<div id="happyBLockSess"><div class="albtitle">Дни рожденья друзей <span>{happy-friends-num}</span><div class="profile_happy_hide"><img src="/images/hide_lef.gif" onMouseOver="myhtml.title('1', 'Скрыть', 'happy_block_')" id="happy_block_1" onClick="HappyFr.HideSess(); return false" /></div></div>
 <div class="newmesnobg profile_block_happy_friends" style="padding:0px;padding-top:10px;">{happy-friends}<div class="clear"></div></div>
 <div class="cursor_pointer no_display" onMouseDown="HappyFr.Show(); return false" id="happyAllLnk"><div class="public_wall_all_comm profile_block_happy_friends_lnk">Показать все</div></div></div>
 [/happy-friends][/owner]
  [common-friends]<a href="/friends/common/{user-id}/" style="text-decoration:none" onClick="Page.Go(this.href); return false"><div class="albtitle">Общие друзья <span>{mutual-num}</span></div></a>
 <div class="newmesnobg" style="padding:0px;padding-top:10px;">{mutual_friends}<div class="clear"></div>
 </div>[/common-friends]
 [friends]<a href="/friends/{user-id}/" onClick="Page.Go(this.href); return false" style="text-decoration:none"><div class="albtitle">Друзья <span>{friends-num}</span></div></a>
 <div class="newmesnobg" style="padding:0px;padding-top:10px;">{friends}<div class="clear"></div>
 </div>[/friends]
 [online-friends]<a href="/friends/online/{user-id}/" style="text-decoration:none" onClick="Page.Go(this.href); return false"><div class="albtitle">Друзья на сайте <span>{online-friends-num}</span></div></a>
 <div class="newmesnobg" style="padding:0px;padding-top:10px;">{online-friends}<div class="clear"></div>
 </div>[/online-friends]
 [subscriptions]<a href="/" onClick="subscriptions.all({user-id}, '', {subscriptions-num}); return false" style="text-decoration:none"><div class="albtitle">Подписки <span>{subscriptions-num}</span></div></a>
 <div class="newmesnobg" style="padding-right:0px;padding-bottom:0px;">{subscriptions}<div class="clear"></div>
 </div>[/subscriptions]
 [groups]<div class="albtitle cursor_pointer" onClick="groups.all_groups_user('{user-id}')">Сообщества <span id="groups_num">{groups-num}</span></div>
 <div class="newmesnobg" style="padding-right:0px;padding-bottom:0px;">{groups}<div class="clear"></div>
 </div>[/groups]
 [videos]<a href="/videos/{user-id}/" onClick="Page.Go(this.href); return false" style="text-decoration:none"><div class="albtitle">Видеозаписи <span>{videos-num}</span></div></a>
 <div class="newmesnobg" style="padding-right:0px;padding-bottom:0px;">{videos}<div class="clear"></div>
 </div>[/videos]
<div class="clear"></div>
<span id="fortoAutoSizeStyleProfile"></span>
</div>[/blacklist]
</div>






</div>
</div>
 <div class="col-sm-8">
<div class="card">
 <div class="card-body">
 <div class="padcont2 mt-3 shadow2">
<div class="profiewr">
 [owner]<div class="set_status_bg no_display" id="set_status_bg">
  <input type="text" id="status_text" class="status_inp" value="{status-text}" style="width:500px;" maxlength="255" onKeyPress="if(event.keyCode == 13)gStatus.set()" />
  <div class="fl_l status_text"><span class="no_status_text [status]no_display[/status]">Введите здесь текст Вашего статуса.</span><a href="/" class="yes_status_text [no-status]no_display[/no-status]" onClick="gStatus.set(1); return false">Удалить статус</a></div>
  [status]<div class="button_div_gray fl_r status_but margin_left"><button>Отмена</button></div>[/status]
  <div class="button_div fl_r status_but"><button id="status_but" onClick="gStatus.set()">Сохранить</button></div>
 </div>[/owner]
 <div class="titleu">{name} {lastname} <a class="fl_r color777" style="text-decoration:none"><b>{online}</b></a></div>
 <div class="status">
  <div>[owner]<a href="/" id="new_status" onClick="gStatus.open(); return false">[/owner][blacklist]{status-text}[/blacklist][owner]</a>[/owner]</div>
  [owner]<span id="tellBlockPos"></span>
  <div class="status_tell_friends no_display">
   <div class="status_str"></div>
   <div class="html_checkbox" id="tell_friends" onClick="myhtml.checkbox(this.id); gStatus.startTell()">Рассказать друзьям</div>
  </div>[/owner]
  [owner]<a href="#" onClick="gStatus.open(); return false" id="status_link" [status]class="no_display"[/status]>установить статус</a>[/owner]
 </div>
 <div class="profile_rate_pos">
  <div class="profile_rate_text">Рейтинг</div>
  [owner]<a class="cursor_pointer" onClick="doLoad.data(1); rating.view()">[/owner]<div class="profile_rate_100_left {rating-class-left}"></div>[owner]</a>[/owner]
  <div class="profile_rate_add" onClick="doLoad.data(1); rating.addbox('{user-id}')" onMouseOver="myhtml.title('1', 'Повысить рейтинг', 'rate', 1)" id="rate1"><img src="/images/icons/rate_ic.png" /></div>
  [owner]<a class="cursor_pointer" onClick="doLoad.data(1); rating.view()" style="text-decoration:none">[/owner]<div class="profile_rate_100_right {rating-class-right}"></div>
  <div class="profile_rate_100_head {rating-class-head}" id="profile_rate_num">{rating}</div>[owner]</a>[/owner]
 </div>
 <div style="min-height:50px">
 [not-all-country]<div class="flpodtext">Страна:</div> <div class="flpodinfo"><a href="/search/?country={country-id}" onClick="Page.Go(this.href); return false">{country}</a></div>[/not-all-country]
 [not-all-city]<div class="flpodtext">Город:</div> <div class="flpodinfo"><a href="/search/?country={country-id}&city={city-id}" onClick="Page.Go(this.href); return false">{city}</a></div>[/not-all-city]
 [blacklist][not-all-birthday]<div class="flpodtext">День рождения:</div> <div class="flpodinfo">{birth-day}</div>[/not-all-birthday]
 [privacy-info][sp]<div class="flpodtext">Семейное положение:</div> <div class="flpodinfo">{sp}</div>[/sp][/privacy-info]
 </div>
 <div class="cursor_pointer" onClick="Profile.MoreInfo(); return false" id="moreInfoLnk"><div class="public_wall_all_comm profile_hide_opne" id="moreInfoText">Показать подробную информацию</div></div>
 <div id="moreInfo" class="no_display">
 [privacy-info][not-block-contact]<div class="fieldset"><div class="w2_a" [owner]style="width:230px;"[/owner]>Контактная информация [owner]<span><a href="/edit/contact/" onClick="Page.Go(this.href); return false;">редактировать</a></span>[/owner]</div></div>
 [not-contact-phone]<div class="flpodtext">Моб. телефон:</div> <div class="flpodinfo">{phone}</div>[/not-contact-phone]
 [not-contact-vk]<div class="flpodtext">В контакте:</div> <div class="flpodinfo">{vk}</div>[/not-contact-vk]
 [not-contact-od]<div class="flpodtext">Одноклассники:</div> <div class="flpodinfo">{od}</div>[/not-contact-od]
 [not-contact-fb]<div class="flpodtext">FaceBook:</div> <div class="flpodinfo">{fb}</div>[/not-contact-fb]
 [not-contact-skype]<div class="flpodtext">Skype:</div> <div class="flpodinfo"><a href="skype:{skype}">{skype}</a></div>[/not-contact-skype]
 [not-contact-icq]<div class="flpodtext">ICQ:</div> <div class="flpodinfo">{icq}</div>[/not-contact-icq]
 [not-contact-site]<div class="flpodtext">Веб-сайт:</div> <div class="flpodinfo">{site}</div>[/not-contact-site][/not-block-contact]
 <div class="fieldset"><div class="w2_b" [owner]style="width:200px;"[/owner]>Личная информация [owner]<span><a href="/edit/interests/" onClick="Page.Go(this.href); return false;">редактировать</a></span>[/owner]</div></div>{not-block-info}
 [not-info-activity]<div class="flpodtext">Деятельность:</div> <div class="flpodinfo">{activity}</div>[/not-info-activity]
 [not-info-interests]<div class="flpodtext">Интересы:</div> <div class="flpodinfo">{interests}</div>[/not-info-interests]
 [not-info-music]<div class="flpodtext">Любимая музыка:</div> <div class="flpodinfo">{music}</div>[/not-info-music]
 [not-info-kino]<div class="flpodtext">Любимые фильмы:</div> <div class="flpodinfo">{kino}</div>[/not-info-kino]
 [not-info-books]<div class="flpodtext">Любимые книги:</div> <div class="flpodinfo">{books}</div>[/not-info-books]
 [not-info-games]<div class="flpodtext">Любимые игры:</div> <div class="flpodinfo">{games}</div>[/not-info-games]
 [not-info-quote]<div class="flpodtext">Любимые цитаты:</div> <div class="flpodinfo">{quote}</div>[/not-info-quote]
 [not-info-myinfo]<div class="flpodtext">О себе:</div> <div class="flpodinfo">{myinfo}</div>[/not-info-myinfo][/privacy-info]
 </div>
 [albums]<a href="/albums/{user-id}" onClick="Page.Go(this.href); return false" style="text-decoration:none"><div class="albtitle" style="margin-top:5px">Альбомы <span>{albums-num}</span><div><b>Все</b></div></div></a>{albums}<div class="clear"></div>[/albums]
 [audios]<div id="jquery_jplayer"></div><input type="hidden" id="teck_id" value="1" /><a href="/audio{user-id}" onClick="Page.Go(this.href); return false" style="text-decoration:none"><div class="albtitle" style="margin-top:5px">{audios-num}<div><b>Все</b></div></div></a>{audios}<div class="clear"></div>[/audios]
 [gifts]<a href="/gifts{user-id}" onClick="Page.Go(this.href); return false" style="text-decoration:none"><div class="albtitle" style="margin-top:5px">{gifts-text}<div><b>Все</b></div></div><center>{gifts}</center><div class="clear"></div></a>[/gifts]
 </div>
 <div class="clear"></div>
</div>
 </div>

</div>
<!-- </div> -->


<div class="padcont2 mt-3 shadow2">
 <div class="profiewr">
 <a href="/wall/{user-id}/" onClick="Page.Go(this.href); return false" style="text-decoration:none"><div class="albtitle" style="border-bottom:0px">Публикации <span id="wall_rec_num">{wall-rec-num}</span></div></a>
 [privacy-wall]<div class="newmes" id="wall_tab" style="border-bottom:0px;margin-bottom:-5px">
  <input type="hidden" value="[owner]Что у Вас нового?[/owner][not-owner]Написать сообщение...[/not-owner]" id="wall_input_text" />
  <input type="text" class="wall_inpst" value="[owner]Что у Вас нового?[/owner][not-owner]Написать сообщение...[/not-owner]" onMouseDown="wall.form_open(); return false" id="wall_input" style="margin:0px" />
  <div class="no_display" id="wall_textarea">
   <textarea id="wall_text" class="wall_inpst wall_fast_opened_texta"
	onKeyUp="wall.CheckLinkText(this.value)"
	onBlur="wall.CheckLinkText(this.value, 1)"
   >
   </textarea>
   <div id="attach_files" class="margin_top_10 no_display"></div>
   <div id="attach_block_lnk" class="no_display clear">
   <div class="attach_link_bg">
    <div align="center" id="loading_att_lnk"><img src="/images/loading_mini.gif" style="margin-bottom:-2px" /></div>
    <img src="" align="left" id="attatch_link_img" class="no_display cursor_pointer" onClick="wall.UrlNextImg()" />
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
	<div class="texta">Тема опроса:</div><input type="text" id="vote_title" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px" 
		onKeyUp="$('#attatch_vote_title').text(this.value)"
	/><div class="mgclr"></div>
	<div class="texta">Варианты ответа:<br /><small><span id="addNewAnswer"><a class="cursor_pointer" onClick="Votes.AddInp()">добавить</a></span> | <span id="addDelAnswer">удалить</span></small></div><input type="text" id="vote_answer_1" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px" /><div class="mgclr"></div>
	<div class="texta">&nbsp;</div><input type="text" id="vote_answer_2" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px" /><div class="mgclr"></div>
	<div id="addAnswerInp"></div>
	<div class="clear"></div>
   </div>
   <div class="attach_toolip_but"></div>
   <div class="attach_link_block_ic fl_l"></div><div class="attach_link_block_te"><div class="fl_l">Опрос: <a id="attatch_vote_title" style="text-decoration:none;cursor:default"></a></div><img class="fl_l cursor_pointer" style="margin-top:2px;margin-left:5px" src="/images/close_a.png" onMouseOver="myhtml.title('1', 'Не прикреплять', 'attach_vote_')" id="attach_vote_1" onClick="Votes.RemoveForAttach()" /></div>
   <input type="hidden" id="answerNum" value="2" />
   </div>
   <div class="clear"></div>
   <input id="vaLattach_files" type="hidden" />
   <div class="clear"></div>
   <div class="button_div fl_l margin_top_10"><button onClick="wall.send(); return false" id="wall_send">Отправить</button></div>
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
 </div>[/privacy-wall]
 <div id="wall_records">{records}[no-records]<div class="wall_none" [privacy-wall]style="border-top:0px"[/privacy-wall]>На стене пока нет ни одной записи.</div>[/no-records]</div>
 [wall-link]<span id="wall_all_record"></span><div onClick="wall.page('{user-id}'); return false" id="wall_l_href" class="cursor_pointer"><div class="photo_all_comm_bg wall_upgwi" id="wall_link">к предыдущим записям</div></div>[/wall-link][/blacklist]
 [not-blacklist]<div class="err_yellow" style="font-weight:normal;margin-top:5px">{name} ограничила доступ к своей странице.</div>[/not-blacklist]
</div>
<div class="clear"></div>
</div>

</div>
</div>

</div>
</div>