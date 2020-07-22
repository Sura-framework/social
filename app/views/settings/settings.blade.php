@extends('app.app')
@section('content')
    <div class="container-lg">
        <div class="row">
            <div class="col-4">
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/settings/general/" onClick="Page.Go(this.href); return false;" class="navbar-brand mb-0 h1 ">Настройки</a>
                    </div>
                </nav>
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/settings/privacy/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Приватность</a>
                    </div>
                </nav>
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/settings/blacklist/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Черный список</a>
                    </div>
                </nav>
                <hr>
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/settings/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Уведомления</a>
                    </div>
                </nav>
                <hr>
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/balance/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Баланс</a>
                    </div>
                </nav>
            </div>
            <div class="col-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active" aria-current="page">Настройки</li>
                    </ol>
                </nav>

                <div class="card mb-2">
                    <div class="card-body">
                        <h2>Настройки аккаунта</h2>
                        <p>Личная информация</p>
                        <a href="/settings/general/" onClick="Page.Go(this.href); return false;"><div><b>Общее</b></div></a>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <a href="/settings/privacy/" onClick="Page.Go(this.href); return false;"><div><b>Приватность</b></div></a>
                    </div>
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <a href="/settings/blacklist/" onClick="Page.Go(this.href); return false;"><div><b>Черный список</b></div></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection