@extends('app.app')
@section('content')
    <script type="text/javascript">ge('new_support').innerHTML = '';</script>
    <div class="container-lg">
        <div class="row">
            <div class="col-4">
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/support/" onClick="Page.Go(this.href); return false;" class="navbar-brand mb-0 h1">[group=4]Вопросы от пользователей[/group][not-group=4]Мои вопросы[/not-group]</a>
                    </div>
                </nav>[not-group=4]
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/support/new/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Задать вопрос</a>
                    </div>
                </nav>[/not-group]
            </div>
            <div class="col-8">
                <div class="margin_top_10"></div><div class="allbar_title" style="border-bottom:0px;margin-bottom:0px">{cnt}</div>

                {content_info}

                <div class="support_questtitle">
                    <div class="support_title_inpad fl_l">
                        <a href="/support/show/{qid}/" onClick="Page.Go(this.href); return false"><b>{title}</b></a><br />
                        {status}
                    </div>
                    <a href="/support/show/{qid}/" onClick="Page.Go(this.href); return false" class="support_last_answer fl_r" style="font-size:11px">
                        <img src="{ava}" alt="" width="35" />
                        {name}<br />
                        <span class="color777">{answer} {date}</span>
                    </a>
                    <div class="clear"></div>
                </div>


            </div>
        </div>
    </div>

    </div>

@endsection