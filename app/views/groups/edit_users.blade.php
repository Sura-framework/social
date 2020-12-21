@extends('app.app')
@section('content')
<script type="text/javascript">
    $(document).ready(function(){
        $(window).scroll(function(){
            if($(document).height() - $(window).height() <= $(window).scrollTop()+($(document).height()/2-250) || $(document).height()/2 <= $(window).scrollTop()){
                epage.users_page('{{ $id }}');
            }
        });
    });
</script>
<div class="container-lg">
    <div class="row">
        <div class="col-4">
            {{ $menu }}
        </div>
        <div class="col-8">
            <div class="container">
                <input id="page_cnt" value="1" type="hidden"/>
                <input id="count" value="{count}" type="hidden"/>
                <input id="type" value="{type}" type="hidden"/>
                <input id="pid" value="{pid}" type="hidden"/>

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/public{{ $id }}">{{ $id }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Настройки</li>
                    </ol>
                </nav>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div ><a href="/{{ $adres }}" onClick="Page.Go(this.href); return false;" style="float:right;">Вернутся к сообществу</a></div>
                        </div>
                        <div class="row">
                            <div class="{{--search_form_tab--}}" style="margin-top:1px">
                                @if($admin_page)
                                    <input type="text" value="Введите имя и фамилию пользователя для более точного поиска" class="fave_input"
                                           onblur="if(this.value=='') this.value='Введите имя и фамилию пользователя для более точного поиска';this.style.color = '#777';"
                                           onfocus="if(this.value=='Введите имя и фамилию пользователя для более точного поиска') this.value='';this.style.color = '#000'"
                                           id="filter" style="margin: -1px 0px 10px 0px;width: 100% !important;">
                                    <div class="button_blue fl_r"><button style="width:170px">
                                            Назначить руководителем</button>
                                    </div>
                                @else
                                    <input type="text" value="Введите имя и фамилию пользователя для более точного поиска" class="fave_input"
                                           onblur="if(this.value=='') this.value='Введите имя и фамилию пользователя для более точного поиска';this.style.color = '#777';"
                                           onfocus="if(this.value=='Введите имя и фамилию пользователя для более точного поиска') this.value='';this.style.color = '#000'"
                                           id="filter" style="margin: -1px 0px 10px 0px;width: 488px !important;">
                                    <div class="button_blue fl_r"><button style="width:90px">
                                            Поиск</button>
                                    </div>
                                @endif

                                <div class="buttonsprofile albumsbuttonsprofile buttonsprofileSecond" style="height:32px;margin-bottom: -10px;">
                                    <div class="{{ $button_tab_a }}"><a href="/public/edit/users/{{ $id }}" onclick="Page.Go(this.href); return false;"><div><b>Все участники</b></div></a></div>
                                    <div class="{{ $button_tab_b }}"><a href="/public/edit/users/{{ $id }}/admin" onclick="Page.Go(this.href); return false;"><div><b>Руководители</b></div></a></div>
                                </div>
                            </div>
                        </div>

                        <div id="gedit_users_summaryw_members" class="summary_wrap" style="">
                            <div id="gedit_users_summary_members" class="summary">{{ $titles }}</div>
                        </div>

                        <div class="row">
                            <table><tbody><tr><td id="all_users">
                                        @if($users)
                                            @foreach($users as $key => $row)
                                                <div id="gedit_user_members" class="gedit_user">
                                                    <div class="gedit_user_bigph_wrap fl_l">
                                                        <a class="gedit_bigph" onClick="Photo.Profile('{{ $row['uid'] }}', '{{ $ava }}'); return false"><span class="gedit_bigph_label" style="cursor:pointer">Увеличить</span></a>
                                                        <a class="gedit_user_thumb" href="/{{ $row['adres'] }}"><img class="gedit_user_img" src="{{ $row['ava_photo'] }}" alt="{{ $row['adres'] }}"></a>
                                                    </div>
                                                    <div class="gedit_user_info fl_l">
                                                        <div class="gedit_user_name"><a class="gedit_user_lnk" href="/{{ $row['adres'] }}" id="gedit_user_name{{ $row['uid']}}">{{ $row['name']}}</a></div>
                                                        <div class="gedit_user_level {{ $row['view_tags'] }}" id="gedit_user_level{{ $row['uid'] }}">{{ $row['tags'] }}</div>
                                                        @if($online)<div class="gedit_user_online">Online</div>@endif
                                                        <div class="gedit_user_btns"></div>
                                                    </div>
                                                    @if($row['yes_admin'])
                                                    <div class="gedit_user_actions fl_r">
                                                        <span id="gedit_users{{ $row['uid'] }}">
                                                        <a class="gedit_user_action" onClick="epage.editadmin('{{ $row['uid'] }}','editadmin'); return false" style="cursor:pointer">Редактировать</a>
                                                        <a class="gedit_user_action" onClick="epage.editadmin('{{ $row['uid'] }}','deleteadmin'); return false" style="cursor:pointer">Разжаловать руководителя</a>
                                                        <a class="gedit_user_action" onClick="epage.deleteusers('{{ $row['uid'] }}'); return false" id="gedit_user_action{{ $row['uid'] }}" style="cursor:pointer">Удалить из сообщества</a>
                                                        </span>
                                                        <a class="gedit_user_action" onClick="epage.rebornusers('{{ $row['uid'] }}'); return false" id="gedit_user_actions{{ $row['uid'] }}" style="display:none" style="cursor:pointer">Восстановить</a>
                                                    </div>
                                                    @else

                                                    <div class="gedit_user_actions fl_r">
                                                        <span id="gedit_users{uid}">
                                                        <a class="gedit_user_action" onClick="epage.editadmin('{{ $row['uid'] }}','newadmin'); return false" style="cursor:pointer">Назначить руководителем</a>
                                                        <a class="gedit_user_action" onClick="epage.deleteusers('{{ $row['uid'] }}'); return false" id="gedit_user_action{{ $row['uid'] }}" style="cursor:pointer">Удалить из сообщества</a>
                                                        </span>
                                                        <a class="gedit_user_action" onClick="epage.rebornusers('{{ $row['uid'] }}'); return false" id="gedit_user_actions{{ $row['uid'] }}" style="display:none" style="cursor:pointer">Восстановить</a>
                                                    </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </td></tr></tbody></table>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection