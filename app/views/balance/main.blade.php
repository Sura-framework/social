@extends('app.app')
@section('content')
    <script type="text/javascript" src="/js/payment.js"></script>
    <div class="container-lg">
        <div class="row">
            <div class="col-4">
                {{ $menu }}
            </div>
            <div class="col-8">
                <div class="margin_top_10"></div><div class="allbar_title">@_e('balance_title')</div>
                <div class="alert alert-info" role="alert">
                    <b>@_e('balance_currency')</b> – @_e('balance_currency_info')
                </div>
                <div class="ubm_descr">
                    <div class="text-center mt-3"><span class="color777">@_e('my_currency'):</span>&nbsp;&nbsp;
                        <b><span id="num2">{{ $ubm }}</span> @_e('currency')</b> @_e('and') <b><span id="rub2">{{ $rub }}</span> {{ $text_rub }}</b></div>
                    <div class="btn-group mt-2" role="group" aria-label="Basic example">
                        <button onClick="doLoad.data(2); payment.box_two();" class="btn btn-success" style="width:161px">@_e('currency_buy')</button>
                        <button onClick="doLoad.data(2); payment.box()" class="btn btn-light" style="width:161px">@_e('currency_replenish')</button>
                    </div>
                </div>
                <div class="mt-3"></div><div class="allbar_title">@_e('code_buy')</div>
                <div class="ubm_descr">
                    <div class="text-center">
                        <div class="err_red no_display" id="err_code" style="font-weight:normal;position:absolute;margin-top:10px;width:400px;"></div><br><br><br>
                        <div class="err_yellow no_display" id="ok_code" style="font-weight:normal;position:absolute;margin-top:10px;width:400px;">@_e('code_activated')</div>
                        <label class="color777" for="code">@_e('code_enter')</label>
                        <input type="text" class="videos_input" id="code" style="width:200px" placeholder="AAAAA-BBBBB-CCCCC"/><br>
                        <div class="button_div fl_l" style="line-height:15px;margin-left:40%"><button id="code" onClick="payment.code();" style="width:161px">@_e('code_activate')</button></div><br>

                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection