<script type="text/javascript" src="/js/upload.photo.js"></script>
<script type="text/javascript">
var loading_photo_pins = false;
var loaded_pins_name = null;
$(document).ready(function(){
aj1 = new AjaxUpload('upload', {
action: '/bugs/load_img/',
name: 'uploadfile',
data: {
add_act: 'upload'
},
accept: 'image/*',
onSubmit: function (file, ext) {
if(!(ext && /^(jpg|png|jpeg|gif|jpe)$/.test(ext))) {
Box.Info('err', 'Ошибка', 'Неверный формат файла');
return false;
}
$('#upload').hide();
$('#prog_poster').show();
},
onComplete: function (file, row){
var exp = row.split('|');
if(exp[0] == 'size'){
Box.Info('err', 'Ошибка', 'Файл превышает 5 МБ');
} else {
$('#r_poster').attr('src', '/uploads/bugs/'+exp[0]+'/'+exp[1]).show();
}
$('#upload').show();
$('#prog_poster, #size_small, #upload_butt').hide();
loading_photo_pins = true;
loaded_pins_name = exp[1];
}
});
});

</script>
<div id="box_bugs" class="box_pos" style="display: block">
<div class="box_bg" style="width: 500px; margin-top: 30px;">
<div class="box_title">
<span id="btitle" dir="auto">Сообщение о баге</span>
    <div class="box_close" onclick="Box.Close('bugs', 1); return false;"><span aria-hidden="true">×</span></div>
</div>
<div class="box_conetnt">
<div class="bugs_create_bg">
<div class="title">Заголовок</div>
<input type="text" class="inp" id="title">
<div class="title">Описание</div>
<textarea class="inp" id="text"></textarea>
<div class="button_div fl_l"><button onclick="bugs.create();" id="saveShortLink">Отправить</button></div>
<div class="button_div fl_l" id="upload_butt"><button type="submit" class="inp" id="upload">Выбрать файл</button></div><div class="clear"></div><br />
<div id="prog_poster" style="display: none;background:url('/images/progress_grad.gif');width:94px;height:18px;border:1px solid #006699; float:left"></div><div class="clear"></div>
<div id="size_small" style="margin-left:-10px"><small> Файл не должен превышать 5 Mб.</></small></div>
<img src="/uploads/bugs/" id="r_poster" style="display:none;" width="100" height="100" />
<div class="mgclr"></div>
</div>
</div>
<div class="clear"></div>
</div>
</div>
</div>