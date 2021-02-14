@extends('app.app')
@section('content')
    <div class="container-lg">
        <div class="row">
            <div class="col-4">
                {{ $menu }}
            </div>
            <div class="col-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/settings/">@_e('settings')</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Настройки аккаунта</li>
                    </ol>
                </nav>
                <div class="card mb-2">
                    <div class="card-body">
                        <h2>Настройки аккаунта</h2>
                        <p>Личная информация</p>
                        <a href="/settings/general/" onClick="Page.Go(this.href); return false;"><div><b>@_e('all')</b></div></a>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <a href="/settings/privacy/" onClick="Page.Go(this.href); return false;"><div><b>@_e('settings_privacy')</b></div></a>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <a href="/settings/blacklist/" onClick="Page.Go(this.href); return false;"><div><b>@_e('blacklist')</b></div></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection