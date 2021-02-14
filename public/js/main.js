var doLoad = {
	data: function(i){
		doLoad.js(i);
	},
	js: function(i){
		const arr = ['audio_player', 'rating', 'payment'];
		let check = $('#dojs'+arr[i]).length;
		if(!check)
			$('#doLoad').append('<div id="dojs'+arr[i]+'"><script type="text/javascript" src="/js/'+arr[i]+'.js"></script></div>');
	}
}

var uagent = navigator.userAgent.toLowerCase();
var is_safari = ((uagent.indexOf('safari') != -1) || (navigator.vendor == "Apple Computer, Inc."));
var is_ie = ((uagent.indexOf('msie') != -1) && (!is_opera) && (!is_safari) && (!is_webtv));
var is_ie4 = ((is_ie) && (uagent.indexOf("msie 4.") != -1));
var is_moz = (navigator.product == 'Gecko');
var is_ns = ((uagent.indexOf('compatible') == -1) && (uagent.indexOf('mozilla') != -1) && (!is_opera) && (!is_webtv) && (!is_safari));
var is_ns4 = ((is_ns) && (parseInt(navigator.appVersion) == 4));
var is_opera = (uagent.indexOf('opera') != -1);
var is_kon = (uagent.indexOf('konqueror') != -1);
var is_webtv = (uagent.indexOf('webtv') != -1);
var is_win = ((uagent.indexOf("win") != -1) || (uagent.indexOf("16bit") != -1));
var is_mac = ((uagent.indexOf("mac") != -1) || (navigator.vendor == "Apple Computer, Inc."));
var is_chrome = (uagent.match(/Chrome\/\w+\.\w+/i)); if(is_chrome == 'null' || !is_chrome || is_chrome == 0) is_chrome = '';
var ua_vers = parseInt(navigator.appVersion);
var req_href = location.href;
var vii_interval = false;
var vii_interval_im = false;
var scrollTopForFirefox = 0;
var url_next_id = 1;

//AJAX PAGES
window.onload = function() {
	window.setTimeout(function() {
		window.addEventListener("popstate", function(e) {
			e.preventDefault();
			if (CheckRequestPhoto(e.state.link))
				Photo.Prev(e.state.link);
			else if (CheckRequestVideo(e.state.link))
				videos.prev(e.state.link);
			else
				Page.Prev(e.state.link);
		}, false);
	}, 1);
}

if(CheckRequestPhoto(req_href)){
	$(document).ready(function(){
		Photo.Show(req_href);
	});
}
if(CheckRequestVideo(req_href)){
	$(document).ready(function(){
		let close_link;
		const video_id = req_href.split('_');
		const section = req_href.split('sec=');
		const fuser = req_href.split('wall/');
		if(fuser[1])
			{
				close_link = '/u'+fuser[1];
			}
		else
			{
				close_link = '';
			}
		if(section[1]){
			var xSection = section[1].split('/');
			if(xSection[0] === 'news')
				{
					close_link = 'news';
				}
			if(xSection[0] === 'msg'){
				const msg_id = xSection[1].split('id=');
				close_link = '/messages/show/'+msg_id[1]+'/';
			}
		}
		
		videos.show(video_id[1], req_href, close_link);
	});
}
function CheckRequestPhoto(request){
	var pattern = new RegExp(/photo[0-9]/i);
 	return pattern.test(request);
}
function CheckRequestVideo(request){
	var pattern = new RegExp(/video[0-9]/i);
 	return pattern.test(request);
}
const Page = {
	Loading: function(f){
		const top_pad = window.innerHeight / 2 - 50;
		const loading = $('#loading');

		if(f === 'start'){
			loading.remove();
			$('html, body').append('<div id="loading" style="margin-top:'+top_pad+'px"><div class="d-flex justify-content-center" style="width: 3rem; height: 3rem;">\n' +
				'  <div class="spinner-border text-primary" role="status">\n' +
				'    <span class="sr-only">Loading...</span>\n' +
				'  </div>\n' +
				'</div></div>');
			loading.show();
		}
		if(f === 'stop'){
			loading.remove();
		}
	},
	Go: function(h){
		$('.js_titleRemove, .vii_box').remove();
		Page.Loading('start');
		$.post(h, {ajax: 'yes'}, function(res){
			// var d = JSON.parse(res);
			const page = $('#page');
			history.pushState({link:h}, res.title, h);
			document.title = res.title;
			page.html(res.content);
			Page.Loading('stop');
			$('html, body').scrollTop(0);
			// $('.ladybug_ant').imgAreaSelect({remove: true});
			if(window.audio_player && !audio_player.pause)
				audio_player.command('play', {style_only:true});
			//Чистим стили AuroResizeWall
			// $('#addStyleClass').remove();
			//Удаляем кеш фоток, видео, модальных окон
			// $('.photo_view, .box_pos, .box_info, .video_view').remove();
			//Возвращаем scroll
			$('html').css('overflow-y', 'auto');
			// $('html, body').css('overflow-y', 'auto');

			//Возвращаем дизайн плеера
			if($('.staticPlbg').length){
				$('.staticPlbg').css('margin-top', '-500px');
				player.reestablish();
			}

			// $('#new_msg').html(d.user_pm_num);
			// $('#new_news').html(d.new_news);
			// $('#new_ubm').html(d.new_ubm);
			// $('#ubm_link').attr('href', d.gifts_link);
			// $('#new_support').html(d.support);
			// $('#news_link').attr('href', '/news/'+d.news_link);
			// $('#new_requests').html(d.demands);
			// $('#new_guests').html(d.guests);
			// $('#new_photos').html(d.new_photos);
			// $('#requests_link_new_photos').attr('href', '/albums/'+d.new_photos_link);
			// $('#requests_link').attr('href', '/friends'+d.requests_link);
			// $('#new_groups').html(d.new_groups);
			// $('#new_groups_lnk').attr('href', d.new_groups_lnk);
			$('#new_notifications').attr('href', res.new_groups_lnk);

		});
		Page.Loading('stop');
	},
	Prev: function(h){
		console.log(h);
		// clearInterval(vii_interval);
		// clearInterval(vii_interval_im);
		
		// $('.vii_box').remove();
		
		Page.Loading('start');
		$.post(h, {ajax: 'yes'}, function(res){
			// var d = JSON.parse(res);
			const page = $('#page');
			history.pushState({link:h}, res.title, h);
			document.title = res.title;
			page.html(res.content);
			Page.Loading('stop');
			$('html, body').scrollTop(0);
			// $('.ladybug_ant').imgAreaSelect({remove: true});
			if(window.audio_player && !audio_player.pause) audio_player.command('play', {style_only:true});
			//Чистим стили AuroResizeWall
			$('#addStyleClass').remove();
			//Удаляем кеш фоток, видео, модальных окон
			$('.photo_view, .box_pos, .box_info, .video_view').remove();
			//Возвращаем scroll
			$('html').css('overflow-y', 'auto');
			// $('html, body').css('overflow-y', 'auto');

			//Возвращаем дизайн плеера
			if($('.staticPlbg').length){ $('.staticPlbg').css('margin-top', '-500px'); player.reestablish(); }

			// $('#new_msg').html(d.user_pm_num);
			// $('#new_news').html(d.new_news);
			// $('#new_ubm').html(d.new_ubm);
			// $('#ubm_link').attr('href', d.gifts_link);
			// $('#new_support').html(d.support);
			// $('#news_link').attr('href', '/news/'+d.news_link);
			// $('#new_requests').html(d.demands);
			// $('#new_guests').html(d.guests);
			// $('#new_photos').html(d.new_photos);
			// $('#requests_link_new_photos').attr('href', '/albums/'+d.new_photos_link);
			// $('#requests_link').attr('href', '/friends'+d.requests_link);
			// $('#new_groups').html(d.new_groups);
			// $('#new_groups_lnk').attr('href', d.new_groups_lnk);
			$('#new_notifications').attr('href', res.new_groups_lnk);

		});
	}
}
//PROFILE FUNC
const Profile = {
	miniature: function () {
		Page.Loading('start');
		$.post('/edit/miniature/', function (d) {
			Page.Loading('stop');
			if (d.status !== 1)
				addAllErr('Вы пока что не загрузили фотографию.');
			else {
				if (is_moz && !is_chrome) scrollTopForFirefox = $(window).scrollTop();
				$('html, body').css('overflow-y', 'hidden');
				if (is_moz && !is_chrome) $(window).scrollTop(scrollTopForFirefox);
				$('body').append('<div id="newbox_miniature">' + d + '</div>');
			}
			$(window).keydown(function (event) {
				if (event.keyCode === 27) Profile.miniatureClose();
			});
		});
	},
	preview: function (img, selection) {
		if (!selection.width || !selection.height) return;
		const scaleX = 100 / selection.width;
		const scaleY = 100 / selection.height;
		const scaleX50 = 50 / selection.width;
		const scaleY50 = 50 / selection.height;
		$('#miniature_crop_100 img').css({
			width: Math.round(scaleX * $('#miniature_crop').width()),
			height: Math.round(scaleY * $('#miniature_crop').height()),
			marginLeft: -Math.round(scaleX * selection.x1),
			marginTop: -Math.round(scaleY * selection.y1)
		});
		$('#miniature_crop_50 img').css({
			width: Math.round(scaleX50 * $('#miniature_crop').width()),
			height: Math.round(scaleY50 * $('#miniature_crop').height()),
			marginLeft: -Math.round(scaleX50 * selection.x1),
			marginTop: -Math.round(scaleY50 * selection.y1)
		});
	},
	miniatureSave: function () {
		const i_left = $('#mi_left').val();
		const i_top = $('#mi_top').val();
		const i_width = $('#mi_width').val();
		const i_height = $('#mi_height').val();
		butloading('miniatureSave', '111', 'disabled', '');
		$.post('/edit/miniature_save/', {
			i_left: i_left,
			i_top: i_top,
			i_width: i_width,
			i_height: i_height
		}, function (d) {
			if (d == 'err') addAllErr('Ошибка');
			else window.location.href = '/u' + d;
			butloading('miniatureSave', '111', 'enabled', 'Сохранить изменения');
		});
	},
	miniatureClose: function () {
		$('#miniature_crop').imgAreaSelect({remove: true});
		$('#newbox_miniature').remove();
		$('html, body').css('overflow-y', 'auto');
	},
	LoadCity: function (id) {
		$('#load_mini').show();
		if (id > 0) {
			$('#city').slideDown();
			$('#select_city').load('/loadcity/', {country: id});
		} else {
			$('#city').slideUp();
			$('#load_mini').hide();
		}
	},
	//MAIN PHOTOS
	LoadPhoto: function () {
		Page.Loading('start');
		$.get('/edit/load_photo/', {ajax: 'yes'}, function (data) {
			Box.Show('photo', 400, lang_title_load_photo, data, lang_box_cancel);
			Page.Loading('stop');
		});
	},
	DelPhoto: function () {
		Box.Show('del_photo', 400, lang_title_del_photo, '<div style="padding:15px;">' + lang_del_photo + '</div>', lang_box_cancel, lang_box_yes, 'Profile.StartDelPhoto(); return false;');
	},
	StartDelPhoto: function () {
		$('#box_loading').show();
		$.get('/edit/del_photo/', function () {
			$('#ava').html('<img src="/images/no_ava.gif" alt="" />');
			$('#del_pho_but').hide();
			Box.Close('del_photo');
			Page.Loading('stop');
		});
	},
	MoreInfo: function () {
		$('#moreInfo').show();
		$('#moreInfoText').text('Скрыть подробную информацию');
		$('#moreInfoLnk').attr('onClick', 'Profile.HideInfo()');
	},
	HideInfo: function () {
		$('#moreInfo').hide();
		$('#moreInfoText').text('Показать подробную информацию');
		$('#moreInfoLnk').attr('onClick', 'Profile.MoreInfo()');
	}
};

//VII BOX
const viiBox = {
	start: function(){
		Page.Loading('start');
	},
	stop: function(){
		Page.Loading('stop');
	},
	win: function(i, d, o, h){
		viiBox.stop();
		if(is_moz && !is_chrome)
			scrollTopForFirefox = $(window).scrollTop();
		$('html, body').css('overflow-y', 'hidden');
		if(is_moz && !is_chrome)
			$(window).scrollTop(scrollTopForFirefox);
		$('body').append('<div id="newbox_miniature'+i+'" class="vii_box">'+d+'</div>');
		$(window).keydown(function(event){
			if(event.keyCode === 27)
				viiBox.clos(i, o, h);
		});
	},
	clos: function(i, o, h){
		$('#newbox_miniature'+i).remove();
		if(o)
			$('html, body').css('overflow-y', 'auto');
		if(h)
			history.pushState({link:h}, null, h);
		Page.Loading('stop');
	}
}

//MODAL BOX
const Box = {
	Page: function(url, data, name, width, title, cancel_text, func_text, func, height, overflow, bg_show, bg_show_bottom, input_focus, cache){
	
		//url - ссылка которую будем загружать
		//data - POST данные
		//name - id окна
		//width - ширина окна
		//title - заголовк окна
		//content - контент окна
		//close_text - текст закрытия
		//func_text - текст который будет выполнять функцию
		//func - функция текста "func_text"
		//height - высота окна
		//overflow - постоянный скролл
		//bg_show - тень внтури окна сверху
		//bg_show_bottom - "1" - с тенью внтури, "0" - без тени внутри
		//input_focus - ИД текстового поля на котором будет фиксация
		//cache - "1" - кешировоть, "0" - не кешировать

		if(cache)
			if(ge('box_'+name)){
				Box.Close(name, cache);
				$('#box_'+name).show();
				$('#box_content_'+name).scrollTop(0);
				if(is_moz && !is_chrome)
					scrollTopForFirefox = $(window).scrollTop();
				
				$('html').css('overflow', 'hidden');

				if(is_moz && !is_chrome)
					$(window).scrollTop(scrollTopForFirefox);
				return false;
			}
		
		Page.Loading('start');
		$.post(url, data, function(html){
			if(!CheckRequestVideo(location.href))
				Box.Close(name, cache);
			Box.Show(name, width, title, html, cancel_text, func_text, func, height, overflow, bg_show, bg_show_bottom, cache);
			Page.Loading('stop');
			if(input_focus)
				$('#'+input_focus).focus();
		});
	},
	Show: function(name, width, title, content, close_text, func_text, func, height, overflow, bg_show, bg_show_bottom, cache){
		
		//name - id окна
		//width - ширина окна
		//title - заголовк окна
		//content - контент окна
		//close_text - текст закрытия
		//func_text - текст который будет выполнять функцию
		//func - функция текста "func_text"
		//height - высота окна
		//overflow - постоянный скролл
		//bg_show - тень внтури окна сверху
		//bg_show_bottom - тень внтури внтури снизу
		let func_but;
		//cache - "1" - кешировоть, "0" - не кешировать
		let top_pad = $(window).height() / 2 - 50;

		if(func_text){
			func_but = '<div class="button_div fl_r" style="margin-right:10px;" id="box_but"><button onClick="'+func+'" id="box_butt_create">'+func_text+'</button></div>';
		}else{
			func_but = '';
		}

		const close_but = '<div class="button_div_gray fl_r"><button onClick="Box.Close(\'' + name + '\', ' + cache + '); return false;">' + close_text + '</button></div>';

		const box_loading = '<div class="spinner-border" id="box_loading" style="display:none;" role="status"><span class="sr-only">Loading...</span></div>';

		if(height)
			{
				top_pad = ($(window).height() - 150 - height) / 2;
			}
			if(top_pad < 0)
				top_pad = 100;
			
		if(overflow)
			//var overflow = 'overflow-y:scroll;';
			var overflow = '';
		else
			var overflow = '';
			
		if(bg_show)
			if(overflow)
				var bg_show = '<div class="bg_show" style="width:'+(width-19)+'px;"></div>';
			else
				var bg_show = '<div class="bg_show" style="width:'+(width-2)+'px;"></div>';
		else
			var bg_show = '';
		
		if(bg_show_bottom)
			if(overflow)
				var bg_show_bottom = '<div class="bg_show_bottom" style="width:'+(width-17)+'px;"></div>';
			else
				var bg_show_bottom = '<div class="bg_show_bottom" style="width:'+(width-2)+'px;"></div>';
		else
			var bg_show_bottom = '';
			
		if(height)
			var sheight = 'height:'+height+'px';
		else
			var sheight = '';

		var close_img = '<svg class="bi bi-x-circle" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">\n' +
			'  <path fill-rule="evenodd" d="M11.854 4.146a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708-.708l7-7a.5.5 0 0 1 .708 0z"></path>\n' +
			'  <path fill-rule="evenodd" d="M4.146 4.146a.5.5 0 0 0 0 .708l7 7a.5.5 0 0 0 .708-.708l-7-7a.5.5 0 0 0-.708 0z"></path>\n' +
			'</svg>';

		$('body').append('<div id="modal_box"><div id="box_'+name+'" class="box_pos"><div class="card box_bg" style="width:'+width+'px;margin: 0 auto; margin-top:'+top_pad+'px;"><div class="card-header" id="box_title_'+name+'">'+title+'<div class="box_close" onClick="Box.Close(\''+name+'\', '+cache+'); return false;">'+close_img+'</div></div><div class="card-body" id="box_content_'+name+'" style="'+sheight+';'+overflow+'">'+content+'<div class="clear"></div></div>'+bg_show_bottom+'<div class="box_footer"><div id="box_bottom_left_text" class="fl_l">'+box_loading+'</div>'+close_but+func_but+'</div></div></div></div>');
		
		$('#box_'+name).show();

		if(is_moz && !is_chrome)
			scrollTopForFirefox = $(window).scrollTop();
		
		$('html').css('overflow', 'hidden');

		if(is_moz && !is_chrome)
			$(window).scrollTop(scrollTopForFirefox);
		
		$(window).keydown(function(event){
			if(event.keyCode == 27) {
				Box.Close(name, cache);
			} 
		});
	},
	Close: function(name, cache){
	
		if(!cache)
			$('.box_pos').remove();
		else
			$('.box_pos').hide();

		if(CheckRequestVideo(location.href) == false && CheckRequestPhoto(location.href) == false)
			$('html, body').css('overflow-y', 'auto');
			
		if(CheckRequestVideo(location.href))
			$('#video_object').show();
			
		if(is_moz && !is_chrome)
			$(window).scrollTop(scrollTopForFirefox);
	},
	GeneralClose: function(){
		$('#modal_box').hide();
	},
	Info: function(bid, title, content, width, tout){
		var top_pad = ($(window).height()-115)/2;
		$('body').append('<div id="'+bid+'" class="box_info"><div class="box_info_margin" style="width: '+width+'px; margin-top: '+top_pad+'px"><b><span>'+title+'</span></b><br /><br />'+content+'</div></div>');
		$(bid).show();
		
		if(!tout)
			var tout = 1400;
		
		setTimeout("Box.InfoClose()", tout);
		
		$(window).keydown(function(event){
			if(event.keyCode == 27) {
				Box.InfoClose();
			} 
		});
	},
	InfoClose: function(){
		$('.box_info').fadeOut();
	}
}
function ge(i){
	// return $('#'+i);
	return document.getElementById(i);
}
function butloading(i, w, d, t){
	if(d == 'disabled'){
		$('#'+i).html('<div style="width:'+w+'px;text-align:center;"><img src="/images/loading_mini.gif" alt="" /></div>');
		ge(i).disabled = true;
	} else {
		$('#'+i).html(t);
		ge(i).disabled = false;
	}
}
function textLoad(i){
	$('#'+i).html('<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>').attr('onClick', '').attr('href', '#');
}
function updateNum(i, type){
	if(type)
		$(i).text(parseInt($(i).text())+1);
	else
		$(i).text($(i).text()-1);
}
function setErrorInputMsg(i){
	$("#"+i).css('background', '#ffefef');
	$("#"+i).focus();
	setTimeout("$('#"+i+"').css('background', '#fff').focus()", 700);
}
function addAllErr(text, tim = 2500){
	// if(!tim)
	// {
	// 	var tim = 2500;
	// }
	let privacy_err = $('.privacy_err');

	privacy_err.remove();
	$('body').append('<div class="privacy_err" style="z-index: 1000;">'+text+'</div>');
	privacy_err.fadeIn('fast');
	setTimeout("$('.privacy_err').fadeOut('fast')", tim);
	privacy_err.remove();
}
function addAllWarn(text, tim = 2500){
	// if(!tim)
	// {
	// 	var tim = 2500;
	// }
	let privacy_warn = $('.privacy_warn');

	privacy_warn.remove();
	$('body').append('<div class="privacy_warn" style="z-index: 1000;">'+text+'</div>');
	privacy_warn.fadeIn('fast');
	setTimeout("$('.privacy_warn').fadeOut('fast')", tim);
	privacy_warn.remove();
}
function langNumric(id, num, text1, text2, text3, text4, text5){
	strlen_num = num.length;
	
	if(num <= 21){
		numres = num;
	} else if(strlen_num === 2){
		parsnum = num.substring(1,2);
		numres = parsnum.replace('0','10');
	} else if(strlen_num === 3){
		parsnum = num.substring(2,3);
		numres = parsnum.replace('0','10');
	} else if(strlen_num === 4){
		parsnum = num.substring(3,4);
		numres = parsnum.replace('0','10');
	} else if(strlen_num === 5){
		parsnum = num.substring(4,5);
		numres = parsnum.replace('0','10');
	}
	
	if(numres <= 0)
		var gram_num_record = text5;
	else if(numres === 1)
		var gram_num_record = text1;
	else if(numres < 5)
		var gram_num_record = text2;
	else if(numres < 21)
		var gram_num_record = text3;
	else if(numres === 21)
		var gram_num_record = text4;
	else
		var gram_num_record = '';
	
	$('#'+id).html(gram_num_record);
}

//LANG
const trsn = {
  box: function(){
    $('.js_titleRemove').remove();
    viiBox.start();
	$.post('/lang/', function(d){
	  viiBox.win('vii_lang_box', d['content']);
	});
  }
}

//Theme
const theme = {
	edit: function(id){
		// $('.js_titleRemove').remove();
		// viiBox.start();
		let theme_mode;

// let chbox = $('#theme');
		const chbox = document.getElementById('theme');

		if (chbox.checked) {
			theme_mode = 1;
			chbox.checked = false;
			// console.log ('Dark mod off');
			// addAllErr('Dark mod off', 3000);
		}else{
			theme_mode = 0;
			chbox.checked = true;
			// console.log('Dark mod on');
			// addAllWarn('Dark mod on', 3000);
		}


		// if (chbox[0].checked) {
		// 	theme_mode = 1;
		// 	let r = document.querySelector('input[type="radio"]');
		// 	r.setAttribute('checked', 'true');
		// 	$('#theme').attr('checked', true);
		// 	// $('#theme')[0].checked = true;
		// }else{
		// 	// $('#theme').attr('checked', false);
		// 	$('#theme')[0].checked = false;
		// }
		//
		$.post('/theme/', {theme: theme_mode}, function(d){
			let theme = d.res;
			$('#theme').html(theme);
			if (theme_mode === 1) {
				console.log ('Dark mod off');
				addAllErr('Dark mod off', 3000);
			}
			else {
				console.log('Dark mod on');
				addAllWarn('Dark mod on', 3000);
			}
		});
	}
}

function AntiSpam(act){
  
  Page.Loading('stop');

	var max_friends = 40;
	var max_msg = 40;
	var max_wall = 500;
	var max_comm = 2000;
  
  if(act === 'friends'){
    Box.Info('antispam_'+act, 'Информация', 'В день Вы можете отправить не более '+max_friends+' заявок в друзья.', 300, 4000);
  } else if(act === 'messages'){
    Box.Info('antispam_'+act, 'Информация', 'В день Вы можете отправить не более '+max_msg+' сообщений. Если Вы хотите продолжить общение с этим пользователем, то добавьте его в список своих друзей.', 350, 5000);
  } else if(act === 'wall'){
    Box.Info('antispam_'+act, 'Информация', 'В день Вы можете отправить не более '+max_wall+' записей на стену.', 350, 4000);
  } else if(act === 'comm'){
    Box.Info('antispam_'+act, 'Информация', 'В день Вы можете отправить не более '+max_comm+' комментариев.', 350, 4000);
  } else if(act === 'groups'){
    Box.Info('antispam_'+act, 'Информация', 'В день Вы можете создать не более <b>5</b> сообществ.', 350, 3000);
  }
  
}

function delMyPage(){
  Box.Show('del_page', 400, 'Удаление страницы', '<div style="padding:15px;">Вы уверены, что хотите удалить свою страницу ?</div>', lang_box_canсel, 'Да, удалить страницу', 'startDelpage()');
}

function startDelpage(){
  $('#box_loading').fadeIn('fast');
  $('.box_footer .button_div, .box_footer .button_div_gray').fadeOut('fast');
  $.post('/del_my_page/',  function(){
    window.location.href = '/';
  });
}

/* HTML 5 AUDIO PLAYER */
var cur = {
	destroy: []
};
cur.langs = {};
cur.Media = {};
var SuraTimers = {};

function str_replace(search, replace, subject) {
	if (!(replace instanceof Array)) {
		replace = new Array(replace);
		if (search instanceof Array) {
			while (search.length > replace.length) {
				replace[replace.length] = replace[0];
			}
		}
	}
	if (!(search instanceof Array)) search = new Array(search);
	while (search.length > replace.length) {
		replace[replace.length] = '';
	}
	if (subject instanceof Array) {
		for (k in subject) {
			subject[k] = str_replace(search, replace, subject[k]);
		}
		return subject;
	}
	for (var k = 0; k < search.length; k++) {
		var i = subject.indexOf(search[k]);
		while (i > -1) {
			subject = subject.replace(search[k], replace[k]);
			i = subject.indexOf(search[k], i);
		}
	}
	return subject;
}
if (!window.Kj) window.Kj = {};

Kj.dropMenu = {
	Init: function (p) {
		$('#' + p.id).addClass('DropMenu').attr('onClick', 'Kj.dropMenu.Hover(\'' + p.id + '\')').attr('onMouseOut', 'Kj.dropMenu.Out()');
		$('#' + p.id).html('<div onmouseover="Kj.dropMenu.over()" onMouseOut="Kj.dropMenu.Out()"><div id="titleDrop"></div><div class="DropMenuItems">' + $('#' + p.id).html() + '</div></div>');
		if (!p.nochange) {
			if (!p.selected) {
				var value = $('#' + p.id + ' li:first').attr('value');
				$('#' + p.id + ' #titleDrop').html($('#' + p.id + ' li:first').html());
				$('#' + p.id).attr('value', value).val(value);
			} else var value = p.selected;
		} else $('#' + p.id + ' #titleDrop').html(p.title);
		$.each($('#' + p.id + ' li'), function () {
			var val = $(this).attr('value');
			$(this).addClass('DropMenuItem');
			if (!p.nochange) $(this).attr('onClick', 'Kj.dropMenu.Change(\'' + p.id + '\', this, \'' + val + '\')');
			if (val == value && !p.nochange) {
				$('#' + p.id + ' #titleDrop').html($(this).html());
				$('#' + p.id).attr('value', val).val(val);
				$(this).addClass('DropMenuItemSelected');
			}
		});
		$('#' + p.id + ' .DropMenuItems').css('margin-left', '-1px');
		setTimeout(function () {
			window.objF = $('#' + p.id + ' .DropMenuItems');
			$('#' + p.id).css('width', ($('#' + p.id + ' .DropMenuItems').width() - 2) + 'px').after('<div class="clear"></div>');
		});
	},
	over: function () {
		removeTimer('dropmenu');
	},
	opened: 0,
	Hover: function (id) {
		if (Kj.dropMenu.opened == id) {
			Kj.dropMenu.opened = 0;
			return;
		}
		setTimeout(function () {
			Kj.dropMenu.opened = id;
			removeTimer('dropmenu');
			$('.DropMenu').removeClass('DropMenuHover');
			$('#' + id).addClass('DropMenuHover');
			$('#' + id + ' .DropMenuItems').show();
		}, 100);
	},
	Out: function () {
		removeTimer('dropmenu');
		addTimer('dropmenu', function () {
			Kj.dropMenu.opened = 0;
			$('.DropMenu').removeClass('DropMenuHover');
			$('.DropMenuItems').hide();
		}, 700);
	},
	Change: function (id, block, value) {
		var bl = $(block);
		$('#' + id + ' li').removeClass('DropMenuItemSelected');
		bl.addClass('DropMenuItemSelected');
		$('#' + id).attr('value', value).val(value);
		$('#' + id + ' #titleDrop').html(bl.html());
		$('#' + id).change();
		Kj.dropMenu.opened = id;
		cancelEvent(window.event);
	},
	inputValues: {},
	callbacks: {},
	InitInput: function (p) {
		if (!p.width) p.width = 400;
		if (!p.noAdd) p.noAdd = 15;
		$(p.id).addClass('DropInput').css('width', p.width + 'px');
		Kj.dropMenu.inputValues[p.id] = {};
		$(p.id).html('<div class="DropInputBlock" onMouseDown="Kj.dropMenu.DownInput(\'' + p.id + '\', ' + p.noAdd + ')"><div id="titleDrop"><div class="dropInputItems"><div id="items"></div><input type="text"/><div class="clear"></div></div></div><div class="DropInputStrelka"></div></div><div class="DropInputItem" style="width: ' + ($(p.id).width() - 2) + 'px">' + $(p.id).html() + '</div>');
		if (p.type == 'friends') var url = '/repost/loadFriends/';
		else if (p.type == 'groups') var url = '/repost/loadGroups¬_id=' + p.notID;
		else if (p.type == 'matirial') var url = '/edit/loadMatirial&male=1';
		else if (p.type == 'matirial2') var url = '/edit/loadMatirial';
		$(p.id + ' input').attr('placeholder', p.text).keyup(function () {
			Kj.dropMenu.sershInput({
				id: p.id,
				url: url,
				query: $(p.id + ' input').val(),
				noAdd: p.noAdd
			});
		});
		Kj.dropMenu.sershInput({
			id: p.id,
			url: url,
			query: '',
			noAdd: p.noAdd,
			sel: p.selected
		});
		$(p.id).after('<div class="clear"></div>');
		if (p.cb) this.callbacks[p.id] = p.cb;
		Kj.dropMenu.size_input(p.id);
	},
	size_input: function (id) {
		var inp = $(id + ' #titleDrop input'),
			w = $(id).width() - 30,
			items = $(id + ' #items'),
			items_w = items.width(),
			rw = Math.max(100, w - items_w);
		if (items_w >= w) {
			var ww = 0;
			$(id + ' #items li').each(function () {
				ww += $(this).width() + 3;
			});
			var lines = Math.ceil(ww / w);
			rw = w * (ww / w) - ww;
			items.css('float', 'none');
		} else items.css('float', 'left');
		inp.css('width', rw + 'px');
	},
	sershInput: function (p) {
		$(p.id + ' .DropInputItem').load(p.url, {
			query: p.query,
			values: JSON.stringify(Kj.dropMenu.inputValues[p.id]),
			sel: p.sel
		}, function (d) {
			$.each($(p.id + ' .DropInputItem li'), function () {
				var img = $(this).attr('data-img');
				var name = $(this).attr('data-name');
				var value = $(this).val();
				var traf = $(this).attr('data-traf') || '';
				$(this).html('<div class="fl_l"><img src="' + img + '" style="width: 30px; height: 30px"/></div><div class="fl_l" style="margin-left: 5px"><div class="uName">' + name + '</div><div style="color: #666; font-size: 10px; margin-top: 2px">' + traf + '</div></div><div class="clear"></div>').mouseover(function () {
					$(p.id + ' .DropInputItem li').removeClass('DropInputItemsHover');
					$(this).addClass('DropInputItemsHover');
				}).mousedown(function () {
					Kj.dropMenu.insertItem({
						id: p.id,
						val: value,
						name: name,
						img: img,
						elem: this,
						traf: traf,
						noAdd: p.noAdd
					});
				});
				if (value == p.sel) Kj.dropMenu.insertItem({
					id: p.id,
					val: value,
					name: name,
					img: img,
					elem: this,
					traf: traf,
					noAdd: p.noAdd
				});
			});
		});
	},
	DownInput: function (id, noAdd) {
		if ($(id + ' .dropInputItems #items li').length < noAdd) {
			$(id + ' .DropInputItem li').removeClass('DropInputItemsHover');
			$(id + ' .DropInputItem li:first').addClass('DropInputItemsHover');
			$(id + ' .DropInputItem').show();
			$(id + ' input').focus().blur(function () {
				$(id + ' .DropInputItem').hide();
			});
			Kj.dropMenu.size_input(id);
		}
	},
	insertItem: function (p) {
		$(p.id + ' input').hide().val('');
		$(p.elem).remove();
		var arrInp = Kj.dropMenu.inputValues[p.id];
		arrInp[p.val] = 1;
		if (Kj.dropMenu.callbacks[p.id]) Kj.dropMenu.callbacks[p.id](Kj.dropMenu.inputValues[p.id]);
		var ins_li = $('<li/>').appendTo(p.id + ' .dropInputItems #items');
		ins_li.attr({
			'data-name': p.name,
			'data-img': p.img,
			value: p.val,
			'data-traf': p.traf
		}).html(p.name + ' ');
		var clos_lnk = $('<span/>').appendTo(ins_li);
		clos_lnk.html('x').addClass('clos').mousedown(function () {
			ins_li.remove();
			delete arrInp[p.val];
			$(p.id + ' .DropInputItem').hide();
			if ($(p.id + ' .dropInputItems #items li').length >= p.noAdd || !$(p.id + ' .dropInputItems #items li').size()) {
				$(p.id + ' .dropInputItems #items #addbut').remove();
				$(p.id + ' input').show();
				var height = $(p.id + ' .dropInputItems').height() - 8;
			} else var height = $(p.id + ' .dropInputItems').height() + 4;
			$(p.id + ' .DropInputBlock').css('height', height + 'px');
			var new_elem = $('<li/>').appendTo($(p.id + ' .DropInputItem'));
			new_elem.attr({
				'data-name': p.name,
				'data-img': p.img,
				value: p.val,
				'data-traf': p.traf
			}).html('<div class="fl_l"><img src="' + p.img + '" style="width: 30px; height: 30px"/></div><div class="fl_l" style="margin-left: 5px"><div class="uName">' + p.name + '</div><div style="color: #666; font-size: 10px; margin-top: 2px">' + p.traf + '</div></div><div class="clear"></div>').mouseover(function () {
				$(p.id + ' .DropInputItem li').removeClass('DropInputItemsHover');
				$(new_elem).addClass('DropInputItemsHover');
			}).mousedown(function () {
				Kj.dropMenu.insertItem({
					id: p.id,
					val: p.val,
					name: p.name,
					img: p.img,
					elem: new_elem,
					traf: p.traf,
					noAdd: p.noAdd
				});
			});
			$(p.id + ' #addbut').trigger('mousedown');
			if (Kj.dropMenu.callbacks[p.id]) Kj.dropMenu.callbacks[p.id](Kj.dropMenu.inputValues[p.id]);
			Kj.dropMenu.size_input(p.id);
		});
		$(p.id + ' .dropInputItems #items #addbut').remove();
		if ($(p.id + ' .dropInputItems #items li').length < p.noAdd) {
			var add_li = $('<li/>').appendTo(p.id + ' .dropInputItems #items');
			add_li.mousedown(function () {
				$(p.id + ' .dropInputItems #items #addbut').remove();
				$(p.id + ' input').show().val('');
				var height = $(p.id + ' .dropInputItems').height() - 8;
				$(p.id + ' .DropInputBlock').css('height', height + 'px');
				setTimeout(function () {
					Kj.dropMenu.DownInput(p.id, p.noAdd);
				}, 100);
			}).html(langs.media_video_add + ' <span class="clos">+</span>').addClass('addItemDropInput').attr('id', 'addbut');
		}
		var height = $(p.id + ' .dropInputItems').height() + 4;
		$(p.id + ' .DropInputBlock').css('height', height + 'px');
		Kj.dropMenu.size_input(p.id);
	}
};

Kj.radioBtn = {
	radioInit: function (bl, def, cb) {
		if (!def && arguments[0] == false) def = $(bl + ' div:first').attr('value');
		$(bl).attr('value', def);
		$.each($(bl + ' div'), function () {
			var val = $(this).attr('value');
			if (val == def) var classAdd = 'uiButtonBgActive';
			else var classAdd = '';
			$(this).html('<div class="ui_radioDiv" onmousedown="Kj.radioBtn.radioDown(this, \'' + bl + '\', ' + cb + ')" value="' + val + '"><div class="uiButtonBg ' + classAdd + '"></div><div class="fl_l">' + $(this).html() + '</div><div class="clear"></div></div>');
		});
	},
	radioDown: function (el, bl, cb) {
		$(bl + ' .uiButtonBg').removeClass('uiButtonBgActive');
		var elem = $(el).children('.uiButtonBg');
		elem.addClass('uiButtonBgActive');
		$(bl).val($(el).attr('value')).change();
		if (cb) cb();
	}
};
var kjSelectArea = {
	data: {},
	Init: function (id, p) {
		var _s = this;
		_s.data[id] = p;
		var width = $(id).width(),
			height = $(id).height(),
			bright = width - p.sw - 50,
			bbottom = height - p.sh - 50;
		$(id).prepend('<div class="kjSelectAreaBL" style="border-width: 50px ' + bright + 'px ' + bbottom + 'px 50px; width: ' + p.width + 'px;height: ' + p.height + 'px;"></div><div class="dropAreaBlock" style="width: ' + p.width + 'px;height: ' + p.height + 'px;">\
		<div class="kjSelectAreaResize select_resize_1" onmousedown="return kjSelectArea.resize(\'rtl\', \'' + id + '\');"></div>\
		<div class="kjSelectAreaResize select_resize_2" onmousedown="return kjSelectArea.resize(\'mt\', \'' + id + '\');"></div>\
		<div class="kjSelectAreaResize select_resize_3" onmousedown="return kjSelectArea.resize(\'rtr\', \'' + id + '\');"></div>\
		<div class="kjSelectAreaResize select_resize_4" onmousedown="return kjSelectArea.resize(\'mr\', \'' + id + '\');"></div>\
		<div class="kjSelectAreaResize select_resize_5" onmousedown="return kjSelectArea.resize(\'rbr\', \'' + id + '\');"></div>\
		<div class="kjSelectAreaResize select_resize_6" onmousedown="return kjSelectArea.resize(\'mb\', \'' + id + '\');"></div>\
		<div class="kjSelectAreaResize select_resize_7" onmousedown="return kjSelectArea.resize(\'rbl\', \'' + id + '\');"></div>\
		<div class="kjSelectAreaResize select_resize_8" onmousedown="return kjSelectArea.resize(\'ml\', \'' + id + '\');"></div>\
		</div>');
		var bl = $(id + ' .dropAreaBlock'),
			left = $(id).offset().left,
			top = $(id).offset().top,
			height = $(id).height(),
			width = $(id).width();
		if (p.hide) {
			bl.hide();
			$(id + ' .kjSelectAreaBL').css('border-color', 'rgba(0,0,0,0)');
		}
		bl.bind('mousedown', function (e) {
			if ($(e.target).filter('.kjSelectAreaResize').length > 0) return;
			e.preventDefault();
			left = $(id).offset().left, top = $(id).offset().top;
			_s.data[id].dl = e.pageX - bl.offset().left, _s.data[id].dt = e.pageY - bl.offset().top;
			$(window).bind('mousemove', Move);
		});
		$(window).bind('mouseup', Up);
		function Move(e1) {
			e1.preventDefault();
			if (_s.data[id].onStart) _s.data[id].onStart();
			var w1 = bl.width(),
				pos = e1.pageX - left - _s.data[id].dl,
				h1 = bl.height(),
				pos1 = e1.pageY - top - _s.data[id].dt;
			if ((pos + w1) > width) pos = width - w1;
			if (pos < 0) pos = 0;
			if (pos1 < 0) pos1 = 0;
			if ((pos1 + h1) > height) pos1 = height - h1;
			var bb = height - bl.height() - pos1,
				br = width - (bl.width() + pos),
				bt = top;
			bl.css('margin', pos1 + 'px ' + br + 'px ' + bb + 'px ' + pos + 'px');
			$(id + ' .kjSelectAreaBL').css('border-width', pos1 + 'px ' + br + 'px ' + bb + 'px ' + pos + 'px');
		}
		function Up() {
			$(window).unbind('mousemove', Move);
			if (_s.data[id].onEnd) _s.data[id].onEnd();
		}
		if (p.creator) {
			var pos_creat = {};
			$(id + ' .kjSelectAreaBL').bind('mousedown', function (e2) {
				left = $(id).offset().left, top = $(id).offset().top;
				var b1 = e2.pageX - left,
					b2 = e2.pageY - top,
					br = width - b1,
					bb = height - b2;
				pos_creat = {
					x: e2.pageX,
					y: e2.pageY
				};
				bl.css({
					'margin': b2 + 'px ' + br + 'px ' + bb + 'px ' + b1 + 'px',
					width: 0,
					height: 0
				}).show();
				$(id + ' .kjSelectAreaBL').css({
					'border-width': b2 + 'px ' + (br - bl.width()) + 'px ' + (bb - bl.height()) + 'px ' + b1 + 'px',
					width: 0,
					height: 0
				}).css('border-color', 'rgba(0,0,0,0.7)');
				$(window).bind('mousemove', moveCreat);
				$(window).bind('mouseup', upCreat);
			}).css('cursor', 'crosshair');

			function moveCreat(e3) {
				e3.preventDefault();
				if (_s.data[id].onStart) _s.data[id].onStart();
				var p1 = e3.pageX,
					p2 = e3.pageY,
					l = bl.offset().left - left,
					t = bl.offset().top - top;
				var w_nav = (p1 > pos_creat.x);
				var h_nav = (p2 > pos_creat.y);
				if (w_nav) {
					var ml = pos_creat.x - left;
					var w = Math.min(p1 - left - l, (width - ml));
				} else {
					var ml = Math.max(e3.pageX - left, 0);
					var w = Math.min(pos_creat.x - e3.pageX, (width - ml));
				}
				if (h_nav) {
					var mt = pos_creat.y - top;
					var h = Math.min(p2 - top - t, (height - mt));
				} else {
					var mt = Math.max(e3.pageY - top, 0);
					var h = Math.min(pos_creat.y - e3.pageY, (height - ml));
				}
				if (mt === 0) h = bl.height();
				if (ml === 0) w = bl.width();
				bl.css({
					width: w + 'px',
					'margin-left': ml + 'px',
					height: h + 'px',
					'margin-top': mt + 'px'
				});
				$(id + ' .kjSelectAreaBL').css({
					'border-width': mt + 'px ' + ((width - ml) - bl.width()) + 'px ' + ((height - mt) - bl.height()) + 'px ' + ml + 'px',
					width: w + 'px',
					height: h + 'px'
				});
			}
			function upCreat() {
				$(window).unbind('mousemove', moveCreat);
				$(window).unbind('mouseup', upCreat);
				var h1 = bl.height();
				if (bl.width() < p.width) bl.css('width', p.width + 'px');
				if (h1 < p.height) bl.css('height', p.height + 'px');
				if (bl.width() > p.max_width) bl.css('width', p.max_width + 'px');
				if (h1 > p.max_height) bl.css('height', p.max_height + 'px');
				h1 = bl.height();
				if (((bl.offset().top - top) + h1) > height) bl.css('margin-top', (height - h1) + 'px');
				var mt = bl.offset().top - top,
					ml = bl.offset().left - left;
				$(id + ' .kjSelectAreaBL').css({
					'border-width': mt + 'px ' + ((width - ml) - bl.width()) + 'px ' + ((height - mt) - bl.height()) + 'px ' + ml + 'px',
					width: bl.width() + 'px',
					height: bl.height() + 'px'
				});
				if (_s.data[id].onEnd) _s.data[id].onEnd();
			}
		}
	},
	resize: function (type, id) {
		var width = $(id).width(),
			height = $(id).height(),
			bl = $(id + ' .dropAreaBlock'),
			left = $(id).offset().left,
			top = $(id).offset().top,
			_s = kjSelectArea,
			Move = false;
		if (_s.data[id].onStart) _s.data[id].onStart();
		if (type == 'mt') {
			Move = function (e) {
				e.preventDefault();
				var pos = Math.round(e.pageY - top),
					mt = bl.offset().top - top,
					h1 = bl.height();
				pos = Math.max(0, pos);
				var res = mt - pos,
					res_h = res + h1;
				if (res_h < _s.data[id].max_height && res_h > _s.data[id].height) {
					if (_s.data[id].sizes) {
						var prop = res_h / bl.width();
						if (prop > _s.data[id].sizeh) return;
					}
					bl.css({
						height: res_h + 'px',
						'margin-top': pos + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						'border-top-width': pos + 'px',
						height: res_h + 'px'
					});
				}
			};
		} else if (type == 'mr') {
			Move = function (e) {
				e.preventDefault();
				var pos = Math.round(e.pageX - left),
					ml = bl.offset().left - left,
					w1 = bl.width();
				pos = Math.min(pos, width);
				var res = pos - ml - w1,
					res_w = res + w1,
					dleft = width - (res_w + ml);
				if (res_w < _s.data[id].max_width && res_w > _s.data[id].width) {
					if (_s.data[id].sizes) {
						var prop = res_w / bl.height();
						if (prop > _s.data[id].sizew) return;
					}
					bl.css({
						width: res_w + 'px',
						'margin-right': dleft + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						'border-right-width': dleft + 'px',
						width: res_w + 'px'
					});
				}
			};
		} else if (type == 'mb') {
			Move = function (e) {
				e.preventDefault();
				var pos = Math.round(e.pageY - top),
					mt = bl.offset().top - top,
					h1 = bl.height();
				pos = Math.min(pos, height);
				var res = pos - (mt + h1),
					res_h = res + h1;
				if (res_h < _s.data[id].max_height && res_h > _s.data[id].height) {
					if (_s.data[id].sizes) {
						var prop = res_h / bl.width();
						if (prop > _s.data[id].sizeh) return;
					}
					bl.css({
						height: res_h + 'px',
						'margin-bottom': (height - pos) + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						'border-bottom-width': (height - (res_h * 1) - mt) + 'px',
						height: res_h + 'px'
					});
				}
			};
		} else if (type == 'ml') {
			Move = function (e) {
				e.preventDefault();
				var pos = Math.round(e.pageX - left),
					ml = bl.offset().left - left,
					w1 = bl.width();
				pos = Math.max(0, pos);
				var res = ml - pos,
					res_w = res + w1;
				if (res_w < _s.data[id].max_width && res_w > _s.data[id].width) {
					if (_s.data[id].sizes) {
						var prop = res_w / bl.height();
						if (prop > _s.data[id].sizew) return;
					}
					bl.css({
						width: res_w + 'px',
						'margin-left': pos + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						'border-left-width': pos + 'px',
						width: res_w + 'px'
					});
				}
			};
		} else if (type == 'rtl') {
			Move = function (e) {
				e.preventDefault();
				var t1 = Math.round(e.pageY - top),
					l1 = Math.round(e.pageX - left),
					w1 = bl.width(),
					h1 = bl.height(),
					ml = bl.offset().left - left,
					mt = bl.offset().top - top;
				l1 = Math.max(0, l1);
				t1 = Math.max(0, t1);
				var res = ml - l1,
					res_w = res + w1,
					res1 = mt - t1,
					res_h = res1 + h1;
				if (res_w < _s.data[id].max_width && res_w > _s.data[id].width) {
					bl.css({
						width: res_w + 'px',
						'margin-left': l1 + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						'border-left-width': l1 + 'px',
						width: res_w + 'px'
					});
				}
				if (res_h < _s.data[id].max_height && res_h > _s.data[id].height) {
					bl.css({
						height: res_h + 'px',
						'margin-top': t1 + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						height: res_h + 'px',
						'border-top-width': t1 + 'px'
					});
				}
			};
		} else if (type == 'rtr') {
			Move = function (e) {
				e.preventDefault();
				var t1 = Math.round(e.pageY - top),
					l1 = Math.round(e.pageX - left),
					w1 = bl.width(),
					h1 = bl.height(),
					ml = bl.offset().left - left,
					mt = bl.offset().top - top;
				l1 = Math.min(width, l1);
				t1 = Math.max(0, t1);
				var res = l1 - ml - w1,
					res_w = res + w1,
					res1 = mt - t1,
					res_h = res1 + h1;
				if (res_w < _s.data[id].max_width && res_w > _s.data[id].width) {
					bl.css({
						width: res_w + 'px',
						'margin-right': (width - res_w - ml) + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						'border-right-width': (width - res_w - ml) + 'px',
						width: res_w + 'px'
					});
				}
				if (res_h < _s.data[id].max_height && res_h > _s.data[id].height) {
					bl.css({
						height: res_h + 'px',
						'margin-top': t1 + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						height: res_h + 'px',
						'border-top-width': t1 + 'px'
					});
				}
			}
		} else if (type == 'rbr') {
			Move = function (e) {
				e.preventDefault();
				var t1 = Math.round(e.pageY - top),
					l1 = Math.round(e.pageX - left),
					w1 = bl.width(),
					h1 = bl.height(),
					ml = bl.offset().left - left,
					mt = bl.offset().top - top;
				l1 = Math.min(width, l1);
				t1 = Math.min(height, t1);
				var res = l1 - ml - w1,
					res_w = res + w1,
					res1 = t1 - (mt + h1),
					res_h = res1 + h1;
				if (res_w < _s.data[id].max_width && res_w > _s.data[id].width) {
					bl.css({
						width: res_w + 'px',
						'margin-right': (width - res_w - ml) + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						'border-right-width': (width - res_w - ml) + 'px',
						width: res_w + 'px'
					});
				}
				if (res_h < _s.data[id].max_height && res_h > _s.data[id].height) {
					bl.css({
						height: res_h + 'px',
						'margin-bottom': (height - t1) + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						height: res_h + 'px',
						'border-bottom-width': (height - (res_h * 1) - mt) + 'px'
					});
				}
			};
		} else if (type == 'rbl') {
			Move = function (e) {
				e.preventDefault();
				var t1 = Math.round(e.pageY - top),
					l1 = Math.round(e.pageX - left),
					w1 = bl.width(),
					h1 = bl.height(),
					ml = bl.offset().left - left,
					mt = bl.offset().top - top;
				l1 = Math.max(0, l1);
				t1 = Math.min(height, t1);
				var res = ml - l1,
					res_w = res + w1,
					res1 = t1 - (mt + h1),
					res_h = res1 + h1;
				if (res_w < _s.data[id].max_width && res_w > _s.data[id].width) {
					bl.css({
						width: res_w + 'px',
						'margin-left': l1 + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						'border-left-width': l1 + 'px',
						width: res_w + 'px'
					});
				}
				if (res_h < _s.data[id].max_height && res_h > _s.data[id].height) {
					bl.css({
						height: res_h + 'px',
						'margin-bottom': (height - t1) + 'px'
					});
					$(id + ' .kjSelectAreaBL').css({
						height: res_h + 'px',
						'border-bottom-width': (height - (res_h * 1) - mt) + 'px'
					});
				}
			};
		}
		$(window).bind('mousemove', Move);
		$(window).bind('mouseup', Up);
		function Up() {
			if (_s.data[id].onEnd) _s.data[id].onEnd();
			$(window).unbind('mouseup', Up);
			$(window).unbind('mousemove', Move);
		}
	},
	getPos: function (id, img) {
		var top1 = $(id).offset().top,
			left1 = $(id).offset().left;
		var bl = $(id + ' .dropAreaBlock'),
			img = img ? img : $(id + ' img'),
			top = bl.offset().top - top1,
			left = bl.offset().left - left1,
			width = img.width(),
			height = img.height(),
			w = bl.width(),
			h = bl.height();
		img.removeAttr('width').removeAttr('height').css({
			width: '',
			height: ''
		});
		var o_width = img.width(),
			o_height = img.height();
		img.attr('width', width + 'px').attr('height', height + 'px')
		var p_width = 100 - ((width / o_width) * 100),
			p_height = 100 - ((height / o_height) * 100);
		var r_top = top + ((p_height * top) / 100),
			r_left = left + ((p_width * left) / 100),
			r_width = w + ((p_width * w) / 100),
			r_height = h + ((p_height * h) / 100);
		return {
			top: Math.round(r_top),
			left: Math.round(r_left),
			width: Math.round(r_width),
			height: Math.round(r_height)
		};
	}
};

function addTimer(name, callback, time) {
	if (SuraTimers[name]) removeTimer(name);
	SuraTimers[name] = setTimeout(function () {
		callback();
		delete SuraTimers[name];
	}, time);
}

function removeTimer(name) {
	if (!SuraTimers[name]) return;
	if (isArray(name)) {
		for (var i = 0; i <= name.length; i++) removeTimer(name[i]);
		return;
	}
	clearTimeout(SuraTimers[name]);
	delete SuraTimers[name];
}

function declOfNum(number, titles) {
	var cases = [2, 0, 1, 1, 1, 2];
	return titles[(number % 100 > 4 && number % 100 < 20) ? 2 : cases[(number % 10 < 5) ? number % 10 : 5]];
}

var opened_title_html = 0;
function titleHtml(p){
	return showTooltip(document.getElementById(p.id), {text: p.text});
}


function showTooltip(el, opt) {
	if (el.ttTimer) {
		clearTimeout(el.ttTimer);
		el.ttTimer = 0;
		return;
	}
	if (el.err_tip) return;
	if (el.tt) {
		if (el.tt.showing || el.tt.load) return;
		if (el.tt.show_timer) {
			clearTimeout(el.tt.show_timer);
			el.tt.show_timer = 0;
		}
	}
	try {
		el.tt.el.style.display = 'block';
		if (el.tt.el.scrollHeight > 0) var is_bl = true;
		else var is_bl = false;
		el.tt.el.style.display = 'none';
	} catch (e) {
		var is_bl = false;
	}
	if (!is_bl) {
		var tt = document.createElement('div');
		tt.className = 'titleHtml  no_center' + (opt.className ? ' ' + opt.className : '');
		tt.innerHTML = '<div dir="auto" style="position: relative">' + opt.text + '<div class="black_strelka"></div></div>';
		document.body.appendChild(tt);
		el.tt = {};
		el.tt.opt = opt;
		el.tt.el = tt;
		if (!opt.shift) opt.shift = [0, 0, 0];
		el.tt.shift = opt.shift;
		el.tt.show = function () {
			if (this.tt.showing) return;
			this.tt.show_timer = 0;
			var ttobj = $(el.tt.el),
				ttw = ttobj.width(),
				tth = ttobj.height(),
				st = window.scrollY,
				obj = $(this),
				pos = obj.offset(),
				elh = obj.height();
			if ((pos.top - tth - this.tt.opt.shift[1]) < st || el.tt.opt.onBottom) {
				ttobj.addClass('down');
				var top = pos.top + (opt.shift[2]) + elh,
					down = true;
			} else {
				ttobj.removeClass('down');
				var top = pos.top - (opt.shift[1]) - tth,
					down = false;
			}
			ttobj.css({
				top: (top - 10) + 'px',
				left: (pos.left + (opt.shift[0])) + 'px'
			}).fadeIn(100);
			if (this.tt.opt.slide) {
				if (down) ttobj.css('margin-top', (this.tt.opt.slide + elh) + 'px');
				else ttobj.css('margin-top', '-' + this.tt.opt.slide + 'px');
				ttobj.animate({
					marginTop: 0
				}, this.tt.opt.atime);
			}
			this.tt.showing = true;
		}.bind(el);
		el.tt.destroy = function () {
			var obj = $(el);
			obj.unbind('mouseout');
			clearTimeout(el.ttTimer);
			clearTimeout(this.tt.show_timer);
			$(el.tt.el).remove();
			el.tt = false;
		}.bind(el);
		function tooltipout(e, fast) {
			var hovered = $('div:hover');
			if (!fast && this.tt.opt.nohide && (hovered.index(this) != -1 || hovered.index(this.tt.el) != -1 || (this.tt.opt.check_parent && hovered.index($(this.parentNode)) != -1))) return;
			if (this.tt.show_timer) {
				clearTimeout(this.tt.show_timer);
				this.tt.show_timer = false;
			}
			if (!this.tt.showing) return;
			var time = fast ? 0 : (this.tt.opt.hideWt || 0),
				_s = this;
			this.ttTimer = setTimeout(function () {
				var tt_el = $(_s.tt.el);
				tt_el.fadeOut(100);
				_s.tt.showing = false;
				_s.ttTimer = false;
			}, time);
		}
		el.tt.hide = tooltipout.bind(el, false);
		$(el).mouseout(tooltipout.bind(el, false));
		if (opt.nohide) $(el.tt.el).bind('mouseover', showTooltip.bind(el, el, opt)).mouseout(tooltipout.bind(el, false));
		if (opt.url) {
			el.tt.load = true;
			$.post(opt.url, opt.data, function (d) {
				if (d == 'fail') {
					el.tt.destroy();
					el.err_tip = true;
					return;
				}
				el.tt.el.innerHTML = d + '<div class="black_strelka"></div>';
				el.tt.load = false;
				if (el.tt.opt.complete) el.tt.opt.complete(el);
			});
			return;
		}
	}

	el.tt.show_timer = setTimeout(el.tt.show.apply(el), opt.showWt || 0);
}

function cancelEvent(event) {
	event = (event || window.event);
	if (!event) return false;
	while (event.originalEvent) event = event.originalEvent;
	if (event.preventDefault) event.preventDefault();
	if (event.stopPropagation) event.stopPropagation();
	event.cancelBubble = true;
	event.returnValue = false;
	return false;
}

function set_cookie(name, value, exp_y, exp_m, exp_d) {
	var cookie_string = name + "=" + escape(value);
	if (exp_y) {
		var expires = new Date(exp_y, exp_m, exp_d);
		cookie_string += "; expires=" + expires.toGMTString();
	}
	document.cookie = cookie_string;
}

function get_cookie(cookie_name) {
	var results = document.cookie.match('(^|;) ?' + cookie_name + '=([^;]*)(;|$)');
	if (results) return (unescape(results[2]));
	else return false;
}

function delete_cookie(cookie_name) {
	var cookie_date = new Date();
	cookie_date.setTime(cookie_date.getTime() - 1);
	document.cookie = cookie_name += "=; expires=" + cookie_date.toGMTString();
}


function isArray(obj) {
	return Object.prototype.toString.call(obj) === '[object Array]';
}


function download(data, strFileName, strMimeType) {
	var self = window,
		u = "application/octet-stream",
		m = strMimeType || u,
		x = data,
		D = document,
		a = D.createElement("a"),
		z = function (a) {
			return String(a);
		},
		B = self.Blob || self.MozBlob || self.WebKitBlob || z,
		BB = self.MSBlobBuilder || self.WebKitBlobBuilder || self.BlobBuilder,
		fn = strFileName || "download",
		blob,
		b,
		ua,
		fr;
	if (String(this) === "true") {
		x = [x, m];
		m = x[0];
		x = x[1];
	}

	if (String(x).match(/^data\:[\w+\-]+\/[\w+\-]+[,;]/)) {
		return navigator.msSaveBlob ?
			navigator.msSaveBlob(d2b(x), fn) :
			saver(x);
	}
	try {
		blob = x instanceof B ?
			x :
			new B([x], {
				type: m
			});
	} catch (y) {
		if (BB) {
			b = new BB();
			b.append([x]);
			blob = b.getBlob(m);
		}
	}
	function d2b(u) {
		var p = u.split(/[:;,]/),
			t = p[1],
			dec = p[2] == "base64" ? atob : decodeURIComponent,
			bin = dec(p.pop()),
			mx = bin.length,
			i = 0,
			uia = new Uint8Array(mx);
		for (i; i < mx; ++i) uia[i] = bin.charCodeAt(i);
		return new B([uia], {
			type: t
		});
	}
	function saver(url, winMode) {
		if ('download' in a) {
			a.href = url;
			a.setAttribute("download", fn);
			a.innerHTML = "downloading...";
			D.body.appendChild(a);
			setTimeout(function () {
				a.click();
				D.body.removeChild(a);
				if (winMode === true) {
					setTimeout(function () {
						self.URL.revokeObjectURL(a.href);
					}, 250);
				}
			}, 66);
			return true;
		}
		var f = D.createElement("iframe");
		D.body.appendChild(f);
		if (!winMode) {
			url = "data:" + url.replace(/^data:([\w\/\-\+]+)/, u);
		}
		f.src = url;
		setTimeout(function () {
			D.body.removeChild(f);
		}, 333);
	}
	if (navigator.msSaveBlob) {
		return navigator.msSaveBlob(blob, fn);
	}
	if (self.URL) {
		saver(self.URL.createObjectURL(blob), true);
	} else {
		if (typeof blob === "string" || blob.constructor === z) {
			try {
				return saver("data:" + m + ";base64," + self.btoa(blob));
			} catch (y) {
				return saver("data:" + m + "," + encodeURIComponent(blob));
			}
		}
		fr = new FileReader();
		fr.onload = function (e) {
			saver(this.result);
		};
		fr.readAsDataURL(blob);
	}
	return true;
}


//CHECKBOX
var myhtml = {
	checkbox: function(id){
		const name = '#'+id;
		$('#'+id).addClass('html_checked');

		if(ge('checknox_'+id)){
			myhtml.checkbox_off(id);
		} else {
			$(name).append('<div id="checknox_'+id+'"><input type="hidden" id="'+id+'" /></div>');
			$(name).val('1');
		}
	},
	checkbox_off: function(id){
		const name = '#'+id;
		$('#checknox_'+id).remove();
		$(name).removeClass('html_checked');
		$(name).val('');
	},
	checked: function(arr){
		$.each(arr, function(){
			myhtml.checkbox(this);
		});
	},
	title: function(id, text, prefix_id, pad_left){
		if(!pad_left)
			pad_left = 5;
		let sub_tooltip = $('#'+prefix_id+id);


		$("body").append('<div id="js_title_'+prefix_id+id+'" class="js_titleRemove"><div id="easyTooltip">'+text+'</div><div class="tooltip"></div></div>');
		xOffset = sub_tooltip.offset().left+pad_left;
		yOffset = sub_tooltip.offset().top-32;

		$('#js_title_'+prefix_id+id)
			.css("position","absolute")
			.css("top", yOffset+"px")
			.css("left", xOffset+"px")
			.css("z-index","1000")
			.fadeIn('fast');
		sub_tooltip.mouseout(function(){
			$('.js_titleRemove').remove();
		});
	},
	title_close: function(id){
		$('#js_title_'+id).remove();
	},
	updateAjaxNav: function(gc, pref, num, page){
		$.get('/updateAjaxNav', {gcount: gc, pref: pref, num: num, page:page}, function(data){
			$('#nav').html(data);
		});
	},
	scrollTop: function(){
		$('.scroll_fix_bg').hide();
		$(window).scrollTop(0);
	}
}

