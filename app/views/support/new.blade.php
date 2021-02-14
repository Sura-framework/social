@extends('app.app')
@section('content')
<div class="container-lg">
    <div class="row">
        <div class="col-4">
            <nav class="navbar navbar-light">
                <div class="container-fluid">
                    <a href="/support/" onClick="Page.Go(this.href); return false;" class="navbar-brand">{{ $support_title }}</a>
                </div>
            </nav>                @if($group > 4)
                <nav class="navbar navbar-light">
                    <div class="container-fluid">
                        <a href="/support/new/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Задать вопрос</a>
                    </div>
                </nav>@endif
        </div>
        <div class="col-8">
            <div id="data">
                @if($group > 4)
                <div style="margin-top:29px"></div>
                <div class="note_add_bg support_bg">
                    Здесь Вы можете сообщить нам о любой проблеме, связанной с <b>нашим сайтом</b>.
                    <input type="text"
                           class="videos_input"
                           style="width:580px;margin-top:10px;color:#c1cad0"
                           maxlength="65"
                           id="title"
                           value="Пожалуйста, добавьте заголовок к Вашему вопросу.."
                           onblur="if(this.value==''){this.value='Пожалуйста, добавьте заголовок к Вашему вопросу..';this.style.color = '#c1cad0';}"
                           onfocus="if(this.value=='Пожалуйста, добавьте заголовок к Вашему вопросу..'){this.value='';this.style.color = '#000'}"
                    />
                    <div class="input_hr" style="width:593px"></div>
                    <textarea
                            class="videos_input wysiwyg_inpt"
                            id="question"
                            style="width:580px;height:100px;color:#c1cad0"
                            onblur="if(this.value==''){this.value='Пожалуйста, расскажите о Вашей проблеме чуть подробнее..';this.style.color = '#c1cad0';}"
                            onfocus="if(this.value=='Пожалуйста, расскажите о Вашей проблеме чуть подробнее..'){this.value='';this.style.color = '#000'}"
                    >Пожалуйста, расскажите о Вашей проблеме чуть подробнее..</textarea>
                    <div class="clear"></div>

                    <div class="button_div fl_l">
                        <button onClick="support.send(); return false" id="send">Отправить</button>
                    </div>
                    <div class="button_div_gray fl_l margin_left" id="cancel">
                        <button onClick="Page.Go('/u{uid}'); return false;">Отмена</button>
                    </div>
                    <div class="clear"></div>
                </div>
                @else
                <div class="info_center">Вы должны всё знать.</div>
                    @endif
            </div>
        </div>
    </div>
</div>
@endsection