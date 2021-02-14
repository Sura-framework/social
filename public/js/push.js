function upClose(xnid){
	$('#event'+xnid).remove();
	$('#updates').css('height', $('.update_box').size()*123+'px');
}
function GoPage(event, p){
	var oi = (event.target) ? event.target.id: ((event.srcElement) ? event.srcElement.id : null);
	if(oi === 'no_ev' || oi === 'update_close' || oi === 'update_close2') return false;
	else {
		pattern = new RegExp(/photo[0-9]/i);
		pattern2 = new RegExp(/video[0-9]/i);
		if(pattern.test(p))
			Photo.Show(p);
		else if(pattern2.test(p)){
			vid = p.replace('/video', '');
			vid = vid.split('_');
			videos.show(vid[1], p, location.href);
		} else
			Page.Go(p);
	}
}
$(document).ready(function(){
	setInterval(function(){

        $.post("/updates/",{ // M.post or M.get
            // query:{}, // Значение запроса
            onDone:function(d){ // Если всё нормально, то отобразить результат.
                // M._ge_by_id("result").innerHTML = a;
                console.log(d)
                if (d &&d.status && d.status == '1') {
                    let row = d.res;
                    if (row.type) {
                        let uTitle = '';
                        if (row.type === 1) uTitle = 'Новый ответ на стене';
                        else if (row.type === 2) uTitle = 'Новый комментарий к фотографии';
                        else if (row.type === 3) uTitle = 'Новый комментарий к видеозаписи';
                        else if (row.type === 4) uTitle = 'Новый комментарий к заметке';
                        else if (row.type === 5) uTitle = 'Новый ответ на Ваш комментарий';
                        else if (row.type === 6) uTitle = 'Новый ответ в теме';
                        else if (row.type === 7) uTitle = 'Новый подарок';
                        else if (row.type === 8) uTitle = 'Новое сообщение';
                        else if (row.type === 9) uTitle = 'Новая оценка';
                        else if (row.type === 10) uTitle = 'Ваша запись понравилась';
                        else if (row.type === 11) uTitle = 'Новая заявка';
                        else if (row.type === 12) uTitle = 'Заявка принята';
                        else if (row.type === 13) uTitle = 'Подписки';
                        else if (row.type === 14) uTitle = 'Уведомление';
                        else uTitle = 'Событие';
                        //Новое сообщение
                        if (row.type === 8) {
                            sli = row.link.split('/');
                            let tURL = (location.href).replace('http://' + location.host, '').replace('/', '').split('#');
                            if (!sli[2] && tURL[0] === 'messages')
                                return false;
                            if ($('#new_msg').text())
                                msg_num = parseInt($('#new_msg').text().replace(')', '').replace('(', '')) + 1;
                            else
                                msg_num = 1;
                            $('#new_msg').html("<div class=\"headm_newac\" style=\"margin-left:37px\">" + msg_num + "</div>");
                        }
                        temp = '<div class="update_box cursor_pointer" id="event' + row.time + '" onClick="GoPage(event, \'' + row.link + '\'); upClose(' + row.time + ')"><div class="update_box_margin"><div style="height:19px"><span>' + uTitle + '</span><div class="update_close fl_r no_display" id="update_close" onMouseDown="upClose(' + row.time + ')"><div class="update_close_ic" id="update_close2"></div></div></div><div class="clear"></div><div class="update_inpad"><a href="/u' + row.id + '" onClick="Page.Go(this.href); return false"><div class="update_box_marginimg"><img src="' + row.ava + '" id="no_ev" /></div></a><div class="update_data"><a id="no_ev" href="/u' + row.id + '" onClick="Page.Go(this.href); return false">' + row.name + '</a>&nbsp;&nbsp;' + row.text + '</div></div><div class="clear"></div></div></div>';
                        $('#updates').html($('#updates').html() + temp);
                        var beepThree = $("#beep-three")[0];
                        beepThree.play();
                        if ($('.update_box').size() <= 5)
                            $('#updates').animate({'height': (123 * $('.update_box').size()) + 'px'});
                        if ($('.update_box').size() > 5) {
                            evFirst = $('.update_box:first').attr('id');
                            $('#' + evFirst).animate({'margin-top': '-123px'}, 400, function () {
                                $('#' + evFirst).fadeOut('fast', function () {
                                    $('#' + evFirst).remove();
                                });
                            });
                        }
                    }
                } else {
                    // addAllErr('Ошибка доступа');
                }

            },
            onFail:function(d){ // А если нет. то показать ошибку
                // alert("Error");
                // console.warn('Ошибка '+ "\n");
            }
        });
	}, 3000);
});