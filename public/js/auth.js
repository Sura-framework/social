//REG
var reg = {
	step1: function(){
		$('#step2').hide();
		$('#step3').hide();
		var name = $('#name').val();
		var lastname = $('#lastname').val();
		if(name !== 0){
			if(isValidName(name)){
				if(lastname !== 0){
					if(isValidName(lastname)){
						$('#step1').hide();
						$('#step2').show();
						$('#reg_lnk').attr('onClick', '');
					} else {
						setErrorInputMsg('lastname');
						$('#err').show().html(lang_nosymbol);
					}
				} else {
					setErrorInputMsg('lastname');
					$('#err').show().html(lang_empty);
				}
			} else {
				setErrorInputMsg('name');
				$('#err').show().html(lang_nosymbol);
			}
		} else {
			setErrorInputMsg('name');
			$('#err').show().html(lang_empty);
		}
	},
	step2: function(){
		$('#step2').hide();
		$('#step3').show();
	},
	finish: function(){
		var email = $('#email').val();
		var new_pass = $('#new_pass').val();
		var new_pass2 = $('#new_pass2').val();
		var rndval = new Date().getTime(); 
		if(email !== 0 && isValidEmailAddress(email)){
			if(new_pass !== 0 && new_pass.length >= 6){
				if(new_pass === new_pass2){
					Box.Show('sec_code', 280, 'Введите код с картинки:', '<div style="padding:20px;text-align:center"><div class="cursor_pointer" onClick="updateCode(); return false"><div id="sec_code"><img src="/antibot/?rndval=' + rndval + '" alt="" title="Показать другой код" width="120" height="50" /></div></div><div id="code_loading"><input type="text" id="val_sec_code" class="inpst" maxlength="6" style="margin-top:10px;width:110px" /></div></div>', lang_box_canсel, 'Отправить', 'checkCode(); return false;');
					$('#val_sec_code').focus();
				} else {
					setErrorInputMsg('new_pass2');
					$('#err2').show().html('Оба введенных пароля должны быть идентичны.');
				}
			} else {
				setErrorInputMsg('new_pass');
				$('#err2').show().html('Длина пароля должна быть не менее 6 символов.');
			}
		} else {
			setErrorInputMsg('email');
			$('#err2').show().html(lang_bad_email);
		}
	},
	send: function(sec_code){
		var email = $('#email').val();
		var new_pass = $('#new_pass').val();
		var new_pass2 = $('#new_pass2').val();
		var name = $('#name').val();
		var lastname = $('#lastname').val();
		var val_sec_code = $("#val_sec_code").val();
		var sex = $("#sex").val();
		var day = $("#day").val();
		var month = $("#month").val();
		var year = $("#year").val();
		var country = $("#country").val();
		var city = $("#select_city").val();
		$.post('/register/', {
				name: name,
				lastname: lastname,
				email: email,
				sex: sex,
				day: day,
				month: month,
				year: year,
				country: country,
				city: city,
				password_first: new_pass,
				password_second: new_pass2,
				sec_code: sec_code
			}, function(d){
			var exp = d.split('|');
			if(exp[0] == 'ok'){
				//window.location = '/u'+exp[1]+'after';
				window.location = '/u'+exp[1];
				//window.location = '/';
			} else if(exp[0] == 'err_mail'){
				$('#err2').show().html('Пользователь с таким E-Mail адресом уже зарегистрирован.');
				Box.Close('sec_code');
			} else if(exp[0] == 'error' && exp['1'] == 'no_val'){
				if (exp['2'] == 'mail'){
					let err_name = 'Некоректный E-Mail.';
					$('#err2').show().html(err_name);
					Box.Info('boxerr', 'Ошибка', err_name, 300);
				}else if (exp['2'] == 'name'){
					let err_name = 'Неправильно введено имя.';
					$('#err2').show().html(err_name);
					Box.Info('boxerr', 'Ошибка', err_name, 300);
				}else if (exp['2'] == 'surname'){
					let err_name = 'Неправильно введена фамилия.';
					$('#err2').show().html(err_name);
					Box.Info('boxerr', 'Ошибка', err_name, 300);
				}else if (exp['2'] == 'password'){
					let err_name = 'Неправильно введен пароль.';
					$('#err2').show().html(err_name);
					Box.Info('boxerr', 'Ошибка', err_name, 300);
				}else{
					$('#err2').show().html('Неправильно введены данные.');
					Box.Info('boxerr', 'Ошибка', 'Неизвестная ошибка', 300);
				}
				Box.Close('sec_code');
			} else {
				Box.Info('boxerr', 'Ошибка', 'Неизвестная ошибка', 300);
				Box.Close('sec_code');
			}
		});
	}
}
//RESTORE
var restore = {
	next: function(){
		var email = $('#email').val();
		if(email != 0 && email != 'Ваш электронный адрес' && isValidEmailAddress(email)){
			butloading('send', '32', 'disabled', '');
			$.post('/restore/next/', {email: email}, function(data){
				if(data == 'no_user'){
					$('#err').show().html('Пользователь <b>'+email+'</b> не найден.<br />Пожалуйста, убедитесь, что правильно ввели e-mail.');
				} else {
					var exp = data.split('|');
					$('#step1').hide();
					$('#step2').show();
					$('#c_src').attr('src', exp[1]);
					$('#c_name').html('<b>'+exp[0]+'</b>');
				}
			});
			butloading('send', '32', 'enabled', 'Далее');
		} else
			setErrorInputMsg('email');
	},
	send: function(){
		var email = $('#email').val();
		butloading('send2', '129', 'disabled', '');
		$.post('/restore/send/', {email: email}, function(d){
			$('#step2').hide();
			$('#step3').show();
		});
	},
	finish: function(){
		var new_pass = $('#new_pass').val();
		var new_pass2 = $('#new_pass2').val();
		var hash = $('#hash').val();
		if(new_pass !== 0 && new_pass !== 'Новый пароль'){
			if(new_pass2 !== 0 && new_pass2 !== 'Повторите еще раз новый пароль'){
				if(new_pass === new_pass2){
					if(new_pass.length >= 6){
						$('#err').hide();
						butloading('send', '43', 'disabled', '');
						$.post('/restore/finish/', {new_pass: new_pass, new_pass2: new_pass2, hash: hash}, function(d){
							$('#step1').hide();
							$('#step2').show();
						});
					} else
						$('#err').show().html('Длина пароля должна быть не менее 6 символов.');
				} else
					$('#err').show().html('Оба введенных пароля должны быть идентичны.');
			} else
				setErrorInputMsg('new_pass2');
		} else
			setErrorInputMsg('new_pass');
	}
}
//LOGIN
var login = {
	send: function(){
		var email = $('#log_email').val();
		var pass = $('#log_password').val();
		$.post('/login/', {
			login: '',
			email: email,
			pass: pass,
		}, function(d){
			console.log(d);
			var exp = d.split('|');
			if(exp[0] == 'ok'){
				// window.location = '/u'+exp[1]+'after';
				window.location = '/u'+exp[1];
			} else if(exp[0] == 'err_mail'){
				$('#err2').show().html('Пользователь с таким E-Mail адресом уже зарегистрирован.');
				Box.Close('sec_code');
			} else if(exp[0] == 'error' && exp['1'] == 'no_val'){
				if (exp['2'] == 'mail'){
					let err_name = '<div class="alert alert-danger" role="alert">' +
						'Некоректный E-Mail.</div>';
					$('#err2').show().html(err_name);
					// Box.Info('boxerr', 'Ошибка', err_name, 300);
				}else if (exp['2'] == 'password'){
					let err_name = '<div class="alert alert-danger" role="alert">' +
						'<a href="/restore" onClick="Page.Go(this.href); return false">Забыли пароль?</a></div>';
					$('#err2').show().html(err_name);
					// Box.Info('boxerr', 'Ошибка', err_name, 300);
				}else if (exp['2'] == 'no_user'){
					let err_name = '<div class="alert alert-danger" role="alert">' +
						'<a href="/restore" onClick="Page.Go(this.href); return false">Забыли пароль?</a></div>';
					$('#err2').show().html(err_name);
					// Box.Info('boxerr', 'Ошибка', err_name, 300);
				}else{
					$('#err2').show().html('Неправильно введены данные.');
					Box.Info('boxerr', 'Ошибка', 'Неизвестная ошибка', 300);
				}
				Box.Close('sec_code');
			} else {
				Box.Info('boxerr', 'Ошибка', 'Неизвестная ошибка', 300);
				Box.Close('sec_code');
			}
		});
	}
}
function isValidName(xname){
	var pattern = new RegExp(/^[a-zA-Zа-яА-Я]+$/);
 	return pattern.test(xname);
}
function isValidEmailAddress(emailAddress) {
 	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
 	return pattern.test(emailAddress);
}
function updateCode(){
	var rndval = new Date().getTime();
	$('#sec_code').html('<img src="/antibot/?rndval=' + rndval + '" alt="" title="Показать другой код" width="120" height="50" />');
}
function checkCode(){
	var val_sec_code = $("#val_sec_code").val();
	$('#code_loading').html('<img src="/images/loading_mini.gif" style="margin-top:21px" />');
	$.get('/antibot/code/?user_code='+val_sec_code, function(data){
		if(data == 'ok'){
			auth.send(val_sec_code);
		} else {
			updateCode();
			$('#code_loading').html('<input type="text" id="val_sec_code" class="inpst" maxlength="6" style="margin-top:10px;width:110px" />');
			$('#val_sec_code').val('');
			$('#val_sec_code').focus();
		}
	});
}