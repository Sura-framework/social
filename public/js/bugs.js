var bugs = {
	box: function(){
    viiBox.start();
	$.post('/bugs/add_box/', function(d){
	  viiBox.win('bugs', d);
	});
  },
	create: function(){
	var title= $('#title').val();
	var text= $('#text').val();
	// if(loading_photo_pins){
		$.post('/bugs/create/', {title: title, text: text, file: loaded_pins_name}, function(data){
			if(data == 'antispam_err'){
			    AntiSpam('bugs');
				Box.Info('err', 'AntiSpam:', 'Извините но на сегодня вы исчерпали лимит добавления багов!');
				return false;
			}
			
			Box.Info('inf', 'Информация', 'Баг отправлен');
			viiBox.clos('create', 1);
			setTimeout("location.href = '/bugs/'", 1500);
		});
		// }else Box.Info('err', 'Ошибка', 'Вы не загрузили фотографию');
  	},
  	Delete: function(id){
		$.post('/bugs/delete/', {id: id});
		Page.Go('/bugs/');
	},
    view: function(id){
		viiBox.start();
		$.post('/bugs/view/', {id: id}, function(d){
			if(d == 'err') Box.Info('err', lang_61, lang_215);
			else viiBox.win('view', d);
		});
	}
};
