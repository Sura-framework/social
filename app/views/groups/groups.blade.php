@extends('app.app')
@section('content')
    <div class="">
        <div class="row">
            <div class="col-4">
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/groups/" onClick="Page.Go(this.href); return false;" class="navbar-brand mb-0 h1">Сообщества</a>
                    </div>
                </nav>
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/groups/admin/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Управление сообществами</a>
                    </div>
                </nav>
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/groups/" onClick="groups.createbox(); return false" class="navbar-brand">Создать сообщество</a>
                    </div>
                </nav>
            </div>
            <div class="col-8">
                <div class="container">
                    <div class="margin_top_10"></div>
                    @if($groups_yes)
                    <div class="allbar_title" style="margin-bottom:0px;border-bottom:0px">
                    Вы состоите в {{ $num }}</div>
                    @else
                    <div class="allbar_title">Вы не состоите ни в одном сообществе.</div>
                    <div class="info_center"><br /><br />
                    Вы пока не состоите ни в одном сообществе.
                    <br /><br />
                    Вы можете <button onClick="groups.createbox(); return false">создать сообщество</button> или воспользоваться <a href="/" onClick="gSearch.open_tab(); gSearch.select_type('4', 'по сообществам'); return false" id="se_link">поиском по сообществам</a>.<br /><br /><br />
                    </div>
                    @endif

                    @if($groups)
                        @foreach($groups as $item)
                            <div class="friends_onefriend width_100" style="border-top:1px solid #e0eaef">
                                <a href="/{{ $item['adres'] }}" onClick="Page.Go(this.href); return false">
                                    <div class="friends_ava">
                                        <img src="{{ $item['photo'] }}" /></div></a>
                                <div class="fl_l" style="width:500px">
                                    <a href="/{{ $item['adres'] }}" onClick="Page.Go(this.href); return false"><b>{{ $item['name'] }}</b></a><div class="friends_clr"></div>
                                    <span class="color777">{{ $item['traf'] }}</span><div class="friends_clr"></div>
                                </div>
                                <div class="menuleft fl_r friends_m">
                                    @if($item['admin'])
                                        <div id="exitlink{{ $item['id'] }}"><a href="/groups/" onClick="groups.exit('{{ $item['id'] }}'); return false"><div>Выйти из сообщества</div></a></div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
            </div>
        </div>
    </div>
    <div>

    </div>

@endsection