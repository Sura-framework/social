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
            ._4_yKc {
                -webkit-box-orient: horizontal;
                -webkit-box-direction: normal;
                -webkit-flex-direction: row;
                -ms-flex-direction: row;
                flex-direction: row;
                -webkit-box-flex: 1;
                -webkit-flex-grow: 1;
                -ms-flex-positive: 1;
                flex-grow: 1;
                -webkit-box-pack: center;
                -webkit-justify-content: center;
                -ms-flex-pack: center;
                justify-content: center;
                margin: 32px auto 0;
                max-width: 935px;
                padding-bottom: 32px;
                width: 100%;
            }
            .yOZjD {
                -webkit-align-self: center;
                -ms-flex-item-align: center;
                align-self: center;
                background-position: 0 0;
                background-size: 454px 618px;
                -webkit-flex-basis: 454px;
                -ms-flex-preferred-size: 454px;
                flex-basis: 454px;
                height: 618px;
                margin-left: -35px;
                margin-right: -15px;

                background-image: url(/images/phones.png);
            }
            .rgFsT {
                color: #262626;
                color: rgba(var(--i1d,38,38,38),1);
                -webkit-box-flex: 1;
                -webkit-flex-grow: 1;
                -ms-flex-positive: 1;
                flex-grow: 1;
                -webkit-box-pack: center;
                -webkit-justify-content: center;
                -ms-flex-pack: center;
                justify-content: center;
                margin-top: 12px;
                max-width: 350px;
            }
            .RP4i1 {
                height: 427px;
                left: 0;
                opacity: 1;
                position: absolute;
                top: 0;
                width: 240px;
            }
            .V64Sp {
                margin: 99px 0 0 151px;
                position: relative;
            }

        </style>
        <div class="rgFsT">
            <div class="card shadows">
                <form class="card-body" action="/login/" method="post">
                    <h1 class="display-4 text-center mb-3">Sura</h1>
                    <p class="text-muted text-center mb-5">@_e('reg_info')</p>
                    <div id="err2"></div>
                    <div class="form-group">
                        <label for="log_email">@_e('email')</label>
                        <input type="email" class="form-control mt-3 mb-3" name="email" id="log_email" placeholder="name@address.com">
                    </div>
                    <div class="form-group">
                        <label for="log_password">@_e('pass')</label>
                        <div class="input-group input-group-merge  mt-3 mb-3">
                            <input type="password" class="form-control form-control-appended" name="password" id="log_password" placeholder="Enter your password">
                        </div>
                    </div>
                    <label>
                        <input type="text" class="d-none" name="log_in">
                        <input type="text" class="d-none" name="login">
                        @csrf('_mytoken')
                    </label>
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-lg btn-block btn-primary mb-3" type="submit" >@_e('log_in')</button>

                        </div>
                        <div class="col">
                            <a href="/restore/" onClick="Page.Go(this.href); return false" class="form-text small text-muted">
                                @_e('not_pass')
                            </a>
                        </div>
                    </div>

                </form>

            </div>

            <div class="card mt-3 shadows">
                <div class="card-body">@_e('not_auth')
                    <a href="/signup/" onclick="Page.Go(this.href); return false;">@_e('sign_up')</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection