<?php


namespace Sura\Models;


class Admin
{
    public static function modules(){
       return array(
           array(
                'name' =>'Настройка системы',
               'description' => 'Настройка общих параметров скрипта, а также настройка системы безопасности скрипта',
               'img' => 'settings.png',
                'link' => 'settings',
            ),
           array(
               'name' =>'Управление БД',
               'description' => 'Резервное копирование и восстановление базы данных',
               'img' => 'db.png',
               'link' => 'db',
           ),
           array(
               'name' =>'Личные настройки',
               'description' => 'Управление и настройка вашего личного профиля пользователя',
               'img' => 'mysettings.png',
               'link' => 'mysettings',
           ),
           array(
               'name' =>'Пользователи',
               'description' => 'Управление зарегистрированными на сайте пользователями, редактирование их профилей и блокировка аккаунта',
               'img' => 'users.png',
               'link' => 'users',
           ),
           array(
               'name' =>'Доп. поля профилей',
               'description' => 'В данном разделе проводится настройка дополнительных полей профиля пользователей',
               'img' => 'xfields.png',
               'link' => 'xfields',
           ),
           array(
               'name' =>'Видео',
               'description' => 'Управление видеозаписями, редактирование и удаление',
               'img' => 'video.png',
               'link' => 'video',
           ),
           array(
               'name' =>'Музыка',
               'description' => 'Управление аудиозаписями, редактирование и удаление',
               'img' => 'music.png',
               'link' => 'music',
           ),
           array(
               'name' =>'Альбомы',
               'description' => 'Управление альбомами, редактирование и удаление',
               'img' => 'photos.png',
               'link' => 'photos',
           ),
           array(
               'name' =>'Подарки',
               'description' => 'Управление подарками на сайте, добавление, редактирование и удаление',
               'img' => 'gifts.png',
               'link' => 'gifts',
           ),
           array(
               'name' =>'Сообщества',
               'description' => 'Управление сообществами, редактирование и удаление',
               'img' => 'groups.png',
               'link' => 'groups',
           ),
           array(
               'name' =>'Жалобы',
               'description' => 'Список жалоб, поступивших от посетителей сайта на фотографии, записи, видеозаписи или заметки',
               'img' => 'report.png',
               'link' => 'report',
           ),
           array(
               'name' =>'Шаблоны сообщений',
               'description' => 'Настройка шаблонов E-Mail сообщений, которые отсылает скрипт с сайта при уведомлении.',
               'img' => 'mail_tpl.png',
               'link' => 'mail_tpl',
           ),
           array(
               'name' =>'Рассылка сообщений',
               'description' => 'Создание и массовая отправка E-Mail сообщений, для зарегистрированных пользователей',
               'img' => 'mail.png',
               'link' => 'mail',
           ),
           array(
               'name' =>'Фильтр по: IP',
               'description' => 'Блокировка доступа на сайт для определенных IP',
               'img' => 'ban.png',
               'link' => 'ban',
           ),
           array(
               'name' =>'Поиск и Замена',
               'description' => 'Быстрый поиск и замена определенного текста по всей базе данных',
               'img' => 'search.png',
               'link' => 'search',
           ),
           array(
               'name' =>'Статические страницы',
               'description' => 'Создание и редактирование страниц, которые как правило редко изменяются и имеют постоянный адрес',
               'img' => 'static.png',
               'link' => 'static',
           ),
           array(
               'name' =>'Антивирус',
               'description' => 'Проверка папок и файлов скрипта на наличие подозрительных файлов',
               'img' => 'antivirus.png',
               'link' => 'antivirus',
           ),
           array(
               'name' =>'Логи посещений',
               'description' => 'Вывод IP и браузера пользователей при последнем входе на сайт',
               'img' => 'logs.png',
               'link' => 'logs',
           ),
           array(
               'name' =>'Страны',
               'description' => 'Добавление, удаление и редактирование стран',
               'img' => 'country.png',
               'link' => 'country',
           ),
           array(
               'name' =>'Города',
               'description' => 'Добавление, удаление и редактирование городов',
               'img' => 'city.png',
               'link' => 'city',
           ),
           array(
               'name' =>'ads',
               'description' => 'ads',
               'img' => 'mysettings.png',
               'link' => 'ads',
           ),
           array(
               'name' =>'stats',
               'description' => 'stats',
               'img' => 'static.png',
               'link' => 'stats',
           ),


        );

    }
}