@extends('app.app')
@section('content')
<div class="container">
    <script type="text/javascript">
        $(document).ready(function(){
            videos.scroll();
            ajaxUpload = new AjaxUpload('fmv_upload', {
                action: '/videos/upload/',
                name: 'uploadfile',
                onSubmit: function (file, ext) {
                    if(!(ext && /^(mp4)$/.test(ext))) {
                        addAllErr('Формат не поддерживается!', 3300);
                        return false;
                    }
                },
                onComplete: function (file, row){
                    console.log(row);
                    if(row == 'big_file') addAllErr('Максимальны размер 500 МБ.', 5300);
                    else if(row == 'bad_format') addAllErr('Неизвестный формат видео.');
                    else if(row == 'not_upload') addAllErr('Ошибка записи.');
                    else if(row == 'not_uploaded') addAllErr('Файл не найден.');
                    else {
                        window.location.reload();
                    }
                }
            });
        });
    </script>
    <div class="buttonsprofile albumsbuttonsprofile" style="height:10px;">
        <div class="activetab">
            <a href="/videos/{{ $user_id }}/" onClick="Page.Go(this.href); return false;"><div>
        @if($owner)Все видеозаписи @endif @if($not_owner)К видеозаписям {name}@endif</div></a></div>
        @if($admin_video_add) @if($owner)<a href="/" onClick="videos.add(); return false;">Добавить видеоролик</a>
        <!-- <a id="fmv_upload" >Загрузить видеоролик</a> -->
        @if($admin_video_add) @if($owner)<a href="/" onClick="videos.addbox(); return false;">С компьютера</a>@endif @endif
        @endif @endif
        @if($not_owner)<a href="/u{{ $user_id }}" onClick="Page.Go(this.href); return false;">К странице {name}</a>@endif
    </div>
    <div class="clear"></div><div style="margin-top:10px;"></div>
    <!-- <input type="hidden" id="back" value="1"> -->
    <input type="hidden" value="{{ $user_id }}" id="user_id" />
    <input type="hidden" id="set_last_id" />
    <input type="hidden" id="videos_num" value="{videos_num}" />
    <span id="video_page" class="scroll_page">
        @foreach($videos as $row)
            <div class="onevideo" id="video_{id}">
                 <a href="/video/{user-id}/{id}/" onClick="videos.show({id}, this.href); return false"><div class="onevideo_img"><img style="width: 175px;" src="{photo}" alt="" /></div></a>
                 <div class="onevideo_title"><a href="/video{{ $user_id }}_{id}" id="video_title_{id}" onClick="videos.show({id}, this.href); return false">{title}</a></div>
                 <div class="onevideo_inf2" id="video_descr_{id}">{descr}</div>
                 <div class="onevideo_inf">{comm}</div>
                 <div class="onevideo_inf">Добавлено {date}</div>
                 [owner]<div class="onevideo_inf"><a href="/" onClick="videos.editbox({id}); return false">Редактировать</a> &nbsp;|&nbsp; <a href="/" onClick="videos.delet({id}); return false">Удалить</a></div>[/owner]
                <input type="hidden" value="{id}" id="onevideo" />
                </div>
            <div class="clear"></div>
        @endforeach
    </span>
</div>
@endsection