<?php
$router->add([
    // Главная
    '/'                                           => 'Home@Index',

    // Регистрация
    '/reg/'                                       => 'Home@Index',
    '/register/'                                       => 'Register@Index',

    //Реф. ссылка на регистрацию
    '/reg/:num/'                                  => 'Home@Index',
    '/signup/'                                  => 'Register@Signup',
    '/login/'                                  => 'Home@Login',

    '/test/'                                       => 'Home@test',
    '/test2/'                                       => 'Home@test2',
    '/test3/'                                       => 'Home@test3',

    // Уведомления
    '/notifications/'                             => 'Notifications@Index',
    '/notifications/settings/'                             => 'Notifications@settings',
    '/notifications/save_settings/'                             => 'Notifications@save_settings',
    '/notifications/notification/'                             => 'Notifications@notification',
    '/notifications/del/'                             => 'Notifications@del',
    '/updates/'                                   => 'Updates@Index',
    // Реклама
    '/ads/'                                       => 'Ads@Index',
    '/ads/view_ajax/'                             => 'Ads@view_ajax',
    '/ads/optionad/'                              => 'Ads@optionad',
    '/ads/checkurl/'                              => 'Ads@checkurl',
    '/ads/nextcreate/'                            => 'Ads@nextcreate',
    '/ads/bigtype/'                               => 'Ads@bigtype',
    '/ads/uploadimg/'                             => 'Ads@uploadimg',
    '/ads/loadage/'                               => 'Ads@loadage',
    '/ads/createad/'                              => 'Ads@createad',
    '/ads/status_ad/'                             => 'Ads@status_ad',
    '/ads/clickgo/'                               => 'Ads@clickgo',
    '/ads/cabinet/'                               => 'Ads@cabinet',
    '/ads/create/'                                => 'Ads@create',
    '/ads/upload/'                                => 'Ads@upload',

    // Страница пользователя
    '/u:num'                                      => 'Profile@Index',
    '/u:num/after'                                => 'Profile@Index',

    '/status/'                                    => 'Status@Index',
    '/status/public/'                             => 'Status@Index',

    // Статистика страницы пользователя
    '/my_stats/'                                  => 'My_stats@Index',

    // Редактирование страницы
    '/edit/'                                      => 'Editprofile@Index',
    '/edit/contact/'                              => 'Editprofile@contact',
    '/edit/interests/'                            => 'Editprofile@interests',
    '/edit/all/'                                  => 'Editprofile@all',
    '/edit/miniature/'                            => 'Editprofile@miniature',
    '/edit/miniature_save/'                       => 'Editprofile@miniature_save',
    '/edit/load_photo/'                           => 'Editprofile@load_photo',//box upload ava
    '/edit/del_photo/'                            => 'Editprofile@del_photo',
    '/edit/delcover/'                             => 'Editprofile@delcover',
    '/edit/savecoverpos/'                         => 'Editprofile@savecoverpos',
    '/edit/save_general/'                         => 'Editprofile@save_general',
    '/edit/save_contact/'                         => 'Editprofile@save_contact',
    '/edit/save_interests/'                       => 'Editprofile@save_interests',
    '/edit/save_xfields/'                         => 'Editprofile@save_xfields',
    '/edit/upload_cover/'                         => 'Editprofile@upload_cover',
    '/edit/upload/'                               => 'Editprofile@upload',
    '/edit/country/'                              => 'Editprofile@Index',
    '/edit/city/'                                 => 'Editprofile@Index',

    '/del_my_page/'                               => 'None@Index',

    '/rating/'                                    => 'Rating@Index',
    '/rating/add/'                                => 'Rating@add',
    '/rating/view/'                               => 'Rating@view',

    // other
    '/loadcity/'                                  => 'None@Index',

    '/antibot/'                                  => 'Antibot@index',
    '/antibot/code/'                                  => 'Antibot@code',

    // Альбомы
    '/albums/:num/'                               => 'Albums@Index',
    '/albums/add/:num/'                           => 'Albums@add',
    '/albums/view/:num/'                          => 'Albums@view',
    '/albums/view/:num/page/:num'                 => 'Albums@view',
    '/albums/comments/:num/'                      => 'Albums@all_comments',
    '/albums/comments/:num/page/:num'             => 'Albums@all_comments',


    '/albums/view/:num/comments/:num'             => 'Albums@all_comments',
    '/albums/view/:num/comments/page/:num'        => 'Albums@all_comments',
    '/albums/edit/:num/'                          => 'Albums@edit_pos_photos',
    '/albums/new/'                                => 'Albums@new_photos',
    '/albums/new/:num/'                           => 'Albums@new_photos',
    '/albums/create_page/'                        => 'Albums@create_page',
    '/albums/del_album/'                          => 'Albums@del_album',
    '/albums/save_pos_albums/'                    => 'Albums@save_pos_albums',
    '/albums/save_pos_photos/'                    => 'Albums@save_pos_photos',
    '/albums/edit_page/'                          => 'Albums@edit_page',
    '/albums/save_album/'                         => 'Albums@save_album',
    '/albums/save_descr/'                         => 'Albums@save_descr',
    '/albums/upload/:num'                         => 'Albums@upload',
    '/albums/del_photo/'                          => 'Albums@del_photo',
    '/albums/set_cover/'                          => 'Albums@set_cover',
    '/albums/all_photos_box/'                     => 'Albums@all_photos_box',

    // Просмотр фотографий
    '/photo/'                                     => 'Photo@Index',
    '/photo/:num/:num/user_page'                  => 'Photo@index',
    '/photo/:num/:num/all_comments'               => 'Photo@Index',
    '/photo/:num/:num/wall/u:num'                 => 'Photo@Index',
    '/photo/:num/:num/notes/id:num'               => 'Photo@Index',
    '/photo/:num/:num/news/'                      => 'Photo@Index',
    '/photo/:num/:num/msg/id:num'                 => 'Photo@Index',
    '/photo/:num/:num/:num/'                      => 'Photo@Index',
    '/photo/:num/:num/:num/album_comments/'       => 'Photo@Index',
    '/photo/:num/:num/:num/new/'                  => 'Photo@Index',
    '/photo/addrating/'                           => 'Photo@addrating',
    '/photo/view_rating/'                         => 'Photo@view_rating',
    '/photo/del_rate/'                            => 'Photo@del_rate',
    '/photo/profile/'                             => 'Photo@profile',
    '/photo/rotation/'                            => 'Photo@rotation',
    '/photo/add_comm/'                            => 'Photo@add_comm',//bug
    '/photo/del_comm/'                            => 'Photo@del_comm',
    '/photo/crop/'                                => 'Photo@crop',

    // Друзья
    '/friends/'                                   => 'Friends@Index',
    '/friends/:num/'                              => 'Friends@Index',
    '/friends/:num/page/:num/'                    => 'Friends@Index',
    '/friends/send_demand/:num/'                  => 'Friends@send_demand',
    '/friends/take/:num/'                         => 'Friends@take',
    '/friends/reject/:num/'                       => 'Friends@reject',
    '/friends/box/'                               => 'Friends@box',
    '/friends/delete/'                            => 'Friends@delete',
    '/friends/online/:num/'                       => 'Friends@online',
    '/friends/common/:num/'                       => 'Friends@common',
    '/friends/requests/'                          => 'Friends@requests',
    '/friends/requests/page/:num/'                => 'Friends@requests',
    '/friends/requests/common/:num/'              => 'Friends@requests',
    '/friends/requests/common/:num/page/:num/'    => 'Friends@requests',

    '/subscriptions/add/'                         => 'Subscriptions@add',
    '/subscriptions/del/'                         => 'Subscriptions@del',
    '/subscriptions/all/'                         => 'Subscriptions@index',

    //distinguish
    '/distinguish/load_friends/'                  => 'Distinguish@load_friends',
    '/distinguish/mark/'                          => 'Distinguish@mark',
    '/distinguish/mark_del/'                      => 'Distinguish@mark_del',
    '/distinguish/mark_ok/'                       => 'Distinguish@mark_ok',

    '/happy_friends_block_hide/'                  => 'None@Index',

    // Закладки
    '/fave/'                                      => 'Fave@Index',
    '/fave/page/:num/'                            => 'Fave@Index',
    '/fave/view/:num'                             => 'Fave@Index',
    '/fave/add/'                                  => 'Fave@add',
    '/fave/save/'                                 => 'Fave@Index',
    '/fave/delet/'                                => 'Fave@delet',

    // Видео
    '/videos/'                                    => 'Videos@Index',
    '/videos/:num/'                                => 'Videos@Index',
    '/videos/:num/page/:num/'                     => 'Videos@Index',
    '/video/:num/:num/'                           => 'Videos@view',
    '/video/:num/:num/wall/:num'                  => 'Videos@Index',
    '/video/:num/:num/msg/:num'                   => 'Videos@Index',
    '/videos/add/'                                => 'Videos@add',
    '/videos/load/'                               => 'Videos@load',
    '/videos/send/'                               => 'Videos@send',
    '/videos/page/'                               => 'Videos@page',
    '/videos/delet/'                              => 'Videos@delet',
    '/videos/edit/'                               => 'Videos@edit',
    '/videos/editsave/'                           => 'Videos@editsave',
    '/videos/view/'                               => 'Videos@view',
    '/videos/upload/'                             => 'Videos@upload',
    '/videos/upload_add/'                         => 'Videos@upload_add',
    '/videos/addcomment/'                         => 'Videos@addcomment',
    '/videos/all_comm/'                           => 'Videos@all_comm',
    '/videos/delcomment/'                         => 'Videos@delcomment',

    // Поиск
    '/search/'                                    => 'Search@Index',
    '/fast_search/'                               => 'Fast_search@Index',

    //Новости
    '/news/'                                      => 'News@Index',
    '/news/updates/'                              => 'News@Index',
    '/news/photos/'                               => 'News@Index',
    '/news/videos/'                               => 'News@Index',
    '/news/notifications/'                        => 'News@Index',

    //Сообщения
    '/messages/'                                  => 'Messages@Index',
    '/messages/i/'                                => 'Messages@Index',
    '/messages/send/'                             => 'Messages@send',
    '/messages/delet/'                            => 'Messages@delet',
    '/messages/history/'                          => 'Messages@history',
    '/messages/outbox/'                           => 'Messages@outbox',
    '/messages/show/:num/'                        => 'Messages@Index',
    '/messages/settTypeMsg/'                      => 'Messages@settTypeMsg',

    '/im/'                                        => 'Im@Index',
    '/im/typograf/'                               => 'Im@typograf',
    '/im/typograf/stop/'                          => 'Im@typograf',
    '/im/read/'                                   => 'Im@read',
    '/im/send/'                                   => 'Im@send',
    '/im/update/'                                 => 'Im@update',
    '/im/history/'                                => 'Im@history',
    '/im/del/'                                    => 'Im@del',
    '/im/upDialogs/'                              => 'Im@upDialogs',

    // repost
    '/report/'                                    => 'Repost@Index',

    '/repost/all/'                                => 'Repost@Index',
    '/repost/groups/'                             => 'Repost@groups',
    '/repost/for_wall/'                             => 'Repost@groups',

    //Стена
    '/wall/:num/'                                 => 'Wall@Index',
    '/wall/:num/page/:num/'                       => 'Wall@Index',
    '/wall/:num/own/'                             => 'Wall@Index',
    '/wall/:num/own/:num/'                        => 'Wall@Index',
    '/wall/:num/:num/'                            => 'Wall@Index',
    '/wall/delet/'                                => 'Wall@delet',
    '/wall/send/'                                 => 'Wall@send',
    '/wall/page/'                                 => 'Wall@page',
    '/wall/all_comm/'                             => 'Wall@all_comm',
    '/wall/all_liked_users/'                      => 'Wall@all_liked_users',
    '/wall/tell/'                                 => 'Wall@tell',
    '/wall/parse_link/'                           => 'Wall@parse_link',
    '/wall/like_yes/'                             => 'Wall@like_yes',
    '/wall/like_no/'                              => 'Wall@like_no',
    '/wall/liked_users/'                          => 'Wall@liked_users',

    //Настройки
    '/settings/'                                  => 'Settings@Index',
    '/settings/general/'                                  => 'Settings@general',
    '/settings/privacy/'                          => 'Settings@privacy',
    '/settings/blacklist/'                        => 'Settings@blacklist',
    '/settings/change_mail/'                      => 'Settings@change_mail',
    '/settings/newpass/'                          => 'Settings@newpass',
    '/settings/newname/'                          => 'Settings@newname',
    '/settings/saveprivacy/'                      => 'Settings@saveprivacy',
    '/settings/addblacklist/'                     => 'Settings@addblacklist',
    '/settings/delblacklist/'                     => 'Settings@delblacklist',

    //Помощь
    '/support/'                                   => 'Support@Index',
    '/support/send/'                              => 'Support@send',
    '/support/new/'                               => 'Support@new',
    '/support/show/:num/'                         => 'Support@show',
    '/support/delet/'                             => 'Support@delet',
    '/support/answer/'                            => 'Support@answer',
    '/support/delet_answer/'                      => 'Support@delet_answer',
    '/support/close/'                             => 'Support@close',

    //Воостановление пароля
    '/restore/'                                   => 'Restore@Index',
    '/restore/next/'                              => 'Restore@next',
    '/restore/send/'                              => 'Restore@send',
    '/restore/finish/'                            => 'Restore@finish',

    //UBM
    '/balance/'                                   => 'Balance@Index',
    '/balance/code/'                                   => 'Balance@code',
    '/balance/invite/'                                   => 'Balance@invite',
    '/balance/invited/'                                   => 'Balance@invited',
    '/balance/payment/'                                   => 'Balance@payment',
    '/balance/payment_2/'                                   => 'Balance@payment_2',
    '/balance/ok_payment/'                                   => 'Balance@ok_payment',

    //Подарки
    '/gifts/'                                     => 'Gifts@Index',
    '/gifts/:num/'                                => 'Gifts@Index',
    '/gifts/:num/new/'                            => 'Gifts@Index',
    '/gifts/del/'                                 => 'Gifts@del',

    //Статистика сообщетсв
    '/stats/'                                     => 'Stats_groups@Index',
    '/stats/:num/'                                => 'Stats_groups@Index',

    //Сообщества
    '/groups/'                                    => 'Groups@Index',
    '/groups/send/'                               => 'Groups@send',
    '/groups/exit/'                               => 'Groups@exit',
    '/groups/login/'                              => 'Groups@login',
    '/groups/loadphoto_page/'                     => 'Groups@loadphoto_page',
    '/groups/delphoto/'                           => 'Groups@delphoto',
    '/groups/addfeedback_pg/'                     => 'Groups@addfeedback_pg',
    '/groups/allfeedbacklist/'                    => 'Groups@allfeedbacklist',
    '/groups/delfeedback/'                        => 'Groups@delfeedback',
    '/groups/editfeeddave/'                       => 'Groups@editfeeddave',
    '/groups/checkFeedUser/'                      => 'Groups@checkFeedUser',
    '/groups/saveinfo/'                           => 'Groups@saveinfo',
    '/groups/new_admin/'                          => 'Groups@new_admin',
    '/groups/send_new_admin/'                     => 'Groups@send_new_admin',
    '/groups/deladmin/'                           => 'Groups@deladmin',
    '/groups/wall_send_comm/'                     => 'Groups@wall_send_comm',
    '/groups/wall_del/'                           => 'Groups@wall_del',
    '/groups/wall_tell/'                          => 'Groups@wall_tell',
    '/groups/all_people/'                         => 'Groups@all_people',
    '/groups/all_groups_user/'                    => 'Groups@all_groups_user',
    '/groups/invitebox/'                          => 'Groups@invitebox',
    '/groups/invitesend/'                         => 'Groups@invitesend',
    '/groups/invite_no/'                          => 'Groups@invite_no',
    '/groups/invites/'                            => 'Groups@invites',
    '/groups/fasten/'                             => 'Groups@fasten',
    '/groups/unfasten/'                           => 'Groups@unfasten',
    '/groups/all_comm/'                           => 'Groups@all_comm',
    '/groups/wall_send/'                          => 'Groups@wall_send',
    '/groups/select_video_info/'                  => 'Groups@select_video_info',
    '/groups/wall_like_remove/'                   => 'Groups@wall_like_remove',
    '/groups/wall_like_yes/'                      => 'Groups@wall_like_yes',
    '/groups/wall_like_users_five/'               => 'Groups@wall_like_users_five',
    '/groups/wall/:num/:num/'                     => 'Groups@wall',
    '/forum:num/view/:num/'                     => 'Groups_forum@view',
    '/groups/loadphoto/:num/'                     => 'Groups@loadphoto',
    '/wallgroups/:num/:num/'                      => 'Groups@wallgroups',

    //Сообщества -> Публичные страницы -> Обсуждения
    '/public/forum/:num/'                         => 'None@Index',
    '/forum:num/'                                 => 'None@Index',
    '/forum/:num/new/'                            => 'None@Index',
    '/forum/:num/view/:num/'                      => 'None@Index',

    '/groups_forum/new_send/'                     => 'Groups_forum@new_send',
    '/groups_forum/:num/'                => 'Groups_forum@Index',
    '/groups_forum/add_msg/'                      => 'Groups_forum@add_msg',
    '/groups_forum/prev_msg/'                     => 'Groups_forum@prev_msg',
    '/groups_forum/saveedit/'                     => 'Groups_forum@saveedit',
    '/groups_forum/savetitle/'                    => 'Groups_forum@savetitle',
    '/groups_forum/fix/'                          => 'Groups_forum@fix',
    '/groups_forum/status/'                       => 'Groups_forum@status',
    '/groups_forum/del/'                          => 'Groups_forum@del',
    '/groups_forum/delmsg/'                       => 'Groups_forum@delmsg',
    '/groups_forum/createvote/'                   => 'Groups_forum@createvote',
    '/groups_forum/delvote/'                      => 'Groups_forum@delvote',
    '/groups_forum/delcover/:num/'                => 'Groups_forum@delcover',
    '/groups_forum/savecoverpos/:num/'            => 'Groups_forum@savecoverpos',

    //Сообщества -> Публичные страницы -> Аудио
    '/public/audio/'                         => 'Public_audio@upload_box',
    '/public/audio/:num/'                         => 'Public_audio@Index',
    '/public/audio/upload_box/'                         => 'Public_audio@upload_box',
    '/public/audio/upload/'                         => 'Public_audio@upload',
    '/public/audio/add/'                         => 'Public_audio@add',

    //Сообщества -> Публичные страницы -> Видео
    '/public/videos/'                        => 'NoPublic_videosne@Index',
    '/public/videos/:num/'                        => 'NoPublic_videosne@Index',
    '/public/videos/add/'                        => 'NoPublic_videosne@add',
    '/public/videos/del/'                        => 'NoPublic_videosne@del',
    '/public/videos/edit/'                        => 'NoPublic_videosne@edit',
    '/public/videos/edit_save/'                        => 'NoPublic_videosne@edit_save',
    '/public/videos/search/'                        => 'NoPublic_videosne@search',

    //Сообщества -> Публичные страницы
    '/public:num'                                 => 'Public@Index',
    '/public/id:num/'                             => 'None@Index',
    '/public_audio/'                              => 'None@Index',
    '/public_audio/:num/'                         => 'None@Index',
    '/public_audio/search/'                       => 'None@Index',
    '/public_audio/addlistgroup/'                 => 'None@Index',
    '/public_audio/editsave/'                     => 'None@Index',
    '/public_audio/del/'                          => 'None@Index',
    '/public_videos/search/'                      => 'None@Index',
    '/public_videos/add/'                         => 'None@Index',
    '/public_videos/del/'                         => 'None@Index',
    '/public_videos/edit/'                        => 'None@Index',
    '/public_videos/edit_save/'                   => 'None@Index',
    '/public_videos/:num/'                        => 'None@Index',

    //Музыка
    '/audio/'                                     => 'Audio@Index',
    '/audio/:num/'                                => 'Audio@Index',
    '/audio/load_play_list/'                      => 'Audio@load_play_list',
    '/audio/add/'                                 => 'Audio@add',
    '/audio/upload_box/'                                 => 'Audio@upload_box',
    '/audio/upload/'                                 => 'Audio@upload',
    '/audio/get_text/'                            => 'Audio@get_text',
    '/audio/get_info/'                            => 'Audio@get_info',
    '/audio/search_all/'                          => 'Audio@search_all',
    '/audio/save_edit/'                           => 'Audio@save_edit',
    '/audio/del_audio/'                           => 'Audio@del_audio',
    '/audio/loadFriends/'                         => 'Audio@loadFriends',
    '/audio/:num/load_all/'                       => 'Audio@load_all',

    '/audio/my_music/'                       => 'Audio@Index',
    '/audio/feed/'                       => 'Audio@Index',
    '/audio/recommendations/'                       => 'Audio@Index',
    '/audio/popular/'                       => 'Audio@Index',

    //Документы
    '/docs/'                                      => 'Doc@Index',
    '/docs/del/'                                  => 'Doc@del',
    '/docs/editsave/'                             => 'Doc@editsave',
    '/docs/download/:num/'                        => 'Doc@download',

    // votes
    '/votes/'                                     => 'Votes@Index',

    '/attach/'                                    => 'Attach@Index',
    '/attach_comm/'                               => 'Attach_comm@Index',
    '/attach_comm/addcomm/'                       => 'Attach_comm@addcomm',
    '/attach_comm/delcomm/'                       => 'Attach_comm@delcomm',
    '/attach_comm/prevcomm/'                      => 'Attach_comm@prevcomm',
    '/attach_groups/:num/'                        => 'Attach_groups@Index',

    //Стат страницы
    '/:seg.html'                                  => 'Static_page@Index',

    //Языки
    '/lang/'                                      => 'Lang@Index',

    '/logout/'                                    => 'Logout@Index',

    '/bugs/'                                    => 'Bugs@Index',
    '/bugs/load_img/'                                    => 'Bugs@load_img',
    '/bugs/add_box/'                                    => 'Bugs@add_box',
    '/bugs/create/'                                    => 'Bugs@create',
    '/bugs/delete/'                                    => 'Bugs@delete',
    '/bugs/open/'                                    => 'Bugs@open',
    '/bugs/complete/'                                    => 'Bugs@complete',
    '/bugs/close/'                                    => 'Bugs@close',
    '/bugs/my/'                                    => 'Bugs@my',
    '/bugs/view/'                                    => 'Bugs@view',

    '/admin/'                                    => 'Admin@main',
    '/admin/stats/'                                    => 'Admin@stats',
    '/admin/settings/'                                    => 'Admin@main',
    '/admin/db/'                                    => 'Admin@main',
    '/admin/mysettings/'                                    => 'Admin@main',
    '/admin/users/'                                    => 'Admin@main',
    '/admin/xfields/'                                    => 'Admin@main',
    '/admin/video/'                                    => 'Admin@main',
    '/admin/music/'                                    => 'Admin@main',
    '/admin/photos/'                                    => 'Admin@main',
    '/admin/gifts/'                                    => 'Admin@main',
    '/admin/groups/'                                    => 'Admin@main',
    '/admin/report/'                                    => 'Admin@main',
    '/admin/mail_tpl/'                                    => 'Admin@main',
    '/admin/mail/'                                    => 'Admin@main',
    '/admin/ban/'                                    => 'Admin@main',
    '/admin/search/'                                    => 'Admin@main',
    '/admin/static/'                                    => 'Admin@main',
    '/admin/antivirus/'                                    => 'Admin@main',
    '/admin/logs/'                                    => 'Admin@main',
    '/admin/country/'                                    => 'Admin@main',
    '/admin/city/'                                    => 'Admin@main',
    '/admin/ads/'                                    => 'Admin@main',

]);

