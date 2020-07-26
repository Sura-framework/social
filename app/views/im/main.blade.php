@extends('app.app')
@section('content')
    @if(!$inbox)
    <script type="text/javascript">
        $(document).ready(function(){
            var query = $('#msg_query').val();
            if(query == 'Поиск по полученным сообщениям')
                $('#msg_query').css('color', '#c1cad0');
        });
    </script>
    <style>.nav{margin-top:10px}</style>
    <div class="buttonsprofile albumsbuttonsprofile" style="height:10px">
        <div class="activetab"><a href="/messages" onClick="Page.Go(this.href); return false;"><div>Полученные</div></a></div>
        <a href="/messages/outbox/" onClick="Page.Go(this.href); return false;">Отправленные</a>
    </div>
    <div class="clear"></div><div style="margin-top:10px"></div>
    <div class="msg_se_bg"><input type="text" value="{query}" class="fave_input msg_se_inp fl_l" onBlur="if(this.value==''){this.value='Поиск по полученным сообщениям';this.style.color = '#c1cad0';}" onFocus="if(this.value=='Поиск по полученным сообщениям'){this.value='';this.style.color = '#000'}" id="msg_query" maxlength="130" onKeyPress="if(event.keyCode == 13)messages.search();" />
        <div class="button_div fl_l msg_pad_top"><button onClick="messages.search(); return false">Найти</button></div>
        <div class="clear"></div>
    </div>
    <div class="msg_speedbar">{msg-cnt} &nbsp;|&nbsp; <a href="/" style="font-weight:normal" onClick="im.settTypeMsg(); return false" id="settTypeMsg">{msg-type}</a></div>
    @endif
    @if(!$outbox)
    <script type="text/javascript">
        $(document).ready(function(){
            var query = $('#msg_query').val();
            if(query == 'Поиск по отправленным сообщениям')
                $('#msg_query').css('color', '#c1cad0')
        });
    </script>
    <style>.nav{margin-top:10px}</style>
    <div class="buttonsprofile albumsbuttonsprofile" style="height:10px">
        <a href="/messages" onClick="Page.Go(this.href); return false;">Полученные</a>
        <div class="activetab"><a href="/messages/outbox" onClick="Page.Go(this.href); return false;"><div>Отправленные</div></a></div>
    </div>
    <div class="clear"></div><div style="margin-top:10px;"></div>
    <div class="msg_se_bg"><input type="text" value="{query}" class="fave_input msg_se_inp fl_l" onblur="if(this.value==''){this.value='Поиск по отправленным сообщениям';this.style.color = '#c1cad0';}" onfocus="if(this.value=='Поиск по отправленным сообщениям'){this.value='';this.style.color = '#000'}" id="msg_query" maxlength="130" onKeyPress="if(event.keyCode == 13)messages.search(1);" />
        <div class="button_div fl_l msg_pad_top"><button onClick="messages.search(1); return false">Найти</button></div>
        <div class="clear"></div>
    </div>
    <div class="msg_speedbar">{msg-cnt}</div>
    @endif
    @if(!$review)
    <div class="buttonsprofile albumsbuttonsprofile" style="height:10px">
        <a href="/messages" onClick="Page.Go(this.href); return false;">Полученные</a>
        <a href="/messages/outbox" onClick="Page.Go(this.href); return false;">Отправленные</a>
        <div class="activetab"><a href="/" onClick="Page.Go('/messages/show/{mid}'); return false"><div>Просмотр сообщения</div></a></div>
    </div>
    <div class="clear"></div><div class="hralbum msghr"></div>
    @endif
@endsection