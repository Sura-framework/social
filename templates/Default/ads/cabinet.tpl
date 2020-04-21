<style>
.padcont {
background: none;
border-radius: none;
margin-bottom: none;
border: none;
min-width: none;
min-height: 760px;
}
</style>
<link media="screen" href="/style/ads.css" type="text/css" rel="stylesheet" /> 
<script type="text/javascript" src="/js/ads.js"></script>
<!-- <div class="search_form_tab" style="margin-top:-9px">
 <div class="buttonsprofile albumsbuttonsprofile" style="height:22px">
  <div class="activetab"><a href="/ads" onClick="Page.Go(this.href); return false;"><div><b>Реклама</b></div></a></div>
  <a href="/ads?act=cabinet" onClick="Page.Go(this.href); return false;"><div><b>Личный кабинет</b></div></a>
 </div>
</div>
 -->

<div class="search_form_tab" style="padding: 7px 0px 1px;">
 <div class="buttonsprofile albumsbuttonsprofile" style="height:22px">
  <a href="/ads/" onClick="Page.Go(this.href); return false;"><div>Реклама</div></a>
  <div class="buttonsprofileSec"><a href="/ads/cabinet/" onClick="Page.Go(this.href); return false;"><div>Личный кабинет</div></a></div>
 </div>
</div>
<div class="bg_block mt-5">
   <h2>Ваш баланс: {balance} руб.<div class="fl_r"><div class="button_div fl_l"><button class="fl_l" onClick="Page.Go('/ads/create/'); return false;">Создать объявление</button></div></div></h2>
   <div class="margin_top_10"></div>
   {myads}
</div>