<?php

return array(
    // Главная
    '/' => 'HomeController@Index',

    '/theme/' => 'HomeController@Theme',
    '/404/' => 'HomeController@Index',
//    '/2'                                           => array('method' => 'HomeController@Index'),
    // Регистрация
    '/reg/' => 'HomeController@Index',
    '/test/' => 'HomeController@Test',


    '/register/' => 'AuthController@end_register',
    //Воостановление пароля
    '/restore/' => 'AuthController@restore',
    '/restore/next/' => 'AuthController@restore_next',
    '/restore/send/' => 'AuthController@restore_send',
    '/restore/finish/' => 'AuthController@restore_finish',
    '/restore/prefinish/' => 'AuthController@restore_pre_finish',
    //Реф. ссылка на регистрацию
    '/reg/:num/' => 'HomeController@Index',
    '/signup/' => 'AuthController@signup',
    '/login/' => 'AuthController@Login',
    '/logout/' => 'AuthController@logout',

    // Уведомления
    '/notifications/' => 'NotificationsController@Index',
    '/notifications/settings/' => 'NotificationsController@settings',
    '/notifications/save_settings/' => 'NotificationsController@save_settings',
    '/notifications/notification/' => 'NotificationsController@notification',
    '/notifications/delete/' => 'NotificationsController@delete',
    '/updates/' => 'UpdatesController@Index',
    // Реклама
    '/ads/' => 'AdsController@Index',
    '/ads/view_ajax/' => 'AdsController@view_ajax',
    '/ads/optionad/' => 'AdsController@optionad',
    '/ads/checkurl/' => 'AdsController@checkurl',
    '/ads/nextcreate/' => 'AdsController@nextcreate',
    '/ads/bigtype/' => 'AdsController@bigtype',
    '/ads/uploadimg/' => 'AdsController@uploadimg',
    '/ads/loadage/' => 'AdsController@loadage',
    '/ads/createad/' => 'AdsController@createad',
    '/ads/status_ad/' => 'AdsController@status_ad',
    '/ads/clickgo/' => 'AdsController@clickgo',
    '/ads/cabinet/' => 'AdsController@cabinet',
    '/ads/create/' => 'AdsController@create',
    '/ads/upload/' => 'AdsController@upload',

    // Страница пользователя
    '/public:num' => 'PublicController@Index',
    '/u:num' => 'ProfileController@Index',

    '/u:num/after' => 'ProfileController@Index',
    '/tags/' => 'TagsController@Index',

    '/status/' => 'StatusController@Index',
    '/status/public/' => 'StatusController@Index',

    // Статистика страницы пользователя
    '/my_stats/' => 'My_statsController@Index',

    // Редактирование страницы
//    '/edit/'                                      => 'EditprofileController@Index',
    '/edit/box/' => 'EditprofileController@box',
    '/edit/contact/' => 'EditprofileController@contact',
    '/edit/interests/' => 'EditprofileController@interests',
    '/edit/all/' => 'EditprofileController@all',
    '/edit/miniature/' => 'EditprofileController@miniature',
    '/edit/miniature_save/' => 'EditprofileController@miniature_save',
    '/edit/load_photo/' => 'EditprofileController@load_photo',//box upload ava
    '/edit/del_photo/' => 'EditprofileController@del_photo',
//    '/edit/delcover/'                             => 'EditprofileController@delcover',
//    '/edit/savecoverpos/'                         => 'EditprofileController@savecoverpos',
    '/edit/save_general/' => 'EditprofileController@save_general',
    '/edit/save_contact/' => 'EditprofileController@save_contact',
    '/edit/save_interests/' => 'EditprofileController@save_interests',
    '/edit/save_xfields/' => 'EditprofileController@save_xfields',
//    '/edit/upload_cover/'                         => 'EditprofileController@upload_cover',
    '/edit/upload/' => 'EditprofileController@upload',
    '/edit/country/' => 'EditprofileController@Index',
    '/edit/city/' => 'EditprofileController@Index',

    '/del_my_page/' => 'NoneController@Index',

    '/rating/' => 'RatingController@Index',
    '/rating/add/' => 'RatingController@add',
    '/rating/view/' => 'RatingController@view',

    // other
    '/loadcity/' => 'LoadcityController@Index',

    '/antibot/' => 'AntibotController@index',
    '/antibot/code/' => 'AntibotController@code',

    // Альбомы
    '/albums/:num/' => 'AlbumsController@Index',
    '/albums/add/:num/' => 'AlbumsController@add',
    '/albums/view/:num/' => 'AlbumsController@view',
    '/albums/view/:num/page/:num' => 'AlbumsController@view',
    '/albums/comments/:num/' => 'AlbumsController@all_comments',
    '/albums/comments/:num/page/:num' => 'AlbumsController@all_comments',


    '/albums/view/:num/comments/:num' => 'AlbumsController@all_comments',
    '/albums/view/:num/comments/page/:num' => 'AlbumsController@all_comments',
    '/albums/edit/:num/' => 'AlbumsController@edit_pos_photos',
    '/albums/new/' => 'AlbumsController@new_photos',
    '/albums/new/:num/' => 'AlbumsController@new_photos',
    '/albums/create/' => 'AlbumsController@create',
    '/albums/create_page/' => 'AlbumsController@create_page',
    '/albums/del_album/' => 'AlbumsController@del_album',
    '/albums/save_pos_albums/' => 'AlbumsController@save_pos_albums',
    '/albums/save_pos_photos/' => 'AlbumsController@save_pos_photos',
    '/albums/edit_page/' => 'AlbumsController@edit_page',
    '/albums/save_album/' => 'AlbumsController@save_album',
    '/albums/save_descr/' => 'AlbumsController@save_descr',
    '/albums/upload/:num' => 'AlbumsController@upload',
    '/albums/del_photo/' => 'AlbumsController@del_photo',
    '/albums/set_cover/' => 'AlbumsController@set_cover',
    '/albums/all_photos_box/' => 'AlbumsController@all_photos_box',

    // Просмотр фотографий
    '/photo/' => 'PhotoController@Index',
    '/photo/:num/:num/user_page' => 'PhotoController@index',
    '/photo/:num/:num/all_comments' => 'PhotoController@Index',
    '/photo/:num/:num/wall/u:num' => 'PhotoController@Index',
    '/photo/:num/:num/notes/id:num' => 'PhotoController@Index',
    '/photo/:num/:num/news/' => 'PhotoController@Index',
    '/photo/:num/:num/msg/id:num' => 'PhotoController@Index',
    '/photo/:num/:num/:num/' => 'PhotoController@Index',
    '/photo/:num/:num/:num/album_comments/' => 'PhotoController@Index',
    '/photo/:num/:num/:num/new/' => 'PhotoController@Index',
    '/photo/add/rating/' => 'PhotoController@addrating',
    '/photo/view_rating/' => 'PhotoController@view_rating',
    '/photo/del_rate/' => 'PhotoController@del_rate',
    '/photo/profile/' => 'PhotoController@profile',
    '/photo/rotation/' => 'PhotoController@rotation',
    '/photo/add_comm/' => 'PhotoController@add_comm',//bug
    '/photo/del_comm/' => 'PhotoController@del_comm',
    '/photo/crop/' => 'PhotoController@crop',

    '/stories/add/box/' => 'StoriesController@addbox',
    '/stories/upload/' => 'StoriesController@upload',
    '/stories/show/' => 'StoriesController@show',
    '/stories/show/:num/:num/' => 'StoriesController@show_next',


    // Друзья
    '/friends/' => 'FriendsController@Index',
    '/friends/send/:num/' => 'FriendsController@send',
    '/friends/send/:num' => 'FriendsController@send',
    '/friends/:num/' => 'FriendsController@Index',
    '/friends/:num/page/:num/' => 'FriendsController@Index',
    '/friends/take/:num/' => 'FriendsController@take',
    '/friends/reject/:num/' => 'FriendsController@reject',
    '/friends/box/' => 'FriendsController@box',
    '/friends/delete/' => 'FriendsController@delete',
    '/friends/:num/online/' => 'FriendsController@online',
    '/friends/common/' => 'FriendsController@common',
    '/friends/:num/common/' => 'FriendsController@common',
    '/friends/requests/' => 'FriendsController@requests',
    '/friends/requests/page/:num/' => 'FriendsController@requests',
    '/friends/requests/common/:num/' => 'FriendsController@requests',
    '/friends/requests/common/:num/page/:num/' => 'FriendsController@requests',

    '/subscriptions/add/' => 'SubscriptionsController@add',
    '/subscriptions/delete/' => 'SubscriptionsController@delete',
    '/subscriptions/all/' => 'SubscriptionsController@index',

    //distinguish
    '/distinguish/load_friends/' => 'DistinguishController@load_friends',
    '/distinguish/mark/' => 'DistinguishController@mark',
    '/distinguish/mark_del/' => 'DistinguishController@mark_del',
    '/distinguish/mark_ok/' => 'DistinguishController@mark_ok',

    '/happy_friends_block_hide/' => 'NoneController@Index',

    // Закладки
    '/fave/' => 'FaveController@Index',
    '/fave/page/:num/' => 'FaveController@Index',
    '/fave/view/:num' => 'FaveController@Index',
    '/fave/add/' => 'FaveController@add',
    '/fave/save/' => 'FaveController@Index',
    '/fave/delete/' => 'FaveController@delete',

    // Видео
    '/videos/' => 'VideosController@Index',
    '/videos/:num/' => 'VideosController@Index',
    '/videos/:num/page/:num/' => 'VideosController@Index',
    '/video/:num/:num/' => 'VideosController@view',
    '/video/:num/:num/wall/:num' => 'VideosController@Index',
    '/video/:num/:num/msg/:num' => 'VideosController@Index',
    '/videos/add/' => 'VideosController@add',
    '/videos/load/' => 'VideosController@load',
    '/videos/send/' => 'VideosController@send',
    '/videos/page/' => 'VideosController@page',
    '/videos/delete/' => 'VideosController@delete',
    '/videos/edit/' => 'VideosController@edit',
    '/videos/editsave/' => 'VideosController@editsave',
    '/videos/view/' => 'VideosController@view',
    '/videos/upload/' => 'VideosController@upload',
    '/videos/upload_add/' => 'VideosController@upload_add',
    '/videos/addcomment/' => 'VideosController@addcomment',
    '/videos/all_comm/' => 'VideosController@all_comm',
    '/videos/delcomment/' => 'VideosController@delcomment',

    // Поиск
    '/search/' => 'SearchController@Index',
    '/fast_search/' => 'Fast_searchController@Index',

    //Новости
    '/news/' => 'FeedController@feed',
    '/news/updates/' => 'FeedController@Index',
    '/news/photos/' => 'FeedController@Index',
    '/news/videos/' => 'FeedController@Index',
    '/news/notifications/' => 'FeedController@Index',
    '/news/next/' => 'FeedController@Next',

    //Сообщения
    '/messages/' => 'MessagesController@Index',
    '/messages/i/' => 'MessagesController@Index',
    '/messages/send/' => 'MessagesController@send',
    '/messages/delet/' => 'MessagesController@delet',
    '/messages/history/' => 'MessagesController@history',
    '/messages/outbox/' => 'MessagesController@outbox',
    '/messages/show/:num/' => 'MessagesController@Index',
    '/messages/settTypeMsg/' => 'MessagesController@settTypeMsg',

    '/im/' => 'ImController@Index',
    '/im/:num/' => 'ImController@user',
    '/im/typograf/' => 'ImController@typograf',
    '/im/typograf/stop/' => 'ImController@typograf',
    '/im/read/' => 'ImController@read',
    '/im/send/' => 'ImController@send',
    '/im/update/' => 'ImController@update',
    '/im/history/' => 'ImController@history',
    '/im/delete/' => 'ImController@delete',
    '/im/upDialogs/' => 'ImController@upDialogs',

    // repost
    '/report/' => 'RepostController@Index',

    '/repost/all/' => 'RepostController@Index',
    '/repost/groups/' => 'RepostController@groups',
    '/repost/for_wall/' => 'RepostController@for_wall',
    '/repost/message/' => 'RepostController@message',

    //Стена
    '/wall/:num/' => 'WallController@Index',
    '/wall/:num/page/:num/' => 'WallController@page',
    '/wall/:num/own/' => 'WallController@Index',
    '/wall/:num/own/:num/' => 'WallController@Index',
    '/wall/:num/:num/' => 'WallController@Index',
    '/wall/delete/' => 'WallController@delete',
    '/wall/send/' => 'WallController@send',
    '/wall/page/' => 'WallController@page',
    '/wall/all_comm/' => 'WallController@all_comm',
    '/wall/all_liked_users/' => 'WallController@all_liked_users',
    '/wall/tell/' => 'WallController@tell',
    '/wall/parse_link/' => 'WallController@parse_link',
    '/wall/like_yes/' => 'WallController@like_yes',
    '/wall/like_no/' => 'WallController@like_no',
    '/wall/like_remove/' => 'WallController@like_no',
    '/wall/liked_users/' => 'WallController@liked_users',

    //Настройки
    '/settings/' => 'SettingsController@Index',
    '/settings/general/' => 'SettingsController@general',
    '/settings/privacy/' => 'SettingsController@privacy',
    '/settings/blacklist/' => 'SettingsController@blacklist',
    '/settings/change_mail/' => 'SettingsController@change_mail',
    '/settings/newpass/' => 'SettingsController@newpass',
    '/settings/newname/' => 'SettingsController@newname',
    '/settings/saveprivacy/' => 'SettingsController@saveprivacy',
    '/settings/addblacklist/' => 'SettingsController@addblacklist',
    '/settings/delblacklist/' => 'SettingsController@delblacklist',
    '/settings/time_zone/' => 'SettingsController@time_zone',

    //Помощь
    '/support/' => 'SupportController@Index',
    '/support/:num/' => 'SupportController@Index',
    '/support/send/' => 'SupportController@send',
    '/support/new/' => 'SupportController@new',
    '/support/show/:num/' => 'SupportController@show',
    '/support/delete/' => 'SupportController@delete',
    '/support/answer/' => 'SupportController@answer',
    '/support/delete_answer/' => 'SupportController@delete_answer',
    '/support/close/' => 'SupportController@close',


    //UBM
    '/balance/' => 'BalanceController@Index',
    '/balance/code/' => 'BalanceController@code',
    '/balance/invite/' => 'BalanceController@invite',
    '/balance/invited/' => 'BalanceController@invited',
    '/balance/payment/' => 'BalanceController@payment',
    '/balance/payment_2/' => 'BalanceController@payment_2',
    '/balance/ok_payment/' => 'BalanceController@ok_payment',

    //Подарки
    '/gifts/' => 'GiftsController@Index',
    '/gifts/:num/' => 'GiftsController@Index',
    '/gifts/:num/new/' => 'GiftsController@Index',
    '/gifts/delete/' => 'GiftsController@delete',

    //Статистика сообщетсв
    '/stats/' => 'Stats_groupsController@Index',
    '/stats/:num/' => 'Stats_groupsController@Index',

    //Сообщества
    '/groups/' => 'GroupsController@Index',
    '/groups/admin/' => 'GroupsController@admin',
    '/groups/send/' => 'GroupsController@send',
    '/groups/exit/' => 'GroupsController@logout',
    '/groups/login/' => 'GroupsController@login',
    '/groups/loadphoto_page/' => 'GroupsController@load_photo_page',
    '/groups/delphoto/' => 'GroupsController@delphoto',
    '/groups/addfeedback_pg/' => 'GroupsController@addfeedback_pg',
    '/groups/allfeedbacklist/' => 'GroupsController@allfeedbacklist',
    '/groups/delfeedback/' => 'GroupsController@delfeedback',
    '/groups/editfeeddave/' => 'GroupsController@editfeeddave',
    '/groups/checkFeedUser/' => 'GroupsController@checkFeedUser',
    '/groups/saveinfo/' => 'GroupsController@saveinfo',
    '/groups/new_admin/' => 'GroupsController@new_admin',
    '/groups/send_new_admin/' => 'GroupsController@send_new_admin',
    '/groups/deladmin/' => 'GroupsController@deladmin',
    '/groups/wall_send_comm/' => 'GroupsController@wall_send_comm',
    '/groups/wall_del/' => 'GroupsController@wall_del',
    '/groups/wall_tell/' => 'GroupsController@wall_tell',
    '/groups/all_people/' => 'GroupsController@all_people',
    '/groups/all_groups_user/' => 'GroupsController@all_groups_user',
    '/groups/invitebox/' => 'GroupsController@invitebox',
    '/groups/invitesend/' => 'GroupsController@invitesend',
    '/groups/invite_no/' => 'GroupsController@invite_no',
    '/groups/invites/' => 'GroupsController@invites',
    '/groups/fasten/' => 'GroupsController@fasten',
    '/groups/unfasten/' => 'GroupsController@unfasten',
    '/groups/all_comm/' => 'GroupsController@all_comm',
    '/groups/wall_send/' => 'GroupsController@wall_send',
    '/groups/select_video_info/' => 'GroupsController@select_video_info',
    '/groups/wall_like_remove/' => 'GroupsController@wall_like_remove',
    '/groups/wall_like_yes/' => 'GroupsController@wall_like_yes',
    '/groups/wall_like_users_five/' => 'GroupsController@wall_like_users_five',
    '/groups/wall/:num/:num/' => 'GroupsController@wall',
    '/forum:num/view/:num/' => 'Groups_forumController@view',
    '/groups/loadphoto/:num/' => 'GroupsController@loadphoto',
    '/wallgroups/:num/:num/' => 'GroupsController@wallgroups',

    '/groups/all_liked_users/' => 'GroupsController@all_liked_users',

    //Сообщества -> Публичные страницы -> Обсуждения
    '/public/forum/:num/' => 'NoneController@Index',
    '/forum:num/' => 'NoneController@Index',
    '/forum/:num/new/' => 'NoneController@Index',
    '/forum/:num/view/:num/' => 'NoneController@Index',

    '/groups_forum/new_send/' => 'Groups_forumController@new_send',
    '/groups_forum/:num/' => 'Groups_forumController@Index',
    '/groups_forum/add_msg/' => 'Groups_forumController@add_msg',
    '/groups_forum/prev_msg/' => 'Groups_forumController@prev_msg',
    '/groups_forum/saveedit/' => 'Groups_forumController@saveedit',
    '/groups_forum/savetitle/' => 'Groups_forumController@savetitle',
    '/groups_forum/fix/' => 'Groups_forumController@fix',
    '/groups_forum/status/' => 'Groups_forumController@status',
    '/groups_forum/del/' => 'Groups_forumController@del',
    '/groups_forum/delmsg/' => 'Groups_forumController@delmsg',
    '/groups_forum/createvote/' => 'Groups_forumController@createvote',
    '/groups_forum/delvote/' => 'Groups_forumController@delvote',
    '/groups_forum/delcover/:num/' => 'Groups_forumController@delcover',
    '/groups_forum/savecoverpos/:num/' => 'Groups_forumController@savecoverpos',

    //Сообщества -> Публичные страницы -> Аудио
    '/public/audio/' => 'Public_audioController@upload_box',
    '/public/audio/:num/' => 'Public_audioController@Index',
    '/public/audio/upload_box/' => 'Public_audioController@upload_box',
    '/public/audio/upload/' => 'Public_audioController@upload',
    '/public/audio/add/' => 'Public_audioController@add',

    //Сообщества -> Публичные страницы -> Видео
    '/public/videos/' => 'NoPublic_videosneController@Index',
    '/public/videos/:num/' => 'NoPublic_videosneController@Index',
    '/public/videos/add/' => 'NoPublic_videosneController@add',
    '/public/videos/delete/' => 'NoPublic_videosneController@delete',
    '/public/videos/edit/' => 'NoPublic_videosneController@edit',
    '/public/videos/edit_save/' => 'NoPublic_videosneController@edit_save',
    '/public/videos/search/' => 'NoPublic_videosneController@search',

    //Сообщества -> Публичные страницы

    '/public/id:num/' => 'NoneController@Index',
    '/public_audio/' => 'NoneController@Index',
    '/public_audio/:num/' => 'NoneController@Index',
    '/public_audio/search/' => 'NoneController@Index',
    '/public_audio/addlistgroup/' => 'NoneController@Index',
    '/public_audio/editsave/' => 'NoneController@Index',
    '/public_audio/delete/' => 'NoneController@Index',
    '/public_videos/search/' => 'NoneController@Index',
    '/public_videos/add/' => 'NoneController@Index',
    '/public_videos/del/' => 'NoneController@Index',
    '/public_videos/edit/' => 'NoneController@Index',
    '/public_videos/edit_save/' => 'NoneController@Index',
    '/public_videos/:num/' => 'NoneController@Index',

    '/public/edit/:num' => 'GroupsController@edit_main',
    '/public/edit/users/:num' => 'GroupsController@edit_users',
    '/public/edit/users/:num/admin' => 'GroupsController@edit_users',
    '/public/edit/blacklist/:num' => 'GroupsController@edit',
    '/public/edit/link/:num' => 'GroupsController@edit',

    //Музыка
    '/audio/' => 'AudioController@Index',
    '/audio/:num/' => 'AudioController@Index',
    '/audio/load_play_list/' => 'AudioController@load_play_list',
    '/audio/add/' => 'AudioController@add',
    '/audio/upload_box/' => 'AudioController@upload_box',
    '/audio/my_box/' => 'AudioController@allMyAudiosBox',
    '/audio/upload/' => 'AudioController@upload',
    '/audio/get_text/' => 'AudioController@get_text',
    '/audio/get_info/' => 'AudioController@get_info',
    '/audio/search_all/' => 'AudioController@search_all',
    '/audio/save_edit/' => 'AudioController@save_edit',
    '/audio/del_audio/' => 'AudioController@del_audio',
    '/audio/loadFriends/' => 'AudioController@loadFriends',
    '/audio/:num/load_all/' => 'AudioController@load_all',

    '/audio/my_music/' => 'AudioController@my_music',
    '/audio/feed/' => 'AudioController@feed',
    '/audio/recommendations/' => 'AudioController@recommendations',
    '/audio/popular/' => 'AudioController@popular',

    //Документы
    '/docs/' => 'DocController@Index',
    '/docs/del/' => 'DocController@del',
    '/docs/editsave/' => 'DocController@editsave',
    '/docs/box/' => 'DocController@editsave',
    '/docs/download/:num/' => 'DocController@download',

    // votes
    '/votes/' => 'VotesController@Index',

    '/attach/' => 'AttachController@Index',
    '/attach_comm/' => 'AttachController@Attach_comm',
    '/attach_comm/addcomm/' => 'AttachController@addcomm',
    '/attach_comm/delcomm/' => 'AttachController@delcomm',
    '/attach_comm/prevcomm/' => 'AttachController@prevcomm',
    '/attach_groups/:num/' => 'AttachController@Attach_groups',

    //Стат страницы
    '/:seg.html' => 'Static_pageController@Index',

    //Языки
    '/lang/' => 'LangController@Index',
    '/lang/change/:num/' => 'LangController@change_lang', //?


    '/bugs/' => 'BugsController@Index',
    '/bugs/:num' => 'BugsController@view_page',
    '/bugs/load_img/' => 'BugsController@load_img',
    '/bugs/add_box/' => 'BugsController@add_box',
    '/bugs/create/' => 'BugsController@create',
    '/bugs/comments/create/' => 'BugsController@create_comment',
    '/bugs/delete/' => 'BugsController@delete',
    '/bugs/open/' => 'BugsController@open',
    '/bugs/complete/' => 'BugsController@complete',
    '/bugs/close/' => 'BugsController@close',
    '/bugs/my/' => 'BugsController@my',
    '/bugs/view/' => 'BugsController@view',

    '/admin/' => 'AdminController@main',
    '/admin/stats/' => 'AdminController@stats',
    '/admin/settings/' => 'AdminController@main',
    '/admin/db/' => 'AdminController@main',
    '/admin/mysettings/' => 'AdminController@main',
    '/admin/users/' => 'AdminController@main',
    '/admin/xfields/' => 'AdminController@main',
    '/admin/video/' => 'AdminController@main',
    '/admin/music/' => 'AdminController@main',
    '/admin/photos/' => 'AdminController@main',
    '/admin/gifts/' => 'AdminController@main',
    '/admin/groups/' => 'AdminController@main',
    '/admin/report/' => 'AdminController@main',
    '/admin/mail_tpl/' => 'AdminController@main',
    '/admin/mail/' => 'AdminController@main',
    '/admin/ban/' => 'AdminController@main',
    '/admin/search/' => 'AdminController@main',
    '/admin/static/' => 'AdminController@main',
    '/admin/antivirus/' => 'AdminController@main',
    '/admin/logs/' => 'AdminController@main',
    '/admin/country/' => 'AdminController@main',
    '/admin/city/' => 'AdminController@main',
    '/admin/ads/' => 'AdminController@main',

    '/sitemap/' => 'SitemapController@main',

    '/:any' => 'HomeController@alias',
);

