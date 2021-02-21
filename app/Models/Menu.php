<?php

declare(strict_types=1);

namespace App\Models;


use Sura\Libs\Langs;
use Sura\Libs\Registry;
use Sura\Menu\Html;
use Sura\Menu\Link;

/**
 * Class Menu
 * TODO add full translate
 *
 * @package App\Models
 */
class Menu
{
    /**
     * @return string
     */
    public static function settings(): string
    {
        $lang = langs::get_langs();
        $go = 'Page.Go(this.href); return false;';
        return \Sura\Menu\Menu::new()
            ->addClass('navigation nav flex-column text-left pl-2')
            ->add(Link::to('/settings/', $lang['settings'])->setAttribute('onClick', $go))
            ->add(Link::to('/settings/general/', $lang['settings_general'])->setAttribute('onClick', $go))
            ->add(Link::to('/settings/privacy/', $lang['settings_privacy'])->setAttribute('onClick', $go))
            ->add(Link::to('/settings/blacklist/', $lang['blacklist'])->setAttribute('onClick', $go))
            ->add(Html::raw('<hr>'))
//            ->add(Link::to('/settings/', $lang['notify'])->setAttribute('onClick', $go))
//            ->add(Html::raw('<hr>'))
            ->add(Link::to('/balance/', $lang['balance'])->setAttribute('onClick', $go))
            ->add(Link::to('/balance/invite/', $lang['friend_invite'])->setAttribute('onClick', $go))
            ->add(Link::to('/balance/invited/', $lang['friend_invited'])->setAttribute('onClick', $go))
//            ->add(Html::empty())
            ->setActive($_SERVER['REQUEST_URI'])
            ->wrap('div', ['class' => 'wrapper'])
            ->render();
    }

    /**
     * @return string
     */
    public static function public_edit(): string
    {
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($path['4']))
        {
            $id = $path['4'];
        }else
        {
            $id = $path['3'];
        }
//        $lang = langs::get_langs();
        $go = 'Page.Go(this.href); return false;';
        return  \Sura\Menu\Menu::new()
            ->addClass('navigation nav flex-column text-left pl-2')
            ->add(Link::to('/public/edit/'.$id, 'Информация')->setAttribute('onClick', $go))
            ->add(Link::to('/public/edit/users/'.$id, 'Участники')->setAttribute('onClick', $go))
            ->add(Link::to('/public/edit/users/'.$id.'/admin', 'Руководители')->setAttribute('onClick', $go))
            ->add(Link::to('/public/edit/blacklist/'.$id, 'Черный список')->setAttribute('onClick', $go))
            ->add(Link::to('/public/edit/link/'.$id, 'Ссылки')->setAttribute('onClick', $go))
            ->setActive($_SERVER['REQUEST_URI'])
            ->wrap('div', ['class' => 'wrapper'])
            ->render();
    }

    /**
     * FriendsController
     *
     * @return string
     */
    public static function friends(): string
    {
        $path = explode('/', $_SERVER['REQUEST_URI']);
//        $id = $path['2'];
        $user_info = Registry::get('user_info');
        $lang = langs::get_langs();
        $go = 'Page.Go(this.href); return false;';

        if (!is_numeric($path['2']) || $path['2'] == $user_info['user_id'])
        {
            $id = $user_info['user_id'];
            return \Sura\Menu\Menu::new()
                ->addClass('navigation nav text-left pl-2')
                ->add(Link::to('/friends/'.$id.'/', $lang['friends_all'])->setAttribute('onClick', $go))
                ->add(Link::to('/friends/'.$id.'/online/', $lang['friends_online'])->setAttribute('onClick', $go))
                ->add(Link::to('/friends/requests/', $lang['friends_requests'])->setAttribute('onClick', $go))
                ->add(Link::to('/friends/'.$id.'/common/', $lang['friends_common'])->setAttribute('onClick', $go))
                ->add(Link::to('/u'.$id, $lang['to_page'])->setAttribute('onClick', $go))
                ->setActive($_SERVER['REQUEST_URI'])
                ->wrap('div', ['class' => 'wrapper'])
                ->render();
        }else{
            $id = $path['2'];
            return \Sura\Menu\Menu::new()
                ->addClass('navigation nav text-left pl-2')
                ->add(Link::to('/friends/'.$id.'/', $lang['friends_all'])->setAttribute('onClick', $go))
                ->add(Link::to('/friends/'.$id.'/online/', $lang['friends_online'])->setAttribute('onClick', $go))
                ->add(Link::to('/friends/'.$id.'/common/', $lang['friends_common'])->setAttribute('onClick', $go))
                ->add(Link::to('/u'.$id, $lang['to_page'])->setAttribute('onClick', $go))
                ->setActive($_SERVER['REQUEST_URI'])
                ->wrap('div', ['class' => 'wrapper'])
                ->render();
        }

    }

    public static function bugs(): string
    {
//        $lang = langs::get_langs();
        $go = 'Page.Go(this.href); return false;';

        return \Sura\Menu\Menu::new()
            ->addClass('navigation nav text-left pl-2')
            ->add(Link::to('/bugs/', 'Все баги')->setAttribute('onClick', $go))
            ->add(Link::to('/bugs/open/', 'Открытые')->setAttribute('onClick', $go))
            ->add(Link::to('/bugs/complete/', 'Исправленные')->setAttribute('onClick', $go))
            ->add(Link::to('/bugs/close/', 'Отклоненные')->setAttribute('onClick', $go))
            ->add(Link::to('/bugs/my/', 'Мои баги')->setAttribute('onClick', $go))
            ->setActive($_SERVER['REQUEST_URI'])
            ->wrap('div', ['class' => 'wrapper'])
            ->render();
    }
}