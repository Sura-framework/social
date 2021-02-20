const step1 = $('#step1');
const step2 = $('#step2');
const step3 = $('#step3');
const name = $('#name');
const lastname = $('#lastname');
const email = $('#email');
const new_pass = $('#new_pass');
const new_pass2 = $('#new_pass2');
const sex = $("#sex");
const day = $("#day");
const month = $("#month");
const year = $("#year");
const country = $("#country");
const city = $("#select_city");
const rndval = new Date().getTime();

//REG
const reg = {
	step1: function(){
		const step1 = $('#step1');
		const step2 = $('#step2');
		const step3 = $('#step3');

		step2.hide();
		step3.hide();

		if(name !== 0){
			if(!isValidName(name.val())){
				//is-invalid
				// setErrorInputMsg('name');
				name.addClass('is-invalid');
				$('#err_name').show().html(lang_nosymbol);
			}
		} else {
			// setErrorInputMsg('name');
			$('#err_name').show().html(lang_empty);
		}

		if(lastname.val() !== 0){
			if(!isValidName(lastname.val())){
				lastname.addClass('is-invalid');
				$('#err_lastname').show().html(lang_nosymbol);
				// setErrorInputMsg('lastname');
				//$('#err').show().html(lang_nosymbol);
			}
		} else {
			// setErrorInputMsg('lastname');
			$('#err_lastname').show().html(lang_empty);
		}

		if(isValidName(lastname.val()) && isValidName(lastname.val())){
			step1.hide();
			step2.show();
			$('#reg_lnk').attr('onClick', '');
		}
	},
	step2: function(){
		const step2 = $('#step2');
		const step3 = $('#step3');

		//проверить данные
		var sex = $("#sex").val();
		var day = $("#day").val();
		var month = $("#month").val();
		var year = $("#year").val();
		var country = $("#country").val();
		var city = $("#select_city").val();

		step2.hide();
		step3.show();
	},
	finish: function(){
		const rndval = new Date().getTime();
		const email = $('#email');
		const new_pass = $('#new_pass');
		const new_pass2 = $('#new_pass2');

		//isValidPass(new_pass.val()

		if(email.val() == null || !isValidEmailAddress(email.val())){
			// setErrorInputMsg('email');
			email.addClass('is-invalid');
			$('#err_email').show().html(lang_bad_email);
		}else
			email.addClass('is-valid');

		if(isValidPass(new_pass.val())) {
			if(new_pass.val() === new_pass2.val()){
			}else{
				new_pass.addClass('is-invalid');
				new_pass2.addClass('is-invalid');
				$('#err_new_pass2').show().html('Оба введенных пароля должны быть идентичны.');
			}
		}else{
			// setErrorInputMsg('new_pass');
			new_pass.addClass('is-invalid');
			new_pass2.addClass('is-invalid');
			$('#err_new_pass2').show().html('Длина пароля должна быть не менее 8 символов.');
		}

		if (isValidEmailAddress(email.val()) && isValidPass(new_pass.val()) && new_pass.val() === new_pass2.val()){
			Box.Show('sec_code', 280, 'Введите код с картинки:', '<div style="padding:20px;text-align:center">' +
				'<div class="cursor_pointer" onClick="updateCode(); return false"><div id="sec_code"><img src="/antibot/?rndval=' + rndval + '" alt="" title="Показать другой код" width="120" height="50" /></div>' +
				'</div>' +
				'<div id="code_loading"><input type="text" id="val_sec_code" class="inpst" maxlength="6" style="margin-top:10px;width:110px" /></div>' +
				'</div>', lang_box_cancel, 'Отправить', 'checkCode(); return false;');
			$('#val_sec_code').focus();
		}
	},
	send: function(code){
		// var email = $('#email').val();
		// var new_pass = $('#new_pass').val();
		// var new_pass2 = $('#new_pass2').val();
		// var name = $('#name').val();
		// var lastname = $('#lastname').val();

		// var sex = $("#sex").val();
		// var day = $("#day").val();
		// var month = $("#month").val();
		// var year = $("#year").val();
		// var country = $("#country").val();
		// var city = $("#select_city").val();

		const name = $('#name');
		const lastname = $('#lastname');
		const email = $('#email');
		const sex = $("#sex");
		const day = $("#day");
		const month = $("#month");
		const year = $("#year");
		const country = $("#country");
		const city = $("#select_city");
		const new_pass = $('#new_pass');
		const new_pass2 = $('#new_pass2');
		const token = $( "input[name='_mytoken']" );
		$.post('/register/', {
				name: name.val(),
				lastname: lastname.val(),
				email: email.val(),
				sex: sex.val(),
				day: day.val(),
				month: month.val(),
				year: year.val(),
				country: country.val(),
				city: city.val(),
				password_first: new_pass.val(),
				password_second: new_pass2.val(),
				sec_code: code,
				token: token.val(),
			}, function(d){
				d = JSON.parse(d);
			if(d.status === 1){
				//window.location = '/u'+exp[1]+'after';
				window.location = '/u'+d.res;
				//window.location = '/';
			} else if(d.status === 17){
				$('#err2').show().html('Пользователь с таким E-Mail адресом уже зарегистрирован.');
				Box.Close('sec_code');
			} else if(d.status === 9){
				if (d.err.mail) {
					let err_name = 'Некоректный E-Mail.';
					$('#err2').show().html(err_name);
					Box.Info('boxerr', 'Ошибка', err_name, 300);
				}
				if (d.err.user_name) {
					let err_name = 'Неправильно введено имя.';
					$('#err2').show().html(err_name);
					Box.Info('boxerr', 'Ошибка', err_name, 300);
				}
				if (d.err.user_surname) {
					let err_name = 'Неправильно введена фамилия.';
					$('#err2').show().html(err_name);
					Box.Info('boxerr', 'Ошибка', err_name, 300);
				}
				if (d.err.password) {
					let err_name = 'Неправильно введен пароль.';
					$('#err2').show().html(err_name);
					Box.Info('boxerr', 'Ошибка', err_name, 300);
				}
				Box.Close('sec_code');
			}
			else if(d.status === 4){}
			else {
				Box.Info('boxerr', 'Ошибка', 'Неизвестная ошибка', 300);
				Box.Close('sec_code');
			}
		});
	}
}
//RESTORE
const restore = {
	next: function(){
		const step1 = $('#step1');
		const step2 = $('#step2');
		const email = $('#email');

		//var email = $('#email').val();
		if(email.val() !== 0 && email.val() !== 'Ваш электронный адрес' && isValidEmailAddress(email.val())){
			butloading('send', '32', 'disabled', '');
			$.post('/restore/next/', {email: email.val()}, function(data){
				if(data.status === 16){
					$('#err').show().html('Пользователь <b>'+email.val()+'</b> не найден.<br />Пожалуйста, убедитесь, что правильно ввели e-mail.');
				} else {
					step1.hide();
					step2.show();
					$('#c_src').attr('src', data.photo);
					$('#c_name').html('<b>'+data.name+'</b>');
				}
			});
			butloading('send', '32', 'enabled', 'Далее');
		} else
			setErrorInputMsg('email');
	},
	send: function(){
		const step2 = $('#step2');
		const step3 = $('#step3');
		const email = $('#email');

		//var email = $('#email').val();
		butloading('send2', '129', 'disabled', '');
		$.post('/restore/send/', {email: email.val()}, function(d){
			step2.hide();
			step3.show();
		});
	},
	finish: function(){
		const new_pass = $('#new_pass');
		const new_pass2 = $('#new_pass2');
		const step1 = $('#step1');
		const step2 = $('#step2');

		// var new_pass = $('#new_pass').val();
		// var new_pass2 = $('#new_pass2').val();
		const hash = $('#hash');
		if(isValidPass(new_pass.val()) && new_pass.val() !== 'Новый пароль'){
			if(isValidPass(new_pass.val()) && new_pass2.val() !== 'Повторите еще раз новый пароль'){
				if(new_pass.val() === new_pass2.val()){
					if(isValidPass(new_pass.val())){
						$('#err').hide();
						butloading('send', '43', 'disabled', '');
						$.post('/restore/finish/', {new_pass: new_pass.val(), new_pass2: new_pass2.val(), hash: hash.val()}, function(d){
							step1.hide();
							step2.show();
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
const login = {
	send: function(){
		const log_email = $('#log_email');
		const log_password = $('#log_password');
		const token = $( "input[name='_mytoken']" );

		// if (location.protocol == 'http:'){
		// 	window.location.href('https://'+location.host+'/no_ssl/');
		// }

		$.post('https://'+location.host+'/login/', {
			login: '',
			email: log_email.val(),
			pass: log_password.val(),
			token: token.val(),
		}, function(d){
			// d = JSON.parse(d);

			// console.log(d);
			// var exp = d.split('|');
			if(d.status === 1){
				// window.location = '/u'+exp[1]+'after';
				window.location = '/u'+d.res;
			}
			else if(d.status === 2){
				let err_name = '<div class="alert alert-danger" role="alert">' +
					'<a href="/restore" onClick="Page.Go(this.href); return false">Забыли пароль?</a></div>';
				$('#err2').show().html(err_name);

				// if (exp['2'] === 'mail'){
				// 	let err_name = '<div class="alert alert-danger" role="alert">' +
				// 		'Некоректный E-Mail.</div>';
				// 	$('#err2').show().html(err_name);
				// 	// Box.Info('boxerr', 'Ошибка', err_name, 300);
				// }else if (exp['2'] === 'password'){
				// 	let err_name = '<div class="alert alert-danger" role="alert">' +
				// 		'<a href="/restore" onClick="Page.Go(this.href); return false">Забыли пароль?</a></div>';
				// 	$('#err2').show().html(err_name);
				// 	// Box.Info('boxerr', 'Ошибка', err_name, 300);
				// }else if (exp['2'] === 'no_user'){
				// 	let err_name = '<div class="alert alert-danger" role="alert">' +
				// 		'<a href="/restore" onClick="Page.Go(this.href); return false">Забыли пароль?</a></div>';
				// 	$('#err2').show().html(err_name);
				// 	// Box.Info('boxerr', 'Ошибка', err_name, 300);
				// }else{
				// 	$('#err2').show().html('Неправильно введены данные.');
				// 	Box.Info('boxerr', 'Ошибка', 'Неизвестная ошибка', 300);
				// }
				Box.Close('sec_code');
			}
			else if(d.status === 3){
				if (d.err.mail) {
					let err_name = '<div class="alert alert-danger" role="alert">Некоректный E-Mail.</div>';
					$('#err2').show().html(err_name);
					Box.Close('sec_code');
				} else if (d.err.password) {
					$('#err2').show().html('Неправильно введены данные.');
					Box.Close('sec_code');
				}
			}
			else if(d.status === 4){
				$('#err2').show().html('Неправильно введены данные.');
				Box.Close('sec_code');
			}
			else {
				Box.Info('boxerr', 'Ошибка', 'Неизвестная ошибка', 300);
				Box.Close('sec_code');
			}
		});
	}
}
function isValidName(x_name){
	const re = /^[a-zA-Zа-яА-Я]+$/;
	return re.test(String(x_name).toLowerCase());
}
// function isValidEmailAddress(emailAddress) {
//  	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
//  	return pattern.test(emailAddress);
// }
function isValidEmailAddress(emailAddress) {
	const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(String(emailAddress).toLowerCase());
}
function isValidPass(pass) {
	const re = /^[A-Za-z]\w{7,14}$/;
	//const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(String(pass).toLowerCase());
}
function updateCode(){
	//var rndval = new Date().getTime();
	const rndval = new Date().getTime();
	$('#sec_code').html('<img src="/antibot/?rndval=' + rndval + '" alt="" title="Показать другой код" width="120" height="50" />');
}
function checkCode(){
	var val_sec_code = $("#val_sec_code").val();
	$('#code_loading').html('<div class="spinner-border mt-2" role="status"><span class="sr-only">Loading...</span>\</div>');
	$.get('/antibot/code/?user_code='+val_sec_code, function(data){
		//var val_sec_code = $('#val_sec_code');
		if(data.status === 1){
			console.log('ok');
			reg.send(val_sec_code);
		} else {
			updateCode();
			$('#code_loading').html('<input type="text" id="val_sec_code" class="inpst" maxlength="6" style="margin-top:10px;width:110px" />');
			$("#val_sec_code").val('').focus();
			//$("#val_sec_code");
		}
	});
}
//Check inputs
function input_email() {
	const email = $('#email');
	if(email.val() == null || !isValidEmailAddress(email.val())){
		setErrorInputMsg('email');
		email.addClass('is-invalid');
		email.removeClass('is-valid');
		$('#err_email').show().html(lang_bad_email);
	}else{
		//add defined/undefined email
		// $.get('/user/check/?user_email='+email.val(), function(data){
		// 	if(data === 'ok') {
		// 		email.removeClass('is-invalid');
		// 		email.addClass('is-valid');
		// 		$('#err_email').show().html('такой email уже существует');
		// 	}else{
		// 		email.addClass('is-invalid');
		// 		email.removeClass('is-valid');
		// 	}
		// }
		email.removeClass('is-invalid');
		email.addClass('is-valid');
	}
}

function input_pass() {
	const new_pass = $('#new_pass');
	if (isValidPass(new_pass.val())){
		new_pass.addClass('is-valid');
		new_pass.removeClass('is-invalid');
	}else{
		new_pass.addClass('is-invalid');
		new_pass.removeClass('is-valid');
		$('#err_new_pass').show().html('Длина пароля должна быть не менее 8 символов.');
	}
}

function input_pass2() {
	const new_pass = $('#new_pass');
	const new_pass2 = $('#new_pass2');
	if (isValidPass(new_pass2.val())){
		if(new_pass.val() === new_pass2.val()){
			new_pass2.addClass('is-valid');
			new_pass2.removeClass('is-invalid');
		}else{
			new_pass2.addClass('is-invalid');
			new_pass2.removeClass('is-valid');
			$('#err_new_pass2').show().html('Оба введенных пароля должны быть идентичны.');
		}
	}else{
		new_pass2.addClass('is-invalid');
		new_pass2.removeClass('is-valid');
		$('#err_new_pass2').show().html('Длина пароля должна быть не менее 8 символов.');
	}
}

function input_name() {
	const name = $('#name');
	if (name.val() !== null){
		if(!isValidName(name.val())){
			name.addClass('is-invalid');
			name.removeClass('is-valid');
			$('#err_name').show().html(lang_nosymbol);
		}else{
			name.removeClass('is-invalid');
			name.addClass('is-valid');
		}
	}else{
		$('#err_lastname').show().html(lang_empty);
	}
}

function input_lastname() {
	const lastname = $('#lastname');
	if (lastname.val() !== null){
		if(!isValidName(lastname.val())){
			lastname.addClass('is-invalid');
			lastname.removeClass('is-valid');
			$('#err_lastname').show().html(lang_nosymbol);
		}else{
			lastname.removeClass('is-invalid');
			lastname.addClass('is-valid');
		}
	}else{
		$('#err_lastname').show().html(lang_empty);
	}
}

