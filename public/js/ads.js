var ads = {
	optionad: function(whad){
		var type = $("#whad").val();
		var what;
		if(type != ''){
			$("#"+type).removeClass("selected");
		}
		if(whad == 'app'){
			$("#ads_param_link_type_app").addClass("selected");
			$("#whad").val('ads_param_link_type_app');
		} else if(whad == 'pub'){
			$("#ads_param_link_type_community").addClass("selected");
			$("#whad").val('ads_param_link_type_community');
		} else if(whad == 'url'){
			$("#ads_param_link_type_link").addClass("selected");
			$("#whad").val('ads_param_link_type_link');
		}
		$.post('/ads/optionad/', {whad: whad}, function(what_ad){
			$("#what_ad").html(what_ad);
		});
	},
	checkurl: function(){
		var url = $("#url").val();
		$.post('/ads/checkurl/', {url: url}, function(result){
			$("#domainch").html(result);
		});
	},
	ads_continue: function(type){
		if(type == 'url'){ 
			$("#url").attr("disabled", true);
			$("#checkurl").hide();
			$("#continue").text('Изменить');
			$("#continue").attr("onClick","ads.changeads('url');");
			$.post('/ads/nextcreate/', {type: type}, function(result){
				$("#nextcreate").html(result);
			});
		} else if(type == 'pub'){
			$.post('/ads/nextcreate/', {type: type}, function(result){
				$("#nextcreate").html(result);
			});
		} else if(type == 'app'){
			$.post('/ads/nextcreate/', {type: type}, function(result){
				$("#nextcreate").html(result);
			});
		}
	},
	changeform: function(type){
		$("#format").val(type);
		if(type == '2'){
			$("#description").attr("disabled", true);
			$("#name").attr("disabled", false);
			$("#exdescr").hide();
			$("#previewimg").attr("class","previewadimg_2");
			$("#previewimg1").attr("style","width:145px;height:165px;");
		} else if(type == '1'){
			$("#description").attr("disabled", false);
			$("#name").attr("disabled", false);
			$("#exdescr").show();
			$("#previewimg").attr("class","previewadimg_1");
			$("#previewimg1").attr("style","width:145px;height:85px;");
		} else if(type == '3'){
			var publicid = $("#publicid").val();
			$.post('/ads/bigtype/', {type: type, publicid: publicid}, function(d){
				d = JSON.parse(d);
				$("#name").val(d.title);
				$("#name").attr("disabled", true);
				$("#description").attr("disabled", true);
				$('#exname').text(d.title);
				$('#exdescr').text(d.traf+' подписчиков');
				$("#previewimg").attr("class","previewadimg_3");
				$('#previewimg1').attr('src', d.ava);
				$("#previewimg1").attr("style","width:145px;height:145px;");
			});
		} else if(type == '4'){
			var appid = $("#appid").val();
			$.post('/ads/bigtype/', {type: type, appid: appid}, function(d){
				d = JSON.parse(d);
				$("#name").val(d.title);
				$("#name").attr("disabled", true);
				$("#description").attr("disabled", true);
				$('#exname').text(d.title);
				$('#exdescr').text(d.traf+' участников');
				$("#previewimg").attr("class","previewadimg_3");
				$('#previewimg1').attr('src', d.ava);
				$("#previewimg1").attr("style","width:145px;height:145px;");
			});
		}
	},
	changepay: function(type){
		$("#pay").val(type);
		if(type == '1'){
			$("#typepay").html('<div class="texta">Количество переходов:</div><input type="text" id="price" class="inpst" style="width:50px;" value="1" onkeyup="ads.updatepay();"/><div class="mgclr"></div>');
			$("#fprice").val('5');
		} else {
			$("#typepay").html('<div class="texta">Количество показов:</div><input type="text" id="price" class="inpst" style="width:50px;" value="1" onkeyup="ads.updatepay();"/><div class="mgclr"></div>');
			$("#fprice").val('1');
		}
	},
	updatepay: function(){
		var type = $("#pay").val();
		if(type == '1'){
			$("#fprice").val($("#price").val()*5);
		} else {
			$("#fprice").val($("#price").val());
		}
	},
	updtitles: function(title){
		if(title == 'name'){
			$('#exname').text($('#name').val());
			if($('#name').val() == ''){
				$('#exname').text('Реклама');
			}
		} else if(title == 'descr'){
			$('#exdescr').text($('#description').val());
			if($('#description').val() == ''){
				$('#exdescr').text('Образец описания');
			}
		}
	},
	upload_img: function(){
		Box.Page('/ads/uploadimg/', 'loadphoto', 400, lang_title_load_photo, 'Загрузка фотографии', lang_box_canсel, 0, 0, 0, 0, 0, 0, 1);
	},
	loadage: function(){
		$.post('/ads/loadage/', {agefirst: $('#agefrom').val()}, function(result){
			$("#agel").html(result);
		});
	},
	create: function(whad){
		var format = $("#format").val();
		var name = $("#name").val();
		var description = $("#description").val();
		var country = $("#country").val();
		var sex = $("#sex").val();
		var agefrom = $("#agefrom").val();
		var agelast = $("#agelast").val();
		var sp = $("#sp").val();
		var pay = $("#pay").val();
		var price = $("#price").val();
		var photo = $("#previewimg1").attr('src');
		
		if(whad == 'url'){
			$.post('/ads/createad/', {name: name, description: description, whad: whad, type: format, pay: pay, photo: photo, url: $('#url').val(), price: price, country: country, sex: sex, agefrom: agefrom, agelast: agelast, sp: sp}, function(result){
				if(result == 'success'){
					Box.Info('info_ads', 'Объявление успешно создано', 'Перейдите в личный кабинет, чтобы следить за статистикой объявления', 300);
				} else {
					Box.Info('info_ads', 'Error', 'Code #1', 300);
				}
			});
		} else if(whad == 'pub'){
			$.post('/ads/createad/', {name: name, description: description, whad: whad, type: format, pay: pay, photo: photo, url: $("#publicid").val(), price: price, country: country, sex: sex, agefrom: agefrom, agelast: agelast, sp: sp}, function(result){
				if(result == 'success'){
					Box.Info('info_ads', 'Объявление успешно создано', 'Перейдите в личный кабинет, чтобы следить за статистикой объявления', 300);
				} else {
					Box.Info('info_ads', 'Error', 'Code #1', 300);
				}
			});
		} else if(whad == 'app'){
			$.post('/ads/createad/', {name: name, description: description, whad: whad, type: format, pay: pay, photo: photo, url: $("#appid").val(), price: price, country: country, sex: sex, agefrom: agefrom, agelast: agelast, sp: sp}, function(result){
				if(result == 'success'){
					Box.Info('info_ads', 'Объявление успешно создано', 'Перейдите в личный кабинет, чтобы следить за статистикой объявления', 300);
				} else {
					Box.Info('info_ads', 'Error', 'Code #1', 300);
				}
			});
		}
	},
	status_ad: function(aid){
		$.post('/ads/status_ad/', {aid: aid}, function(result){
			if($("#status_"+aid).hasClass("status_on")){
				$("#status_"+aid).removeClass("status_on");
				$("#status_"+aid).addClass("status_off");
			} else {
				$("#status_"+aid).removeClass("status_off");
				$("#status_"+aid).addClass("status_on");
			}
			Box.Info('info_ads', 'Объявление', result, 300);
		});
	},
	clickgo: function(aid){
		$.post('/ads/clickgo/', {aid: aid}, function(res){
			window.location.href = res;
		});
	}
}