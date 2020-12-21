@extends('app.app')
@section('content')
    <style type="text/css">.padcont {padding: 0;padding-bottom: 15px;}</style>
    <div class="d-flex justify-content-center">
        <div class="col-12 col-sm-12 col-md-12 col-lg-8 col-xl-6">
            <div class="card">
                <div class="">
                    <nav class="nav al_audio_player">
                        <a class="nav-link h3 active" href="#"  onclick="if(event.target.id != 'upload_btn') audio.change_tab('my_music');">Мои аудиозаписи

                        </a>
                        <a class="nav-link h3" href="#" onclick="audio.change_tab('feed');">Обновления друзей</a>
                        <a class="nav-link h3" href="#" onclick="audio.change_tab('recommendations');">Рекомендации</a>
                        <a class="nav-link h3" href="#" onclick="audio.change_tab('popular');">Популярное</a>
                    </nav>
                    <div class="al_audio_add_but icon-plus-1 bsbb" onmouseover="showTooltip(this, {text: 'Добавить аудиозапись', shift:[2,5,0]});" id="upload_btn" onclick="audio.uploadBox()">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-plus" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8 3.5a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5H4a.5.5 0 0 1 0-1h3.5V4a.5.5 0 0 1 .5-.5z"/>
                            <path fill-rule="evenodd" d="M7.5 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1H8.5V12a.5.5 0 0 1-1 0V8z"/>
                        </svg>
                    </div>
                    <div class="menu_item no_display" id="search_tab2">Результаты поиска</div>
                    @if($friends_block)
                    <div id="mainSearchFrBl">
                        <div class="audioFriendsBlock" id="friendBlockMain"></div>
                    </div>
                    @endif

                    <div class="">
                        <div class="al_audio_player shadow_box">
                            <table><tbody>
                                <tr>
                                    <td class="al_audio_tb_col1">
                                        <div class="bigPlay_but" id="pl_play">
                                            <div class="icon icon-play-1"></div>
                                        </div>
                                        <div class="al_audio_nav prev icon-fast-bw" id="pl_prev"></div>
                                        <div class="al_audio_nav next icon-fast-fw" id="pl_next"></div>
                                    </td>
                                    <td class="al_audio_tb_col2">
                                        <div class="al_audio_info_wrap">
                                            <div class="al_audio_names_wrap">
                                                <div class="al_audio_time fl_r" id="pl_time">0:00</div>
                                                <div class="audio_names" id="pl_names"><b>Artist</b> - title of the song</div>
                                                <div class="clear"></div>
                                            </div>
                                            <div class="al_audio_progress_wrap" id="pl_progress_bl">
                                                <div class="bg_line"></div>
                                                <div class="load_line" id="pl_load_line"></div>
                                                <div class="play_line" id="pl_play_line"><div class="slider" id="pl_slider"></div></div>
                                                <div class="audioTimesAP" id="pl_time_bl"><div class="audioTAP_strlka">3:00</div></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="al_audio_tb_col3">
                                        <div class="al_audio_info_wrap volume">
                                            <div class="al_audio_progress_wrap" id="pl_volume">
                                                <div class="bg_line"></div>
                                                <div class="play_line" id="pl_volume_line"><div class="slider"></div></div>
                                                <div class="audioTimesAP"><div class="audioTAP_strlka">3:00</div></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="al_audio_tb_col4">
                                        <table class="al_audio_tools"><tbody>
                                            <tr>
                                                <td><div class="icon-plus-2" id="pl_add" onmouseover="showTooltip(this, {text: 'Добавить в мой список', shift:[-1,7,0]});"></div></td>
                                                <td><div class="icon-loop-1" id="pl_loop" onmouseover="showTooltip(this, {text: 'Повторять эту композицию', shift:[-1,7,0]});"></div></td>
                                                <td><div class="icon-shuffle-2" id="pl_shuffle" onmouseover="showTooltip(this, {text: 'Случайный порядок', shift:[-1,7,0]});"></div></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="atitle">{{ $audio_title }}</div>
                        <div class="al_audio_result_wrap shadow_box">
                            @if($is_user)
                            <div class="audio_search_bl">
                                <label for="search_audio_val"></label>
                                <input type="text" placeholder="Поиск" onkeyup="audio.search(this.value @if($public), {{ $uid }} @endif )" id="search_audio_val"/>
                                <img src="/images/loading_mini.gif" id="search_preloader" class="no_display" alt=""/>
                            </div>
                            @endif
                            <div id="audios_res">
                                @foreach($sql as $row)
                                    {{ $row['res'] }}
                                @endforeach
                            </div>
                            <div id="load_but"></div>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>

    <div class="clear"></div>
    <script type="text/javascript">
        $(document).ready(function(){
            audio.tabType = '{{ $plname }}';
            audio.init({{ $init }});
            audio.user_id = '{{ $uid }}';
            audio.a_user_fid = '';
            audio.uname = '{{ $user_name }}';
// audio.loadAll({uid}, 0);
            @if($friends_block)
            audio.LoadFriends();
            @endif
        });
    </script>
@endsection