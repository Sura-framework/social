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
 <div class="activetab"><a href="/videos/{user-id}/" onClick="Page.Go(this.href); return false;"><div>[owner]Все видеозаписи[/owner][not-owner]К видеозаписям {name}[/not-owner]</div></a></div>
 [admin-video-add][owner]<a href="/" onClick="videos.add(); return false;">Добавить видеоролик</a>
<!-- <a id="fmv_upload" >Загрузить видеоролик</a> -->
 [admin-video-add][owner]<a href="/" onClick="videos.addbox(); return false;">С компьютера</a>[/owner][/admin-video-add]
  [/owner][/admin-video-add]
 [not-owner]<a href="/u{user-id}" onClick="Page.Go(this.href); return false;">К странице {name}</a>[/not-owner]
</div>
<div class="clear"></div><div style="margin-top:10px;"></div>
<!-- <input type="hidden" id="back" value="1"> -->
<input type="hidden" value="{user-id}" id="user_id" />
<input type="hidden" id="set_last_id" />
<input type="hidden" id="videos_num" value="{videos_num}" />