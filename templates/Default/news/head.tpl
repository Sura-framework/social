
<script type="text/javascript">
var page_cnt = 1;
$(document).ready(function(){
	$('#wall_text, .fast_form_width').autoResize();
	$(window).scroll(function(){
		if($(document).height() - $(window).height() <= $(window).scrollTop()+($(document).height()/2-250)){
			news.page();
		}
	});
});
$(document).click(function(event){
	wall.event(event);
});
</script>
<style>.newcolor000{color:#000}</style>
<div class="">
<div class="d-flex justify-content-between">


  <div class="col-2 d-none d-sm-none d-md-none d-lg-block">
    [news]
   <div class="">
    <div class="card-body">
        <div class="site_menu_fix" style="left: 490px;">
            <a href="{my-page-link}" onclick="Page.Go(this.href); return false;" class="left_row">
                <span class="left_label inl_bl "><span class="icon icon-user-7"></span>Моя Страница</span>
            </a>
            <a href="/messages/" onclick="Page.Go(this.href); return false;" class="left_row">
                <span class="left_label inl_bl "><span class="icon icon-mail-2"></span>Сообщения <span id="new_msg">{msg}</span></span>
            </a>
            <a href="/friends/{requests-link}" onclick="Page.Go(this.href); return false;" class="left_row" id="requests_link">
                <span class="left_label inl_bl "><span class="icon icon-users-3"></span>Друзья <span id="new_requests">{demands}</span></span>
            </a>
            <a href="/albums/{my-id}/" onclick="Page.Go(this.href); return false;" class="left_row" id="requests_link_new_photos">
                <span class="left_label inl_bl "><span class="icon icon-picture-3"></span>Фото <span id="new_photos">{new_photos}</span></span>
            </a>
            <a href="/fave/" onclick="Page.Go(this.href); return false;" class="left_row">
                <span class="left_label inl_bl "><span class="icon icon-user-7"></span>Закладки</span>
            </a>
            <a href="/videos/" onclick="Page.Go(this.href); return false;" class="left_row">
                <span class="left_label inl_bl "><span class="icon icon-videocam-3"></span>Видео</span>
            </a>
            <a href="/audio/" onclick="Page.Go(this.href); return false;" class="left_row">
                <span class="left_label inl_bl "><span class="icon icon-music-2" ></span>Музыка</span>
            </a>
            <a href="{groups-link}/" onclick="Page.Go(this.href); return false;" class="left_row"  id="new_groups_lnk">
                <span class="left_label inl_bl "><span class="icon icon-users-2"></span>Группы <span id="new_groups">{new_groups}</span></span>
            </a>
            <a href="/news/{news-link}" onclick="Page.Go(this.href); return false;" class="left_row"  id="news_link">
                <span class="left_label inl_bl "><span class="icon icon-globe-alt"></span>Лента <span id="new_news">{new-news}</span></span>
            </a>
            <a href="/settings/" onclick="Page.Go(this.href); return false" class="left_row">
                <span class="left_label inl_bl "><span class="icon icon-cog-4"></span>Настройки</span>
            </a>
            <a href="/support/" onclick="Page.Go(this.href); return false" class="left_row">
                <span class="left_label inl_bl "><span class="icon icon-help"></span>Помощь <span id="new_support">{new-support}</span></span>
            </a>
            <a href="{ubm-link}" onclick="Page.Go(this.href); return false" class="left_row">
                <span class="left_label inl_bl "><span class="icon icon-money"></span>Баланс <span id="new_ubm">{new-ubm}</span></span>
            </a>
        </div>

    </div>

   </div>
    <div class="mt-5 mb-5"></div>
    [/news]
  </div>
 <div class="col-12 col-sm-12 col-md-8 col-lg-4 ">
  <div class="card">
   <div class="card-body">

    <div class="bg_block" >
     <div class="newmes" id="wall_tab">
<textarea id="wall_text"  onblur="if(this.value=='') this.value='Что у Вас нового?';this.style.color = '#909090';$('#wall_text').css('height', '33px');" onfocus="if(this.value=='Что у Вас нового?')this.value='';this.style.color = '#000000';$('#wall_text').css('height', '50px');" class="wall_inpst wall_fast_opened_texta"  style="width: 100%;
resize: none;
overflow-y: hidden;
border-bottom: 1px solid #E4E4E4;
margin-top: -5px;
color: #909090;
font-weight: 500;" onkeyup="wall.CheckLinkText(this.value)" onblur="wall.CheckLinkText(this.value, 1)">Что у Вас нового?</textarea>
      <div id="attach_files" class="margin_top_10 no_display"></div>
      <div id="attach_block_lnk" class="no_display clear">
       <div class="attach_link_bg">
        <div align="center" id="loading_att_lnk"><img src="/images/loading_mini.gif" style="margin-bottom:-2px"></div>
        <img src="" align="left" id="attatch_link_img" class="no_display cursor_pointer" onclick="wall.UrlNextImg()">
        <div id="attatch_link_title"></div>
        <div id="attatch_link_descr"></div>
        <div class="clear"></div>
       </div>
       <div class="attach_toolip_but"></div>
       <div class="attach_link_block_ic fl_l"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/" id="attatch_link_url" target="_blank"></a></div><img class="fl_l cursor_pointer" style="margin-top:2px;margin-left:5px" src="/images/close_a.png" onmouseover="myhtml.title('1', 'Не прикреплять', 'attach_lnk_')" id="attach_lnk_1" onclick="wall.RemoveAttachLnk()"></div>
       <input type="hidden" id="attach_lnk_stared">
       <input type="hidden" id="teck_link_attach">
       <span id="urlParseImgs" class="no_display"></span>
      </div>
      <div class="clear"></div>
      <div id="attach_block_vote" class="no_display">
       <div class="attach_link_bg">
        <div class="texta">Тема опроса:</div><input type="text" id="vote_title" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px" onkeyup="$('#attatch_vote_title').text(this.value)"><div class="mgclr"></div>
        <div class="texta">Варианты ответа:<br><small><span id="addNewAnswer"><a class="cursor_pointer" onclick="Votes.AddInp()">добавить</a></span> | <span id="addDelAnswer">удалить</span></small></div><input type="text" id="vote_answer_1" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px"><div class="mgclr"></div>
        <div class="texta">&nbsp;</div><input type="text" id="vote_answer_2" class="inpst" maxlength="80" value="" style="width:355px;margin-left:5px"><div class="mgclr"></div>
        <div id="addAnswerInp"></div>
        <div class="clear"></div>
       </div>
       <div class="attach_toolip_but"></div>
       <div class="attach_link_block_ic fl_l"></div><div class="attach_link_block_te"><div class="fl_l">Опрос: <a id="attatch_vote_title" style="text-decoration:none;cursor:default"></a></div><img class="fl_l cursor_pointer" style="margin-top:2px;margin-left:5px" src="/images/close_a.png" onmouseover="myhtml.title('1', 'Не прикреплять', 'attach_vote_')" id="attach_vote_1" onclick="Votes.RemoveForAttach()"></div>
       <input type="hidden" id="answerNum" value="2">
      </div>
      <div class="clear"></div>
      <input id="vaLattach_files" type="hidden">
      <div class="clear"></div>
      <div class="wall_attach_icon_photo fl_l" id="wall_attach_link" onclick="wall.attach_addphoto()">Фотография</div>
      <div class="wall_attach_icon_doc fl_l" id="wall_attach_link" onclick="wall.attach_addDoc()">Документ</div>
      <div class="wall_attach_icon_video fl_l" id="wall_attach_link" onclick="wall.attach_addvideo()"></div>
      <div class="wall_attach_icon_audio fl_l" id="wall_attach_link" onclick="wall.attach_addaudio()"></div>
      <div class="wall_attach_icon_vote fl_l" id="wall_attach_link" onclick="$('#attach_block_vote').slideDown('fast');wall.attach_menu('close', 'wall_attach', 'wall_attach_menu');$('#vote_title').focus();$('#vaLattach_files').val($('#vaLattach_files').val()+'vote|start||')"></div>

      <div class="button_div fl_r margin_top_10"><button onclick="wall.send_news(); return false" id="wall_send">Отправить</button></div>

     </div>

     <div class="clear"></div>

    </div>


   </div>
  </div>

[bottom]
 <span id="news"></span>
<div onClick="news.page()" id="wall_l_href_news" class="cursor_pointer"><div class="photo_all_comm_bg wall_upgwi" id="loading_news" style="width:750px">Показать предыдущие новости</div></div>[/bottom]

