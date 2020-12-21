@extends('app.app')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-6 col-md-10 m-auto">
            <div class="card rounded-lg shadow-sm mt-5">
                <div class="card-header px-lg-7 px-4 pt-3 pb-0 border-0 bg-transparent">
                    <div class="row d-flex justify-content-center">
                        <div class="col pt-4 mb-2">
                            <h5>@_e('restore')
                            </h5>
                        </div>
                    </div>
                </div>
                <div id="step1">
                    <div class="card-block px-lg-7 px-4 pb-6">
                        <p class="lead text-muted mb-4 mr-3">@_e('restore_info')</p>
                        <div class="mb-3">
                            <div class="input-group mb-5">
                                <input class="form-control rounded mx-0 mr-2" type="text" id="email" placeholder="Введите адрес электронной почты" aria-label="Эл. адрес">
                                <button class="btn btn-primary" type="button" onClick="restore.next(); return false" id="send">@_e('restore_send')</button>
                            </div>
                            <a class="text-muted" href="/" onclick="Page.Go(this.href); return false;">
                                <svg width="2em" height="2em" viewBox="0 0 16 16" class="bi bi-arrow-bar-left" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M5.854 4.646a.5.5 0 0 0-.708 0l-3 3a.5.5 0 0 0 0 .708l3 3a.5.5 0 0 0 .708-.708L3.207 8l2.647-2.646a.5.5 0 0 0 0-.708z"/>
                                    <path fill-rule="evenodd" d="M10 8a.5.5 0 0 0-.5-.5H3a.5.5 0 0 0 0 1h6.5A.5.5 0 0 0 10 8zm2.5 6a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 1 0v11a.5.5 0 0 1-.5.5z"/>
                                </svg>@_e('restore_to_main')
                            </a>
                        </div>
                    </div>
                </div>
                <div class="no_display" id="step2">
                    <div class="card-block px-lg-7 px-4 pb-6">
                        <div class="lead text-muted mb-4 mr-3">@_e('whose_page')</div>
                        <div class="mb-3">
                            <img src="" alt="" class="text-left mt-2 mr-1" id="c_src" />
                            <div style="margin-top:11px;font-size:13px;color:#21578b" id="c_name"></div>
                            <button class="btn btn-primary" type="button" onClick="restore.send(); return false" id="send2">
                                @_e('yes_page')
                            </button>
                        </div>
                    </div>
                </div>
                <div class="no_display"  id="step3">
                    <div class="card-block px-lg-7 px-4 pb-6">
                        <div class="lead text-muted mb-4 mr-3">@_e('restore_to_email')</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection