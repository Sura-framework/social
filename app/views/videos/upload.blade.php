<script type="text/javascript">
    ajaxUpload = new AjaxUpload('fmv_upload', {
        action: '/videos/upload/',
        name: 'uploadfile',
        onSubmit: function (file, ext) {
            if(!(ext && /^(mp4|mpeg|avi|ogg|flv)$/.test(ext))) {
                addAllErr('Формат не поддерживается!', 3300);
                return false;
            }
            $('#fmv_loading').show();
            $('#fmv_info').hide();
            $('#fmv_input_none').hide();
        },
        onComplete: function (file, row){
            if(row == 'big_file') addAllErr('Максимальны размер 500 МБ.', 5300);
            else if(row == 'bad_format') addAllErr('Неизвестный формат видео.');
            else if(row == 'not_upload') addAllErr('Ошибка записи.');
            else if(row == 'not_uploaded') addAllErr('Файл не найден.');
            else {
                $('.fmv_hidden').show();
                $('#fmv_upload').hide();
                $('#fmv_loading').hide();
                $('#fmv_info').show();
                $('#fmv_input_none').show();
                $('#video_upload_id').val(row);
                setTimeout("$('.fmvideos_input_1').css('background', '#F3EFED').focus()", 1000);
                setTimeout("$('.fmvideos_input_1').css('background', '#FFFFFF').focus()", 2000);
                setTimeout("$('.fmvideos_input_2').css('background', '#F3EFED').focus()", 3000);
                setTimeout("$('.fmvideos_input_2').css('background', '#FFFFFF').focus()", 4000);
                setTimeout("$('#fmv_info').css('background', '#F3EFED').focus()", 5000);
            }
        }
    });
</script>
<div class="miniature_box">
    <div class="miniature_pos" style="width:500px">
        <h2 class="miniature_title fl_l apps_box_text">Загрузка видео материалов Beta</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onmouseover="myhtml.title('1', 'Закрыть', 'box_upload_')" onclick="viiBox.clos('fmv_mouse', 1)" id="box_upload_1">
            <span aria-hidden="true">&times;</span>
        </button>

        <div class="clear"></div>
        <div class="alert alert-info" role="alert">
            При добавлении видео следует придерживаться конкретных требований данного видео сервиса. Желательно все
            характеристики загружаемого видео привести к наилучшему его просмотру
            в высоком разрешении 1080р («р» – прогрессивная развертка).
            <br />Поддерживаемй формат mp4.<br />Допустимый размер 500 мб.
        </div>
        <button type="button" id="fmv_upload" class="btn btn-secondary float-right">Загрузить видео</button>
        <div class="fmv_cle">
            <div class="clear"></div>
        </div>
        <div id="fmv_input_none" style="display:none;">
            <div class="videos_text">Название</div>
            <input type="text" class="fmvideos_input_1" id="title" name="title" maxlength="65" />
            <input type="text" class="fmvideos_input_1" id="video_upload_id" name="title" class="d_none" />
            <div class="videos_text">Описание</div>
            <textarea class="fmvideos_input_2" id="descr" name="descr" style="height:70px"></textarea>
        </div>
        <div id="fmv_loading" style="display:none;">...Загрузка<!-- <img src="/images/loading_file.gif" /> --></div>
        <div id="fmv_info" onClick="videos.reload_list()" style="display:none;">Видео успешно загружено! <a class="fmv_hidden">Сохранить данное видео.</a></div>
        <div class="clear"></div>
    </div>
</div>