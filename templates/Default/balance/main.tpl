<script type="text/javascript" src="/js/payment.js"></script>
<div class="search_form_tab" style="margin-top:-9px">
 <div class="buttonsprofile albumsbuttonsprofile buttonsprofileSecond" style="height:22px">
  <div class="buttonsprofileSec"><a href="/balance" onClick="Page.Go(this.href); return false;"><div><b>Личный счёт</b></div></a></div>
  <a href="/balance/invite/" onClick="Page.Go(this.href); return false;"><div><b>Пригласить друга</b></div></a>
  <a href="/balance/invited/" onClick="Page.Go(this.href); return false;"><div><b>Приглашённые друзья</b></div></a>
 </div>
</div>
<div class="margin_top_10"></div><div class="allbar_title">Состояние личного счёта</div>
<div class="ubm_descr">
<b>Голоса</b> – это универсальная валюта для всех приложений на нашем сайте. Кроме этого, голосами можно оплатить подарки. Голосами нельзя оплатить рекламу. Обратите внимание, что услуга считается оказанной в момент зачисления голосов, возврат невозможен. Кроме этого за каждого приглашённого друга по вашей ссылке, вы будете получать по <b>10 голосов</b>, также каждый день на ваш счёт будет начислятся по <b>1 голосу</b>, если вы заходили в течении дня на сайт.
<br />
<br />
<center><span class="color777">На Вашем счёте:</span>&nbsp;&nbsp; <b><span id="num2">{ubm}</span> голос</b> и <b><span id="rub2">{rub}</span> {text-rub}</b></center>

<div class="btn-group mt-2" role="group" aria-label="Basic example">
	<button onClick="doLoad.data(2); payment.box_two();" class="btn btn-success" style="width:161px">Купить голоса</button>
	<button onClick="doLoad.data(2); payment.box()" class="btn btn-light" style="width:161px">Пополнить баланс</button>
</div>

</div>

<div class="margin_top_10"></div><div class="allbar_title">ПОПОЛНЕНИЕ БАЛАНСА С ПОМОЩЬЮ АКТИВАЦИИ КОДА</div>
<div class="ubm_descr">
<center>
<div class="err_red no_display" id="err_code" style="font-weight:normal;position:absolute;margin-top:10px;width:400px;"></div><br><br><br>
<div class="err_yellow no_display" id="ok_code" style="font-weight:normal;position:absolute;margin-top:10px;width:400px;">Код успешно активирован!</div>
<span class="color777">Введите код:</span>&nbsp;&nbsp; 
<input type="text" class="videos_input" id="code" style="width:200px" placeholder="AAAAA-BBBBB-CCCCC"/><br>
<div class="button_div fl_l" style="line-height:15px;margin-left:40%"><button id="code" onClick="payment.code();" style="width:161px">Активировать</button></div><br>

</center>
</div>