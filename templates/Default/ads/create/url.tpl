<div class="mgclr"></div>
<div class="margin_top_10"></div><div class="allbar_title">Оформление</div>
<div class="previewad fl_r" id="prewad">
<div id="previewimg" class="previewadimg_1"><img id="previewimg1" style="width:145px;height:85px;"></div>
<div class="previewad_name" id="exname">Реклама</div>
<div class="previewad_descr" id="exdescr">Образец описания</div>
</div><div class="mgclr"></div>
<div class="texta">Формат объявления:</div>
<ul class="radio-group" onclick="ads.changeform('1');" style="width:500px"><li><input type="radio" checked name="format" id="format1"><label for="format1"><span><span></span></span>Изображение и текст</label></li></ul>
<div class="mgclr"></div><div class="texta"></div>
<ul class="radio-group" onclick="ads.changeform('2');" style="width:500px"><li><input type="radio" name="format" id="format2"><label for="format2"><span><span></span></span>Большое изображение</label></li></ul>
<div class="mgclr"></div>
<div class="texta">Заголовок:</div><input type="text" id="name" class="inpst" maxlength="33" style="width:300px;" onkeyup="ads.updtitles('name');"/><div class="mgclr"></div>
<div id="descr"><div class="texta">Описание:</div><textarea type="text" id="description" class="inpst" maxlength="70" style="width:300px;height:100px;" onkeyup="ads.updtitles('descr');"/></div><div class="mgclr"></div>
<div class="texta"></div><div class="button_div fl_l"><button onClick="ads.upload_img(); return false" id="uploadimg">Загрузить изображение</button></div><div class="mgclr"></div>
<input id="format" type="hidden" value="1">
<div class="margin_top_10"></div><div class="allbar_title">Настройка целевой аудитории</div><div class="mgclr"></div>
<div class="texta">Страна:</div><div id="container1" class="selector_container dropdown_container selector_focused fl_l editpr_fieldlist editpr_fieldlist" style="width: 178px;">
		<table cellspacing="0" cellpadding="0" class="selector_table">
			<tbody>
				<tr>
					<td class="selector">
						<span class="selected_items"></span>
							<input type="text" class="selector_input selected" readonly="true"  value="{country_name}" style="color: rgb(0, 0, 0); width: 115px; " id="container1" >
							<input type="hidden" onChange="Profile.LoadCity(this.value);" name="country" id="country" value="{country_id}" class="resultField" >
						
					</td>
					<td id="container1" class="selector_dropdown" style="width: 18px; ">&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<div class="results_container results_container1" style="display:none">
		<div class="result_list result_list1" style="opacity: 1; width: 178px; height: 218px; bottom: auto; visibility: visible;overflow-x: hidden; overflow-y: visible;"><ul value ="{country_id}" id="resultField1">{country}</ul></div>
		<div class="result_list_shadow" style="width: 140px; margin-top: 217px; " ><div class="shadow1"></div><div class="shadow2"></div></div></div></div><div class="mgclr"></div>
<div class="texta">Пол:</div><div id="container2" class="selector_container dropdown_container fl_l selector_focused editpr_fieldlist">
		<table cellspacing="0" cellpadding="0" class="selector_table">
			<tbody>
				<tr>
					<td class="selector">
						<span class="selected_items"></span>
							<input type="text" class="selector_input selected" readonly="true"  value="- Не выбрано -" style="color: rgb(0, 0, 0); width: 122px; " id="container2" >
							<input type="hidden"  name="sex" id="sex" value="0" class="resultField" >
						
					</td>
					<td id="container2" class="selector_dropdown" style="width: 25px; ">&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<div class="results_container" style="display:none">
		<div class="result_list" style="opacity: 1; width: 165px; height: 218px; bottom: auto; visibility: visible;overflow-x: hidden; overflow-y: visible;"><ul>{sex}</ul></div>
		<div class="result_list_shadow" style="width: 165px; margin-top: 217px; " ><div class="shadow1"></div><div class="shadow2"></div></div></div></div><div class="mgclr"></div>
<div class="texta">Возраст:</div><div id="container3" class="selector_container dropdown_container fl_l selector_focused editpr_fieldlist">
		<table cellspacing="0" cellpadding="0" class="selector_table">
			<tbody>
				<tr>
					<td class="selector">
						<span class="selected_items"></span>
							<input type="text" class="selector_input selected" readonly="true"  value="Любой" style="color: rgb(0, 0, 0); width: 122px; " id="container3" >
							<input type="hidden"  name="agefrom" id="agefrom" value="0" class="resultField" >
						
					</td>
					<td id="container3" class="selector_dropdown" style="width: 25px; ">&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<div class="results_container" style="display:none">
		<div class="result_list" style="opacity: 1; width: 165px; height: 161px; bottom: auto; visibility: visible;overflow-x: hidden; overflow-y: visible;"><ul onmousedown="ads.loadage();">{age}</ul></div>
		<div class="result_list_shadow" style="width: 165px; margin-top: 161px; " ><div class="shadow1"></div><div class="shadow2"></div></div></div></div>
		<div id="agel"></div>
		<div class="mgclr"></div>
<div class="texta">Семейное положение:</div><div id="container6" class="selector_container dropdown_container fl_l selector_focused editpr_fieldlist">
		<table cellspacing="0" cellpadding="0" class="selector_table">
			<tbody>
				<tr>
					<td class="selector">
						<span class="selected_items"></span>
							<input type="text" class="selector_input selected" readonly="true"  value="- Не выбрано -" style="color: rgb(0, 0, 0); width: 122px; " id="container6" >
							<input type="hidden"  name="sp" id="sp" value="0" class="resultField" >
						
					</td>
					<td id="container6" class="selector_dropdown" style="width: 25px; ">&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<div class="results_container" style="display:none">
		<div class="result_list" style="opacity: 1; width: 165px; height: 161px; bottom: auto; visibility: visible;overflow-x: hidden; overflow-y: visible;"><ul>
<li onmousemove="Select.itemMouseMove(6, 0)"  val="0" class="">- Не выбрано -</li>
<li onmousemove="Select.itemMouseMove(6, 1)"  val="1" class="">Не женат</li>
<li onmousemove="Select.itemMouseMove(6, 2)"  val="2" class="">Есть подруга</li>
<li onmousemove="Select.itemMouseMove(6, 3)"  val="3" class="">Помовлен</li>
<li onmousemove="Select.itemMouseMove(6, 4)"  val="4" class="">Женат</li>
<li onmousemove="Select.itemMouseMove(6, 5)"  val="5" class="">Влюблён</li>
<li onmousemove="Select.itemMouseMove(6, 6)"  val="6" class="">Всё сложно</li>
<li onmousemove="Select.itemMouseMove(6, 7)"  val="7" class="">В активном поиске</li>
</ul></div>
		<div class="result_list_shadow" style="width: 165px; margin-top: 161px; " ><div class="shadow1"></div><div class="shadow2"></div></div></div></div><div class="mgclr"></div>
<div class="margin_top_10"></div><div class="allbar_title">Настройка цены и расположения</div><div class="mgclr"></div>
<div class="texta">Способ оплаты:</div><ul class="radio-group" onclick="ads.changepay('1');" style="width:500px"><li><input type="radio" checked name="pay" id="pay1"><label for="pay1"><span><span></span></span>Оплата за переходы</label></li></ul><div class="mgclr"></div><div class="texta"></div><ul class="radio-group" onclick="ads.changepay('2');" style="width:500px"><li><input type="radio" name="pay" id="pay2"><label for="pay2"><span><span></span></span>Оплата за показы</label></li></ul><div class="mgclr"></div>
<div id="typepay">
<div class="texta">Количество переходов:</div><input type="text" id="price" class="inpst" style="width:50px;" value="1" onkeyup="ads.updatepay();"/><div class="mgclr"></div>
</div>
<div class="texta">К оплате:</div><input type="text" id="fprice" class="inpst" style="width:50px;" value="5" disabled /> руб.<div class="mgclr"></div>
<input id="pay" type="hidden" value="1">
<div class="margin_top_10"></div><div class="allbar_title"></div><div class="mgclr"></div>
<div class="texta"></div><div class="button_div fl_l"><button onClick="ads.create('url'); return false">Создать объявление</button></div><div class="mgclr"></div>