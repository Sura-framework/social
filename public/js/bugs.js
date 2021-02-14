const bugs = {
    box: function () {
        viiBox.start();
        $.post('/bugs/add_box/', function (d) {
            viiBox.win('bugs', d.row);
        });
    },
    create: function () {
        const title = $('#title').val();
        const text = $('#text').val();
        // if(loading_photo_pins){
        // 	let loaded_pins_name = '';
        $.post('/bugs/create/', {title: title, text: text, file: loaded_pins_name}, function (data) {
            if (data == 'antispam_err') {
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
    Delete: function (id) {
        $.post('/bugs/delete/', {id: id});
        Page.Go('/bugs/');
    },
    view: function (id) {
        viiBox.start();
        $.post('/bugs/view/', {id: id}, function (d) {
            if (d.status === 2) {
                Box.Info('err', lang_61, lang_215);
            } else {
                viiBox.win('view', d.row);
            }
        });
    },
    create_comment: function (rid, for_user_id) {
        let wall_text = $('#fast_text_' + rid).val();
        let status;
        if ($('#fast_status_' + rid)) {
            status = $('#fast_status_' + rid).val();
        } else {
            status = '';
        }

        if (wall_text != 0) {
            butloading('fast_buts_' + rid, 56, 'disabled');

            $.post('/bugs/comments/create/', {
                text: wall_text,
                user_id: for_user_id,
                id: rid,
                status: status
            }, function (data) {
                if (data === 'antispam_err') {
                    AntiSpam('comm');
                    return false;
                }
                if (data === 'err_privacy') {
                    addAllErr(lang_pr_no_title);
                } else {
                    $('#ava_rec_' + rid).addClass('wall_ava_mini'); //добавляем для авы класс wall_ava_mini
                    $('#fast_textarea_' + rid).remove(); //удаляем полей texatra
                    $('#fast_comm_link_' + rid).remove(); //удаляем кнопку комментировать
                    // $('#wall_fast_block_'+rid).html(data); //выводим сам результат
                    $('.wall_fast_text').val(''); //Текстовое значение полей Texatrea делаем 0
                    wall.fast_form_close();
                }
                butloading('fast_buts_' + rid, 56, 'enabled', lang_box_send);
            });
        } else {
            $('#fast_text_' + rid).val('');
            $('#fast_text_' + rid).focus();
        }
    }
};
