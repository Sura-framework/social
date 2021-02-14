<script type="text/javascript">
    $(document).ready(function(){
        Xajax = new AjaxUpload('upload', {
            action: '/attach/',
            name: 'uploadfile',
            onSubmit: function (file, ext) {
                if (!(ext && /^(jpg|png|jpeg|gif|jpe)$/.test(ext))) {
                    addAllErr(lang_bad_format, 3300);
                    return false;
                }
                Page.Loading('start');
            },
            onComplete: function (file, response){
                // const d = JSON.parse(response);
                const d = response;


                if (d.status === 1){
                    // let res = d.res
                    // var response = response.split('|||');
                    // var imgname = response[1].split('/');
                    var imgname = d.res.img.split('/');
                    wall.attach_insert('photo', d.res.url, 'attach|'+imgname[6].replace('c_', ''), d.res.user);
                    Page.Loading('stop')
                }else if(d.status === 2 || d.status === 3){
                    addAllErr(lang_max_size, 3300);
                    Page.Loading('stop');
                }else if(d.status === 4){
                    addAllErr('Неизвестный формат', 3300);
                    Page.Loading('stop');
                } else {

                }
            }
        });
    });
</script>
<div class="cover_edit_title">
    <div class="fl_l margin_top_5">Всего {{ $photo_num }}</div>
    <div class="button_div_gray fl_r"><button id="upload">Загрузить новую фотографию</button></div>
    <div class="clear"></div>
</div>
<div class="clear"></div>


{{--<div class="cover_minm_po cursor_pointer"><img src="{photo}" alt="" onClick="wall.attach_insert('photo', this.src, '{photo-name}|{aid}')" /></div>--}}