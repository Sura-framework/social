@extends('app.app')
@section('content')
<div class="container">
    <div class="d-flex _4_yKc">
        <div class="_80tAB yOZjD  ">
            <div class="text-center V64Sp">
                <img class="RP4i1 " src="/images/home_phone.jpg" alt="">
            </div>
        </div>
        <h1 class="d-none">Sura - Первая независимая социальная сеть, которая поможет всегда оставаться на связи и общаться со своими друзьями.</h1>
<style>
._4_yKc {-webkit-box-orient: horizontal;-webkit-box-direction: normal;-webkit-flex-direction: row;-ms-flex-direction: row;flex-direction: row;-webkit-box-flex: 1;-webkit-flex-grow: 1;-ms-flex-positive: 1;flex-grow: 1;-webkit-box-pack: center;-webkit-justify-content: center;-ms-flex-pack: center;justify-content: center;margin: 30px auto 0;max-width: 935px;padding-bottom: 44px;width: 100%;align-items: center;}
.yOZjD {-webkit-align-self: center;-ms-flex-item-align: center;align-self: center;background-image: url(/images/phones.png);background-position: 0 0;background-size: 454px 618px;-webkit-flex-basis: 454px;-ms-flex-preferred-size: 454px;flex-basis: 454px;height: 618px;margin-left: -35px;margin-right: -15px;width: 450px;}
.RP4i1 {height: 427px;opacity: 1;position: absolute;visibility: inherit;width: 240px;left: 0;top: 0;}
.rgFsT {color: #262626;color: rgba(var(--i1d,38,38,38),1);-webkit-box-flex: 1;-webkit-flex-grow: 1;-ms-flex-positive: 1;flex-grow: 1;-webkit-box-pack: center;-webkit-justify-content: center;-ms-flex-pack: center;justify-content: center;margin-top: 12px;max-width: 350px;width: 100%;}
.V64Sp {margin: 99px 0 0 151px;position: relative;}
.shadows {box-shadow: 0 3px 11px #aaa;-moz-box-shadow: 0 3px 11px #aaa;-webkit-box-shadow: 0 3px 11px #aaa;}
@media (max-width: 768px) {
.RP4i1 {left: 37.8%;}
.yOZjD{display: none;}
}
@media (max-width: 875px) {
._80tAB {display: none;}
}
</style>
        <div class="rgFsT">
            <div class="card shadows">
                <form class="card-body" onSubmit="return login.send()">
                    <h1 class="display-4 text-center mb-3">Sura</h1>
                    <p class="text-muted text-center mb-5">Чтобы продолжить, создайте аккаунт или войдите.</p>
                    <div id="err2"></div>
                    <div class="form-group">
                        <label>Электронный адрес</label>
                        <input type="email" class="form-control mt-3 mb-3" name="email" id="log_email" placeholder="name@address.com" maxlength="50">
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col"><label>Пароль</label></div>
                            <div class="col-auto">
                                <a href="/restore/" onClick="Page.Go(this.href); return false" class="form-text small text-muted">
                                    Не можете войти?
                                </a>
                            </div>
                        </div>
                        <div class="input-group input-group-merge  mt-3 mb-3">
                            <label for="log_password"></label>
                            <input type="password" class="form-control form-control-appended" name="password" id="log_password" placeholder="Enter your password" maxlength="50">

                        </div>
                    </div>
                    <label>
                        <input type="text" class="d-none" name="log_in">
                        @csrf('_mytoken')
                    </label>
                    <button class="btn btn-lg btn-block btn-primary mb-3" type="submit" onClick="login.send(); return false">Войти</button>
                </form>

            </div>

            <div class="card mt-3 shadows">
                <div class="card-body">
                    У вас ещё нет аккаунта? <a href="/signup/" onclick="Page.Go(this.href); return false;">Зарегистрироваться</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection