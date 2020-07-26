@extends('app.app')
@section('content')
    <script type="text/javascript" src="/js/payment.js"></script>
    <div class="container-lg">
        <div class="row">
            <div class="col-4">
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/settings/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Настройки</a>
                    </div>
                </nav>
                <hr>
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/balance/" onClick="Page.Go(this.href); return false;" class="navbar-brand mb-0 h1 ">Баланс</a>
                    </div>
                </nav>
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/balance/invite/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Пригласить друга</a>
                    </div>
                </nav>
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/balance/invited/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Приглашённые друзья</a>
                    </div>
                </nav>
            </div>
            <div class="col-8">

                <div class="margin_top_10"></div><div class="allbar_title">Состояние личного счёта</div>
                <div class="alert alert-info" role="alert">
                    <b>Голоса</b> – это универсальная валюта для всех приложений на нашем сайте. Кроме этого, голосами можно оплатить подарки. Голосами нельзя оплатить рекламу. Обратите внимание, что услуга считается оказанной в момент зачисления голосов, возврат невозможен. Кроме этого за каждого приглашённого друга по вашей ссылке, вы будете получать по <b>10 голосов</b>, также каждый день на ваш счёт будет начислятся по <b>1 голосу</b>, если вы заходили в течении дня на сайт.
                </div>
                <div class="ubm_descr">

                    <div class="text-center mt-3"><span class="color777">На Вашем счёте:</span>&nbsp;&nbsp; <b><span id="num2">{{ $ubm }}</span> голос</b> и <b><span id="rub2">{{ $rub }}</span> {{ $text_rub }}</b></div>

                    <div class="btn-group mt-2" role="group" aria-label="Basic example">
                        <button onClick="doLoad.data(2); payment.box_two();" class="btn btn-success" style="width:161px">Купить голоса</button>
                        <button onClick="doLoad.data(2); payment.box()" class="btn btn-light" style="width:161px">Пополнить баланс</button>
                    </div>

                </div>

                <div class="mt-3"></div><div class="allbar_title">ПОПОЛНЕНИЕ БАЛАНСА С ПОМОЩЬЮ АКТИВАЦИИ КОДА</div>
                <div class="ubm_descr">
                    <div class="text-center">
                        <div class="err_red no_display" id="err_code" style="font-weight:normal;position:absolute;margin-top:10px;width:400px;"></div><br><br><br>
                        <div class="err_yellow no_display" id="ok_code" style="font-weight:normal;position:absolute;margin-top:10px;width:400px;">Код успешно активирован!</div>
                        <span class="color777">Введите код:</span>&nbsp;&nbsp;
                        <label for="code"></label>
                        <input type="text" class="videos_input" id="code" style="width:200px" placeholder="AAAAA-BBBBB-CCCCC"/><br>
                        <div class="button_div fl_l" style="line-height:15px;margin-left:40%"><button id="code" onClick="payment.code();" style="width:161px">Активировать</button></div><br>

                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection