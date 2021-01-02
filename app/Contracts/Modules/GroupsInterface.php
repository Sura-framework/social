<?php
/*
 * Copyright (c) 2021. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace App\Contracts\Modules;

use Exception;

interface GroupsInterface
{
    /**
     * Отправка сообщества БД
     */
    public function send();

    /**
     *  Выход из сообщества
     */
    public function logout();

    /**
     * Страница загрузки главного фото сообщества
     * @param $params
     */
    public function load_photo_page($params);

    /**
     * Загрузка и изминение главного фото сообщества
     * @param $params
     * @throws Exception
     */
    public function loadphoto($params);

    /**
     * Удаление фото сообщества
     * @param $params
     */
    public function delphoto($params);

    /**
     * Вступление в сообщество
     */
    public function login();

    /**
     * Страница добавления контактов
     * @param $params
     */
    public function addfeedback_pg($params);

    /**
     * Добавления контакт в БД
     * @param $params
     */
    public function addfeedback_db($params);

    /**
     * Удаление контакта из БД
     * @param $params
     */
    public function delfeedback($params);

    /**
     * Выводим фотографию юзера при указании ИД страницы
     * @param $params
     */
    public function checkFeedUser($params);

    /**
     * Сохранение отредактированых данных контакт в БД
     * @param $params
     */
    public function editfeeddave($params);

    /**
     * Все контакты (БОКС)
     * @param $params
     */
    public function allfeedbacklist($params);

    /**
     * Сохранение отредактированных данных группы
     * @param $params
     */
    public function saveinfo($params);

    /**
     * Выводим информацию о пользователе которого будем делать админом
     * @param $params
     */
    public function new_admin($params);

    /**
     * Запись нового админа в БД
     * @param $params
     */
    public function send_new_admin($params);

    /**
     * Удаление админа из БД
     * @param $params
     */
    public function deladmin($params);

    /**
     * Добавление записи на стену
     *
     * @param $params
     * @return string
     */
    public function wall_send($params): string;

    /**
     * Добавление комментария к записи
     * @param $params
     */
    public function wall_send_comm(&$params);

    /**
     * Удаление записи
     * @param $params
     */
    public function wall_del($params);

    /**
     * Показ всех комментариев к записи
     * @param $params
     */
    public function all_comm($params);

    /**
     * Страница загрузки фото в сообщество
     * @param $params
     */
    public function photos($params);

    /**
     * Выводим инфу о видео при прикриплении видео на стену
     * @param $params
     */
    public function select_video_info($params);

    /**
     * Ставим мне нравится
     * @param $params
     * @return string
     */
    public function wall_like_yes($params): string;

    /**
     * Убераем мне нравится
     * @param $params
     * @return string
     */
    public function wall_like_remove($params): string;

    /**
     * Выводим последних 7 юзеров кто поставил "Мне нравится"
     * @param $params
     */
    public function wall_like_users_five($params);

    /**
     * Выводим всех юзеров которые поставили "мне нравится"
     * @param $params
     * @return string
     * @throws Exception
     */
    public function all_liked_users($params): string;

    /**
     * Рассказать друзьям "Мне нравится"
     * @param $params
     */
    public function wall_tell($params);

    /**
     * Показ всех подпискок
     * @param $params
     */
    public function all_people($params);

    /**
     * Показ всех сообщества юзера на которые он подписан (BOX)
     * @param $params
     */
    public function all_groups_user($params);

    /**
     * Одна запись со стены
     * @param $params
     */
    public function wallgroups($params);

    /**
     * Закрипление записи
     * @param $params
     */
    public function fasten($params);

    /**
     * Убераем фиксацию
     * @param $params
     */
    public function unfasten($params);

    /**
     * Загрузка обложки
     * @param $params
     */
    public function upload_cover($params);

    /**
     * Сохранение новой позиции обложки
     * @param $params
     */
    public function savecoverpos($params);

    public function delcover($params);

    public function invitebox($params);

    public function invitesend($params);

    public function invites($params);

    public function invite_no($params);

    /**
     * Вывод всех сообществ
     * @param $params
     * @return string
     * @throws Exception
     */
    public function index($params): string;

    /**
     * Вывод всех сообществ
     * @param $params
     * @return bool|string
     * @throws Exception
     */
    public function admin(&$params): bool|string;

    public function edit_main(): string;

    public function edit_users();

    public function edit(): string;
}