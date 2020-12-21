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

        </style>
        <div class="rgFsT">
            <div class="card shadows">
                <form class="card-body" onSubmit="return login.send()">
                    <h1 class="display-4 text-center mb-3">Sura</h1>
                    <p class="text-muted text-center mb-5">@_e('reg_info')</p>
                    <div id="err2"></div>
                    <div class="form-group">
                        <label for="log_email">@_e('email')</label>
                        <input type="email" class="form-control mt-3 mb-3" name="email" id="log_email" placeholder="name@address.com" maxlength="50" value="">
                    </div>
                    <div class="form-group">
                        <label for="log_password">@_e('pass')</label>
                        <div class="input-group input-group-merge  mt-3 mb-3">
                            <input type="password" class="form-control form-control-appended" name="password" id="log_password" placeholder="Enter your password" maxlength="50" value="">
                        </div>
                    </div>
                    <label>
                        <input type="text" class="d-none" name="log_in">
                        @csrf('_mytoken')
                    </label>
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-lg btn-block btn-primary mb-3" type="submit" onClick="login.send(); return false">@_e('log_in')</button>

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