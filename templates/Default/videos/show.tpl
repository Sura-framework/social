
<div id="video_show_{vid}" class="video_view" onClick="videos.setEvent(event, {owner-id}, '{close-link}')">
<div class="photo_close" onClick="videos.close({owner-id}, '{close-link}'); return false"></div>
 <div class="container video_show_bg">
  <div class="video_show_object" style="
    display: flex;height: auto;    max-height: 450px;
">
  
<div id="video_object" style="width: 800px;position: relative;">

<style>.video_show_object {padding: 0px;}</style>

[not-owner]
<!-- <div class="videoplayer_share_actions">
  <div class="_donate" style="display: none;">
<svg class="_donate_icon" viewBox="5 8 26 22" xmlns="http://www.w3.org/2000/svg">
  <path d="M21 21v-8h-2v8h-2v2h2v2h2v-2h3v-2h-3zm2-3.077V20s3 0 3-3.5-3-3.5-3-3.5v2.087C22.725 15 22.5 15 22.5 15H21v3h1.5c.17 0 .34-.026.5-.077V20h-2v-7h2v2.087c.436.138 1 .493 1 1.413 0 .77-.45 1.246-1 1.423zM21.5 28c-5.247 0-9.5-4.253-9.5-9.5S16.253 9 21.5 9s9.5 4.253 9.5 9.5-4.253 9.5-9.5 9.5z" fill="#FFF" fill-rule="evenodd"></path>
  <path d="M15.818 27.995c-.106.003-.212.005-.318.005-5.247 0-9.5-4.253-9.5-9.5S10.253 9 15.5 9c.446 0 .884.03 1.314.09C12.844 10.503 10 14.294 10 18.75c0 4.073 2.376 7.592 5.818 9.245z" fill="#FFF" fill-rule="evenodd"></path>
</svg>
  </div>
  <div class="_like">
<svg class="_like_icon" viewBox="10 11 17 15" xmlns="http://www.w3.org/2000/svg" focusable="false">
  <path d="M18.5 13.922c-1.7-3.4-5.097-3.393-7.042-1.43-1.944 1.96-1.944 5.147 0 7.11.608.612 5.834 5.76 5.834 5.76.608.613 1.702.613 2.31 0l5.833-5.76c2.066-1.963 2.066-5.15.12-7.11-1.943-1.84-5.355-1.97-7.055 1.43z" fill="#FFF" fill-rule="evenodd"></path>
</svg>
  </div>
  <div class="_share" onclick="Repost_Videos.Box({vid});">
<svg class="_share_icon" viewBox="11 11 14 15" xmlns="http://www.w3.org/2000/svg" focusable="false">
  <path d="M14.16 21h-.637C12.077 21 11 19.39 11 18.09v-1.18c0-1.3 1.077-2.91 2.523-2.91H17c1.358 0 1.694.31 3.712-.99 1.387-.946 2.9-2.01 2.9-2.01H25v13h-1.26s-1.767-1.182-3.154-2.127c-1.46-.948-2.088-.9-3.166-.878.11.784.315 2.057.58 2.734 0 1.475-.133 2.27-2.667 2.27l-1.174-5z" fill="#FFF" fill-rule="evenodd"></path>
</svg>
  </div>
  <div class="_add">
  
  
<svg class="_add_icon" viewBox="10 11 17 15" xmlns="http://www.w3.org/2000/svg" focusable="false" onClick="videos.addmylist('{vid}'); return false">
  <path d="M20 17v-4.993C20 11.45 19.553 11 19 11h-1c-.557 0-1 .45-1 1.007V17h-4.993C11.45 17 11 17.447 11 18v1c0 .557.45 1 1.007 1H17v4.993c0 .558.447 1.007 1 1.007h1c.557 0 1-.45 1-1.007V20h4.993C25.55 20 26 19.553 26 19v-1c0-.557-.45-1-1.007-1H20z" fill="#FFF" fill-rule="evenodd" class="_plus"></path>
</svg>


  </div>
</div> -->
[/not-owner]

<style>
  video {height: 450px;}
  video:fullscreen {width:auto;height:auto;}
</style>

<video  controls="controls" width="" height="" id="video1" class="video_fp">
  <!-- Video files -->
  	{video_240}
	{video_720}
	{video_1080}
	{video}
</video>

</div>
   
<div id="video_playlist">
  <div style="padding: 8px 9px 7px;color: #fff;font-size: 12px;">[owner]Мои видеозаписи[/owner][not-owner]Все видеозаписи[/not-owner]</div>
  {vplaylist}
</div> 

<input type="hidden" value="{vid}" id="vid"/>

<script>
function next(){
if($('.plvideo'+$('#vid').val()).next()) $('.plvideo'+$('#vid').val()).next().click();
}

$( document ).ready(function() {
const plyr = new Plyr(document.querySelector('.video_fp'));
});
   </script>

  </div>
  <div class="video_show_panel" id="video_del_info">
   <div class="photo_leftcol video_show_left_col">
    <div class="video_show_descr" id="video_full_descr_{vid}">{descr}</div>
    <div class="video_show_date">Добавлена {date}</div><br />
	[all-comm]<a href="/" onClick="videos.allcomment({vid}, {comm-num}, {owner-id}); return false" id="all_href_lnk_comm"><div class="photo_all_comm_bg" id="all_lnk_comm">Показать {prev-text-comm}</div></a><span id="all_comments"></span>[/all-comm]
	[admin-comments]<span id="comments">{comments}</span>
    <textarea id="comment" class="inpst" style="width: 350px;height:35px;margin-bottom:10px;"></textarea>
    <div class="button_div fl_l"><button onClick="videos.addcomment({vid}); return false" id="add_comm">Отправить</button></div>[/admin-comments]
   </div>
   <div class="photo_rightcol">
    {views}
    Отправитель:<br /><a href="/u{uid}" onClick="Page.Go(this.href); return false">{author}</a><br /><br />
	[public]
     [owner]<a href="/" onClick="videos.editbox({vid}); return false"><div>Редактировать</div></a> 
	 <a href="/" onClick="videos.delet({vid}, 1); return false"><div>Удалить</div></a>[/owner]
	 <a onClick="Report.Box('video', '{vid}')"><div>Пожаловаться</div></a>
    </div>[/public]

  <div class="clear"></div>
  </div>
 </div>
</div>