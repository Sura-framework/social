<script type="text/javascript">
    $(document).ready(function(){
        Xajax = new AjaxUpload('upload', {
            action: '/stories/upload/',
            name: 'uploadfile',
            onSubmit: function (file, ext) {
                if (!(ext && /^(jpg|png|jpeg|gif|jpe)$/.test(ext))) {
                    Box.Info('load_photo_er', lang_dd2f_no, lang_bad_format, 400);
                    return false;
                }
                butloading('upload', '113', 'disabled', '');
            },
            onComplete: function (file, response) {
                if(response == 'bad_format')
                    $('.err_red').show().text(lang_bad_format);
                else if(response == 'big_size')
                    $('.err_red').show().html(lang_bad_size);
                else if(response == 'bad')
                    $('.err_red').show().text(lang_bad_aaa);
                else {
                    Box.Close('photo');
                    $('#ava').html('<img src="'+response+'" alt="" />');
                    $('body, html').animate({scrollTop: 0}, 250);
                    $('#del_pho_but').show();
                }
            }
        });
    });
</script>
<div class="miniature_box">
    <div class="miniature_pos" style="width:230px">
        <div class="load_photo_pad">
            <div class="err_red" style="display:none;font-weight:normal;"></div>
            <div class="d-flex justify-content-center">
                <button type="button" class="btn btn-primary" id="upload" aria-describedby="uploadHelp" style="height: 330px;width: 220px;background: linear-gradient(301deg, #ff49a5, #0d6efd);border: 1px solid white;">
                    <div style="width: 53px;height: 53px;background-color: #00b3ff;margin-left: 51px;margin-bottom: 10px;" class="rounded-circle p-3">
                        <svg width="3em" height="3em" viewBox="0 0 16 16" class="bi bi-images" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12.002 4h-10a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1zm-10-1a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-10z"></path>
                            <path d="M10.648 8.646a.5.5 0 0 1 .577-.093l1.777 1.947V14h-12v-1l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71z"></path>
                            <path fill-rule="evenodd" d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM4 2h10a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1v1a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2h1a1 1 0 0 1 1-1z"></path>
                        </svg>
                    </div>
                    Добавить фото
                </button>
            </div>
        </div>
        <div class="clear" style="margin-top:15px"></div>
        <div class="fl_r"><button class="btn btn-secondary" onClick="viiBox.clos('stories_box', 1)">Отмена</button></div>
        <div class="clear"></div>

    </div>
    <div class="clear" style="height:50px"></div>
</div>