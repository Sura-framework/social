function a() {
    console.log(audio_player);
}

const audio_player = {
    old_type: '',
    players: {},
    aID: 0,
    aInfo: false,
    aOwner: 0,
    aType: '',
    fullID: '',
    inited: false,
    player: false,
    play: false,
    cplay: false,
    pause: true,
    is_html5: false,
    time: 0,
    pr_click: false,
    curTime: 0,
    timeDir: 0,
    playList: false,
    playLists: {},
    currentPos: 0,
    vol: get_cookie('audioVol') || 1,
    loop: false,
    shuffle: false,
    curPL: false,
    init: function (id) {
        const _a = audio_player;
        _a.player = document.getElementById('audioplayer');
        _a.player.addEventListener('canplay', _a.canPlay);
        _a.player.addEventListener('progress', _a.load_progress);
        _a.player.addEventListener('timeupdate', _a.play_progress);
        _a.player.addEventListener('ended', _a.play_finish);
        _a.player.addEventListener('error', function () {
            _a.nextTrack();
            _a.on_error();
        });
        _a.inited = true;
        _a.is_html5 = true;
        _a.player.volume = _a.vol;
        _a.playNew(id);
        $(window).bind('keyup', function (e) {
            if (!e.keyCode) return;
            if (e.keyCode === 179) {
                if (_a.pause) _a.command('play');
                else _a.command('pause');
            } else if (e.keyCode === 176) _a.nextTrack();
            else if (e.keyCode === 177) _a.prevTrack();
        });
    },
    addPlayer: function (d) {

        const _a = audio_player;
        _a.players[d.id] = d;
        if (!_a.inited) _a.init();
        $(d.play_but).bind('click', function () {
            if ($(this).hasClass('play'))
                _a.command('pause');
            else
                _a.command('play');
        });
        $(d.prbl).bind('mousedown', function (e) {
            _a.progressDown(e, d.id);
        }).bind('mousemove', function (e) {
            _a.progressMove(e, d.id);
        }).bind('mouseout', function () {
            $(_a.players[d.id].timeBl).hide();
        });
        $(d.prev).bind('click', _a.prevTrack);
        $(d.next).bind('click', _a.nextTrack);
        $(d.volume).bind('mousedown', _a.volumeDown);
        $(d.add).bind('click', _a.addAudio);
        _a.playLists = {};
        // let i;
        // let j;
        for (var i in d.playList) {
            const pl = d.playList,
                pl_data = {
                    data: [],
                    pname: d.pname
                };
            for (var j in pl) pl_data.data.push(pl[j]);
            _a.playLists[d.playList[i][7]] = pl_data;
            break;
        }
        if (!_a.aInfo) {
            for (var i in d.playList) {
                _a.aID = d.playList[i][1];
                _a.aOwner = d.playList[i][0];
                _a.aInfo = d.playList[i];
                var type = d.playList[i][7];
                _a.fullID = _a.aID + '_' + _a.aOwner + (type ? '_' + type : '');
                _a.time = d.playList[i][5];
                var s = parseInt(d.playList[i][5] % 60),
                    m = parseInt((d.playList[i][5] / 60) % 60);
                $(d.time).html(m + ':' + s);
                if (_a.is_html5) {
                    _a.player.src = d.playList[i][2];
                    _a.player.load();
                }
                _a.compilePlayList(d.playList[i][7]);
                break;
            }
            $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad').addClass('play').addClass('preactiv');
            _a.play = false;
            _a.cplay = false;
        } else {
            if (_a.pause) {
                $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad').addClass('preactiv');
            }
            else _a.command('play', {
                style_only: true
            });
        }
        _a.command('set_info', {
            player: d.id
        });
        const vol_percent = _a.vol * 100;
        $(d.volume_line).css('width', vol_percent + '%');
        $(d.loop).bind('click', _a.clickLoop);
        $(d.shuffle).bind('click', _a.clickShuffle);
        if (_a.loop) $(d.loop).addClass('active');
        if (_a.shuffle) $(d.shuffle).addClass('active');
        _a.check_add();
    },
    clickLoop: function () {
        const _a = audio_player;
        if (_a.loop)
            _a.command('off_loop');
        else
            _a.command('on_loop');
    },
    clickShuffle: function () {
        const _a = audio_player;
        if (_a.shuffle)
            _a.command('off_shuffle');
        else
            _a.command('on_shuffle');
    },
    play_pause: function () {
        const _a = audio_player;
        if (_a.pause)
            _a.command('play');
        else
            _a.command('pause');
    },
    command: function (type, params) {
        // let i;
        const _a = audio_player;
        if (!params) {
            params = {};

        }
        if (type === 'pause') {
            for (var i in _a.players) $(_a.players[i].play_but).removeClass('play');
            $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad').addClass('pause');
            $('#audioMP .playBtn').removeClass('icon-pause').addClass('icon-play-4');
            if (params.style_only) return;
            if (_a.inited) {
                if (_a.is_html5) {
                    _a.play = false;
                    _a.player.pause();
                } else _a.player.pauseAudio();
            }
            _a.pause = true;
        } else if (type === 'play') {
            for (var i in _a.players) $(_a.players[i].play_but).addClass('play');
            $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad').removeClass('pause').removeClass('preactiv').addClass('play');
            $('#player' + _a.fullID).css('display', 'block');

            $('.player' + _a.fullID).css('display', 'block');

            $('#player' + _a.fullID + ' #playerVolumeBar').css('width', (_a.vol * 100) + '%');
            $('#audioMP .playBtn').removeClass('icon-play-4').addClass('icon-pause');
            _a.initMP();
            if (params.style_only) return;
            if (_a.inited) {
                if (_a.cplay) {
                    if (Math.round(_a.player.currentTime) === 0) _a.player.load();
                    _a.player.play();
                } else _a.player.play();
            }
            _a.pause = false;
        } else if (type === 'set_info') {
            if (params.player)
                $(_a.players[params.player].names).html('<b>' + _a.aInfo[3] + '</b> – ' + _a.aInfo[4]);
            else
                for (var i in _a.players) $(_a.players[i].names).html('<b>' + _a.aInfo[3] + '</b> – ' + _a.aInfo[4]);
        } else if (type === 'load_progress') {
            for (var i in _a.players)
                $(_a.players[i].load).css('width', params.p + '%');
            $('#player' + _a.fullID + ' .audioLoadProgress').css('width', params.p + '%');
        } else if (type === 'play_progress') {
            if (_a.pr_click) return;
            for (var i in _a.players)
                $(_a.players[i].pr).css('width', params.p + '%');
            $('#player' + _a.fullID + ' #playerPlayLine').css('width', params.p + '%');
        } else if (type === 'update_time') {
            for (var i in _a.players)
                $(_a.players[i].time).html(params.time);
            $('#audio_time_' + _a.fullID + ', #audio_time_' + _a.fullID + '_pad').html(params.time);
        } else if (type === 'off_loop') {
            _a.loop = false;
            for (var i in _a.players)
                $(_a.players[i].loop).removeClass('active');
        } else if (type === 'on_loop') {
            _a.loop = true;
            for (var i in _a.players)
                $(_a.players[i].loop).addClass('active');
        } else if (type === 'off_shuffle') {
            _a.shuffle = false;
            for (var i in _a.players)
                $(_a.players[i].shuffle).removeClass('active');
        } else if (type === 'on_shuffle') {
            _a.shuffle = true;
            for (var i in _a.players)
                $(_a.players[i].shuffle).addClass('active');
        } else if (type === 'show_add') {
            for (var i in _a.players) {
                $(_a.players[i].add).show();
                if (params.added)
                    $(_a.players[i].add).addClass('icon-ok-3');
                else
                    $(_a.players[i].add).removeClass('icon-ok-3');
            }
        } else if (type === 'hide_add') {
            for (var i in _a.players) $(_a.players[i].add).hide();
        }
    },
    playNew: function (id) {
        const _a = audio_player;
        if (!id) return;
        if (!_a.inited) {
            _a.init(id);
            return;
        }
        id = id.replace('_pad', '');
        if (_a.fullID === id) {
            _a.command(_a.pause ? 'play' : 'pause');
        }
        else {
            let audio_html = $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad');
            if (_a.fullID) {
                audio_html.removeClass('play').removeClass('pause').removeClass('preactiv');
                _a.backTime(_a.fullID, _a.time);
            }
            _a.player.pause();
            _a.player = null;
            $('.audioPlayer').hide();
            const audio_data = id.split('_');
            _a.aID = audio_data[0];
            _a.aOwner = audio_data[1];
            _a.aType = audio_data[2] ? audio_data[2] : '';
            _a.fullID = _a.aID + '_' + _a.aOwner + ((audio_data[2] && audio_data[2] !== 'pad') ? '_' + audio_data[2] : '');
            _a.getInfoFromDom();
            audio_html.addClass('play');
            _a.play = true;
            _a.cplay = false;
            _a.player = document.getElementById('audioplayer');
            _a.command('play', {
                style_only: true
            });
            _a.curTime = 0;
            _a.player.src = _a.aInfo[2];
            _a.player.load();
            _a.command('set_info');
            if (audio_data[3] !== 'pad') {
                _a.compilePlayList(_a.aInfo[7]);
            }
            if (_a.aInfo[8] !== 'page') _a.scrollToAudio();
            try {
                let pl = _a.playlist.data,
                    cnt = 0;
                // let i;
                for (var i in pl) {
                    const uid = pl[i][1] + '_' + pl[i][0] + (pl[i][7] ? '_' + pl[i][7] : '');
                    if (uid === _a.fullID) {
                        _a.currentPos = cnt;
                    }
                    cnt++;
                }
            } catch (e) {
                console.log("err");
            }

            _a.check_add();
        }
    },
    getInfoFromDom: function () {
        let url;
        const _a = audio_player,
            aid = _a.fullID;
        const audio_url_id = $('#audio_url_' + aid);
        const audio_url_id_pad = $('#audio_url_' + aid + '_pad');
        if (audio_url_id.length) {
            url = audio_url_id.val().split(',');
            _a.aInfo = [_a.aOwner, _a.aID, url[0], $('#audio_' + aid + ' #artist').html(), $('#audio_' + aid + ' #name').html(), url[1], audio_url_id.text(), _a.aType, url[2]];
            _a.time = url[1];
        } else if (audio_url_id_pad.length) {
            url = audio_url_id_pad.val().split(',');
            _a.aInfo = [_a.aOwner, _a.aID, url[0], $('#audio_' + aid + '_pad' + ' #artist').html(), $('#audio_' + aid + '_pad' + ' #name').html(), url[1], $('#audio_time_' + aid + '_pad').text(), _a.aType, url[2]];
            _a.time = url[1];
        }
    },
    canPlay: function () {
        const _a = audio_player;
        if (_a.play) {
            _a.player.play();
            _a.pause = false;
        }
        _a.cplay = true;
    },
    play_progress: function (curTime, totalTime) {
        const _a = audio_player;
        if (_a.is_html5) {
            curTime = Math.floor(_a.player.currentTime * 1000) / 1000;
            totalTime = Math.floor(_a.player.duration * 1000) / 1000;
        } else {
            if (isNaN(totalTime))
                totalTime = _a.aInfo[5];
        }
        let percent = Math.ceil(curTime / totalTime * 100);
        percent = Math.min(100, Math.max(0, percent));
        _a.command('play_progress', {
            p: percent
        });
        if (!_a.pause)
            _a.updateTime(curTime, totalTime);
    },
    play_finish: function () {
        const _a = audio_player;
        $('.audioPlayer').hide();
        if (_a.loop) {
            if (_a.is_html5)
                _a.player.play();
            else
                _a.player.playAudio(0);
        } else if (!_a.loop && _a.shuffle) {
            const i = Math.floor(Math.random() * _a.playlist.data.length);
            _a.playToPlayList(i);
        } else _a.nextTrack();
    },
    on_error: function (e) {
        Box.Show('error', 400, 'Ошибка', '<div style="padding: 15px;" dir="auto">При загрузке аудиозаписи произошла ошибка, обновите страницу и попробуйте снова.</div>', 'Закрыть');
    },
    errorPL: function () {
        Box.Show('error', 400, 'Ошибка', '<div style="padding: 15px;" dir="auto">Идет загрузка плейлиста, попробуйте чуть позже..</div>', 'Закрыть');
    },
    end_load: function () {
    },
    load_progress: function (bufferedTime, totalTime) {
        const _a = audio_player;
        if (_a.is_html5) {
            totalTime = Math.floor(_a.player.duration * 1000) / 1000;
            try {
                bufferedTime = (Math.floor(_a.player.buffered.end(0) * 1000) / 1000) || 0;
            } catch (e) {}
        }
        let percent = (bufferedTime / totalTime) * 100;
        _a.command('load_progress', {
            p: percent
        });
    },
    progressDown: function (e1, id) {
        const _a = audio_player;
        var el = typeof id === 'string' ? _a.players[id].prbl : id,
            left = $(el).offset().left,
            w = $(el).width(),
            percent;
        function Move(e) {
            e.preventDefault();
            var l = Math.min(Math.max(0, e.pageX - left - 1), w),
                p = (l / w) * 100;
            percent = p;
            for (var i in _a.players) $(_a.players[i].pr).css('width', p + '%');
            $('#player' + _a.fullID + ' #playerPlayLine').css('width', p + '%');
        }
        function Up(ev) {
            cancelEvent(ev);
            $(window).unbind('mousemove', Move).unbind('mouseup', Up);
            var time = (_a.time * percent) / 100;
            _a.setTime(time);
            _a.pr_click = false;
            if (typeof id === 'string') $(_a.players[id].slider).hide();
        }
        _a.pr_click = true;
        Move(e1);
        if (typeof id === 'string') $(_a.players[id].slider).show();
        $(window).bind('mousemove', Move).bind('mouseup', Up);
    },
    progressMove: function (e, id) {
        const _a = audio_player;
        var el = _a.players[id].prbl,
            left = $(el).offset().left,
            w = $(el).width(),
            l = Math.min(Math.max(0, e.pageX - left - 1), w),
            p = (l / w) * 100,
            time = (_a.time * p) / 100;
        $(_a.players[id].timeBl).css('left', p + '%').show();
        var s = parseInt(time % 60),
            m = parseInt((time / 60) % 60);
        if (s < 10) s = '0' + s;
        $(_a.players[id].timeBl).children('.audioTAP_strlka').html(m + ':' + s);
    },
    setTime: function (time) {
        const _a = audio_player;
        if (_a.is_html5) {
            _a.player.currentTime = time;
            if (!_a.pause) _a.player.play();
        } else {
            _a.player.playAudio(time);
            if (_a.pause) _a.player.pauseAudio();
        }
    },
    updateTime: function (cur, len) {
        const _a = audio_player;
        if (_a.preloadUrl) return;
        _a.curTime = cur;
        var cur_time = _a.timeDir ? cur : (len - cur);
        var s = parseInt(cur_time % 60),
            m = parseInt((cur_time / 60) % 60);
        if (s < 10) s = '0' + s;
        var resTime = (_a.timeDir ? '' : '-') + m + ':' + s;
        if (parseFloat(resTime) != 'NaN') _a.command('update_time', {
            time: resTime
        });
    },
    backTime: function (id, time) {
        const _a = audio_player;
        var s = parseInt(time % 60),
            m = parseInt((time / 60) % 60);
        if (parseFloat(m) != 'NaN' || parseFloat(s) != 'NaN') $('#audio_time_' + id + ', #audio_time_' + _a.fullID + '_pad').html(m + ':' + s);
    },
    compilePlayList: function (type) {
        const _a = audio_player;
        _a.curPL = type;
        if (type) {
            _a.startLoadPL = true;
            if (type === 'search') {
                var start = false,
                    cnt = 0,
                    res = [];
                if ($('#audios_res .audio').length) {
                    $('#audios_res .audio').each(function () {
                        var aid = $(this).attr('id').replace('audio_', ''),
                            adata = aid.split('_');
                        if (cnt < 60) {
                            if (adata[0] === _a.aID) _a.currentPos = cnt;
                            var url = $('#audio_url_' + aid).val().split(',');
                            var inf = [adata[1], adata[0], url[0], $('#audio_' + aid + ' #artist').html(), $('#audio_' + aid + ' #name').html(), url[1], $('#audio_time_' + aid).text(), 'search', url[2]];
                            res.push(inf);
                            cnt++
                        } else return;
                    });
                } else {
                    $('#page .audioPage').each(function () {
                        var aid = $(this).attr('id').replace('audio_', ''),
                            adata = aid.split('_');
                        if (cnt < 60) {
                            if (adata[0] === _a.aID) _a.currentPos = cnt;
                            var url = $('#audio_url_' + aid).val().split(',');
                            var inf = [adata[1], adata[0], url[0], $('#audio_' + aid + ' #artist').html(), $('#audio_' + aid + ' #name').html(), url[1], $('#audio_time_' + aid).text(), 'search', url[2]];
                            res.push(inf);
                            cnt++
                        } else return;
                    });
                }
                _a.playlist = {
                    data: res,
                    name: 'Сейчас играют результаты поиска'
                };
                window.cur.audios = _a.playlist;
                _a.startLoadPL = false;
            } else if (type === 'attach') {
                _a.playlist = {
                    data: [_a.aInfo],
                    name: ''
                };
                _a.currentPos = 0;
                _a.startLoadPL = false;
            } else if (type === 'wall') {
                var res = [],
                    cur = 0,
                    cnt = 0;
                $('#audio_' + _a.fullID).parent().children('.audioPage').each(function () {
                    var aid = this.id.replace('audio_', ''),
                        adata = aid.split('_');
                    if (aid === _a.fullID) {
                        cur = cnt;
                        _a.currentPos = cnt;
                    }
                    cnt++;
                    var url = $('#audio_url_' + aid).val().split(',');
                    res.push([adata[1], adata[0], url[0], $('#audio_' + aid + ' #artist').html(), $('#audio_' + aid + ' #name').html(), url[1], $('#audio_time_' + aid).text(), 'wall', url[2]]);
                });
                _a.startLoadPL = false;
                _a.playlist = {
                    data: res,
                    name: ''
                };
            } else {
                if (!kj.uid) return;
                $.post('/audio/load_play_list/', {
                    data: _a.fullID
                }, function (d) {
                    d = JSON.parse(d);
                    _a.playlist = {
                        data: d.playList,
                        name: d.pname
                    };
                    var pl = d.playList,
                        cnt = 0;
                    for (var i in pl) {
                        var id = pl[i][1] + '_' + pl[i][0] + (pl[i][7] ? '_' + pl[i][7] : '');
                        if (id === _a.fullID) {
                            _a.currentPos = cnt;
                        }
                        cnt++;
                    }
                    _a.startLoadPL = false;
                });
            }
        }
    },
    nextTrack: function () {
        const _a = audio_player;
        if (_a.startLoadPL) {
            _a.errorPL();
            return;
        }
        var nid = _a.currentPos + 1;
        if (!_a.playlist.data[nid]) nid = 0;
        _a.playToPlayList(nid);
    },
    prevTrack: function () {
        const _a = audio_player;
        if (_a.startLoadPL) {
            _a.errorPL();
            return;
        }
        var nid = _a.currentPos - 1;
        if (!_a.playlist.data[nid]) nid = _a.playlist.data.length - 1;
        _a.playToPlayList(nid);
    },
    playToPlayList: function (i) {
        const _a = audio_player;
        if (_a.fullID) {
            $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad').removeClass('play').removeClass('pause').removeClass('preactiv');
            _a.backTime(_a.fullID, _a.time);
        }
        $('.audioPlayer').hide();
        _a.currentPos = i;
        _a.aID = _a.playlist.data[i][1];
        _a.aOwner = _a.playlist.data[i][0];
        _a.aType = _a.playlist.data[i][7] ? _a.playlist.data[i][7] : '';
        _a.fullID = _a.aID + '_' + _a.aOwner + (_a.playlist.data[i][7] ? '_' + _a.playlist.data[i][7] : '');
        _a.aInfo = [_a.aOwner, _a.aID, _a.playlist.data[i][2], _a.playlist.data[i][3], _a.playlist.data[i][4], _a.playlist.data[i][5], _a.playlist.data[i][6], _a.aType, _a.playlist.data[i][7]];
        _a.time = _a.playlist.data[i][5];
        $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad').addClass('play');
        _a.play = true;
        _a.cplay = false;
        _a.command('play', {
            style_only: true
        });
        _a.curTime = 0;
        if (_a.is_html5) {
            _a.player.src = _a.aInfo[2];
            audio_player.player.load();
        } else {
            _a.player.loadAudio(_a.aInfo[2]);
            _a.pause = false;
        }
        _a.command('set_info');
        if (_a.aInfo[8] != 'page' && _a.aInfo[7] != 'wall') _a.scrollToAudio();
        _a.check_add();
    },
    scrollToAudio: function () {
        const _a = audio_player;
        if ($('#audio_' + _a.fullID).length) {
            var top = $('#audio_' + _a.fullID).offset().top,
                h = ($(window).height() / 2),
                r = top - h;
            $('body').animate({
                scrollTop: r
            }, 200);
        }
    },
    volumeDown: function (e1, elem) {
        cancelEvent(e1);
        const _a = audio_player;
        var el = elem ? elem : this,
            left = $(el).offset().left,
            w = $(el).width(),
            pbl = $(el).children('.audioTimesAP').get(0),
            pblstr = $(pbl).children('.audioTAP_strlka'),
            vol;
        pbl = $(pbl);
        function Move(e) {
            e.preventDefault();
            var l = Math.min(Math.max(0, e.pageX - left - 1), w),
                p = (l / w) * 100;
            for (var i in _a.players) $(_a.players[i].volume_line).css('width', p + '%');
            $('#player' + _a.fullID + ' #playerVolumeBar').css('width', p + '%');
            var str = Math.round(p);
            vol = p / 100;
            _a.vol = vol;
            if (_a.is_html5) _a.player.volume = vol;
            else _a.player.setVolume(vol);
            var l1 = (p * w) / 100 - (pblstr.width() / 2) - 6 + (elem ? 17 : 0);
            pbl.css('left', l1 + 'px');
            pblstr.html(str + '%');
        }
        function Up(ev) {
            cancelEvent(ev);
            $(window).unbind('mousemove', Move).unbind('mouseup', Up);
            pbl.hide();
            var d = new Date(),
                date = d.getDate() + 5,
                month = d.getMonth(),
                year = d.getFullYear();
            set_cookie('audioVol', vol, year, month, date);
        }
        pbl.show();
        $(window).bind('mousemove', Move).bind('mouseup', Up);
        Move(e1);
    },
    playerPrMove: function (e, el) {
        const _a = audio_player;
        _a.mouseoverProgress = true;
        var elem = $(el),
            pos = e.clientX,
            w = elem.width(),
            left = elem.offset().left;
        pos = pos - left;
        var val = (pos / w) * 100;
        var curTime = val / 100 * _a.time,
            prTP = elem.children('.audioTimesAP'),
            prTPtext = prTP.children('.audioTAP_strlka');
        $('.audioTimesAP').hide();
        var s = parseInt(curTime % 60),
            m = parseInt((curTime / 60) % 60);
        if (s < 10) s = '0' + s;
        prTPtext.html(m + ':' + s);
        var left = val / 100 * w;
        prTP.css('left', (left - (prTPtext.width() / 2)) + 'px').show();
    },
    playerPrOut: function () {
        let _a = audio_player;
        _a.mouseoverProgress = false;
        $('.audioTimesAP').hide();
    },
    //pad
    initedPad: false,
    initedMP: false,
    padScroll: false,
    initMP: function () {
        const _a = audio_player;
        if (!_a.initedMP) {
            _a.initedMP = true;
            var content = '\
			<div class="cButs">\
			<button class="nextPrevBtn icon-fast-bw" id="no_play" onClick="audio_player.prevTrack();"></button>\
			<button class="playBtn icon-pause" id="no_play" onClick="audio_player.play_pause()"></button>\
			<button class="nextPrevBtn icon-fast-fw" id="no_play" onClick="audio_player.nextTrack();"></button>\
			</div>\
			<div class="min_player_names"><div class="row" style="padding: 10px 10px;"><div class="col-12 text-truncate" style="width: 135px;"><span id="minPlayerArtist"></span> – <span id="minPlayerName"></span></div></div>\
			</div>\
			';
            $('#audioMP').html(content).attr('onClick', 'audio_player.showPad(event)');
        }
        $('#audioMP').addClass('show');
        if (_a.aInfo) {
            $('#minPlayerArtist').html(_a.aInfo[3]);
            $('#minPlayerName').html(_a.aInfo[4]);
        }
    },
    showPad: function (e) {
        const _a = audio_player;
        if (e && e.target.id === 'no_play') return;
        if (!_a.initedPad) {
            _a.initedPad = true;
            var content = '<div class="audio_head">\
				<div class="bigPlay_but icon-play-1 mt-3" id="pad_play"></div>\
				<div class="prevision icon-fast-bw" id="pad_prev"></div>\
				<div class="prevision next icon-fast-fw" id="pad_next"></div>\
				<div class="fl_l" style="width:268px;margin-left: 14px;margin-top: 2px;" id="pad_cont_progress">\
					<div>\
						<div class="names fl_l" id="pad_names"></div>\
						<div class="fl_r time" id="pad_time">0:00</div>\
						<div class="clear"></div>\
					</div>\
					<div class="audio_progres_bl" id="pad_progress_bl">\
						<div class="bg"></div>\
						<div class="play" id="pad_play_line"><div class="slider" id="pad_slider"></div></div>\
						<div class="load" id="pad_load_line"></div>\
						<div class="audioTimesAP" id="pad_time_bl"><div class="audioTAP_strlka">3:00</div></div>\
					</div>\
				</div>\
				<div class="volume_bar ml-3" id="pad_volume">\
					<div class="volume_bg"></div>\
					<div class="volume_line" id="pad_volume_line"><div class="slider"></div></div>\
					<div class="audioTimesAP"><div class="audioTAP_strlka">3:00</div></div>\
				</div>\
				<div class="fl_l plcontols_buts">\
					<li class="icon-plus-6" id="pad_add" onmouseover="showTooltip(this, {text: \'Добавить в мой список\', shift:[0,5,0]});"></li>\
					<li class="icon-loop-1" id="pad_loop" onmouseover="showTooltip(this, {text: \'Повторять эту композицию\', shift:[0,5,0]});"></li>\
					<li class="icon-shuffle-2" id="pad_shuffle" onmouseover="showTooltip(this, {text: \'Случайный порядок\', shift:[0,5,0]});"></li>\
					<div class="clear"></div>\
				</div>\
			</div><div style="position:relative;"><div class="rightStrelka"></div></div>\
			<div class="audios_scroll_bl" id="pad_scroll">\
				<div id="audios_scroll_cont" class="scroller_cont"><div id="audioPadRes"></div></div>\
			</div>\
			<div class="padFooter">\
				<div class="plName fl_l"></div>\
				<div class="button_div fl_r" style="margin-right: 5px;margin-top: 2px;"><button onClick="audio_player.showPad();">Закрыть</button></div>\
				</div><div></div><div></div></div>\
				<div class="clear"></div>\
			</div>';
            $('#audioPad').html(content);

            var data = {
                id: 'pad',
                play_but: $('#pad_play').get(0),
                names: $('#pad_names').get(0),
                pr: $('#pad_play_line').get(0),
                load: $('#pad_load_line').get(0),
                prbl: $('#pad_progress_bl').get(0),
                slider: $('#pad_slider').get(0),
                timeBl: $('#pad_time_bl').get(0),
                time: $('#pad_time').get(0),
                prev: $('#pad_prev').get(0),
                next: $('#pad_next').get(0),
                volume: $('#pad_volume').get(0),
                volume_line: $('#pad_volume_line').get(0),
                loop: $('#pad_loop').get(0),
                shuffle: $('#pad_shuffle').get(0),
                add: $('#pad_add').get(0)
            };
            _a.addPlayer(data);
            _a.padScroll = new Scroller('pad_scroll');
            var _s = _a;
        }
        if ($('#audioPad').hasClass('show')) {
            $('#audioPad').removeClass('show');
            $('#audioMP').removeClass('active');
            $('#audioPadRes').html('');
        } else {
            $('#audioPad').css('margin-left', 'auto').addClass('show');
            $('#audioMP').addClass('active');
            if (_a.aType === 'wall' || _a.aType === 'search' || _a.aType === 'attach') {
                var pl = _a.playlist.data,
                    res = '';
                for (var i = 0; i < pl.length; i++) res += _a.compile_audio(pl[i]);
                $('#audioPadRes').html(res);
                $('.plName').html(_a.playlist.name);
                if (_a.pause) $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad').addClass('preactiv');
                else _a.command('play', {
                    style_only: true
                });
                if (_a.play) $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad').removeClass('preactiv');
                if ($('#pad_add').css('display') === 'none') $('#pad_cont_progress').css('width', '268px');
                else $('#pad_cont_progress').css('width', '245px');
                _a.padScroll.check_scroll();
            } else {
                $.post('/audio/load_play_list/', {
                    data: _a.fullID
                }, function (d) {
                    d = JSON.parse(d);
                    _a.playlist = {
                        data: d.playList,
                        name: d.pname
                    };
                    var pl = d.playList,
                        cnt = 0,
                        res = '';
                    for (var i in pl) {
                        var id = pl[i][1] + '_' + pl[i][0] + (pl[i][7] ? '_' + pl[i][7] : '');
                        if (id === _a.fullID) _a.currentPos = cnt;
                        if (cnt > 50) break;
                        cnt++;
                        res += _a.compile_audio(pl[i]);
                    }
                    if (pl.length > 50) res += '<div onclick="audio_player.show_more(' + cnt + '); return false;" class="public_wall_all_comm" id="audio_show_more">Показать больше аудиозаписей</div>';
                    _a.startLoadPL = false;
                    $('#audioPadRes').html(res);
                    $('.plName').html(_a.playlist.name);
                    if (_a.pause) $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad').addClass('preactiv');
                    else _a.command('play', {
                        style_only: true
                    });
                    if (_a.play) $('#audio_' + _a.fullID + ', #audio_' + _a.fullID + '_pad').removeClass('preactiv');
                    if ($('#pad_add').css('display') === 'none') $('#pad_cont_progress').css('width', '268px');
                    else $('#pad_cont_progress').css('width', '245px');
                    _a.padScroll.check_scroll();
                });
            }
        }
        _a.padScroll.check_scroll();
    },
    show_more: function (offset) {
        $('#audio_show_more').remove();
        const _a = audio_player;
        var pl = _a.playlist.data,
            cnt = 0,
            num = 0,
            res = '';
        for (var i in pl) {
            cnt++;
            if (cnt > offset && num < 51) {
                num++;
                res += _a.compile_audio(pl[i]);
            }
        }
        var q = parseInt(num) + parseInt(offset);
        if (_a.playlist.data.length > q) res += '<div onclick="audio_player.show_more(' + q + '); return false;" class="public_wall_all_comm" id="audio_show_more">Показать больше аудиозаписей</div>';
        $('#audioPadRes').append(res);
        _a.padScroll.check_scroll();
    },
    compile_audio: function (d) {
        if (d[1]) {
            const _a = audio_player;
            var full_id = d[1] + '_' + d[0] + '_' + d[7] + '_pad';
            if (d[7] === 'audios' + kj.uid) var add = '',
                hclass = 'no_tools';
            else var add = '<li class="icon-plus-6 ' + hclass + '" onclick="audio.add(\'' + full_id + '\')" id="add_tt_' + full_id + '" onmouseover="titleHtml({text: \'Добавить аудиозапись\', id: this.id, top: 29, left: 12})"></li>',
                hclass = '';
            if (_a.fullID === d[1] + '_' + d[0] + '_' + d[7]) {
                if (_a.play) {
                    hclass += ' play';
                    _a.command('play', {
                        style_only: true
                    });
                } else {
                    hclass += ' pause';
                    _a.command('pause', {
                        style_only: true
                    });
                }
            }
            return '<div class="audio ' + hclass + '" id="audio_' + full_id + '" onclick="playNewAudio(\'' + full_id + '\', event);">\
			<div class="audio_cont">\
				<div class="play_btn icon-play-4"></div>\
				<div class="name"><span id="artist">' + d[3] + '</span> – <span id="name">' + d[4] + '</span></div>\
				<div class="fl_r">\
					<div class="time" id="audio_time_' + full_id + '">' + d[6] + '</div>\
					<div class="tools">' + add + '\
						<div class="clear"></div>\
					</div>\
				</div>\
				<input type="hidden" value="' + d[2] + ',' + d[5] + ',pad" id="audio_url_' + full_id + '"/>\
				<div class="clear"></div>\
			</div>\
			<div id="audio_text_res"></div>\
		</div>';
        } else return '';
    },
    added: {},
    check_add: function () {
        let _a = audio_player;
        var type = _a.fullID.split('_');
        if (type[2] === 'public') {
            if (!_a.added[_a.aID]) _a.command('show_add', {
                added: false
            });
            else if (_a.added[_a.aID]) _a.command('show_add', {
                added: true
            });
        } else {
            if (_a.aOwner != kj.uid && !_a.added[_a.aID]) _a.command('show_add', {
                added: false
            });
            else if (_a.aOwner != kj.uid && _a.added[_a.aID]) _a.command('show_add', {
                added: true
            });
            else _a.command('hide_add');
        }
    },
    addAudio: function () {
        const _a = audio_player;
        if (_a.added[_a.aID]) return;
        _a.added[_a.aID] = true;
        $('#pad_add, #pl_add').addClass('icon-ok-3').removeClass('icon-plus-6');
        $('#audio_' + _a.fullID + ' .tools, #audio_' + _a.fullID + '_pad .tools').html('<li class="icon-ok-3" style="padding-top: 2px;font-size: 16px;"></li><div class="clear"></div>');
        $.post('/audio/add/', {
            id: _a.aID
        });
        $('.titleHtml').remove();
    },
    get_text: function (id, el) {
        if (el && !$(el).hasClass('text_avilable')) return;
        const tbl = $('#audio_' + id + ' #audio_text_res');
        if (tbl.hasClass('opened')) tbl.removeClass('opened');
        else {
            tbl.addClass('opened');
            const html = tbl.html();
            if (html.length === 0) {
                tbl.html('<div style="padding:20px 0;text-align:center;"><img src="/images/loading_mini.gif"></div>');
                $.post('/audio/get_text/', {
                    id: id
                }, function (d) {
                    tbl.html(d);
                });
            }
        }
    }
};


const audio = {
    user_id: 0,
    a_user_fid: 0,
    init: function (d) {
        $.extend(d, {
            play_but: $('#pl_play').get(0),
            names: $('#pl_names').get(0),
            pr: $('#pl_play_line').get(0),
            load: $('#pl_load_line').get(0),
            prbl: $('#pl_progress_bl').get(0),
            slider: $('#pl_slider').get(0),
            timeBl: $('#pl_time_bl').get(0),
            time: $('#pl_time').get(0),
            prev: $('#pl_prev').get(0),
            next: $('#pl_next').get(0),
            volume: $('#pl_volume').get(0),
            volume_line: $('#pl_volume_line').get(0),
            loop: $('#pl_loop').get(0),
            shuffle: $('#pl_shuffle').get(0),
            add: $('#pl_add').get(0)
        });
        audio_player.addPlayer(d);
        audio.load_page = 1;
        $(window).scroll(function () {
            if (!audio.start_load && $(window).scrollTop() + $(window).height() >= $(document).height()) {
                if (audio.moreSaerch) audio.loadMoreSearch();
                else audio.loadMore();
            }
        });
    },
    change_tab: function (type) {
        $('#search_tab2').hide();
        $('#search_preloader').show();
        $('.audio_menu li').removeClass('active');
        $('.menu_item.active').removeClass('active');
        $('#friendBlockMain li').removeClass('audioFrActive');
        $('#' + type).addClass('active');
        const url = '/audio/' + type + '/';
        history.pushState({
            link: url
        }, null, url);
        this.load_page = 1;
        this.start_load = false;
        audio.tabType = type;
        $('#search_audio_val').val('');
        this.moreSaerch = false;
        if (type === 'my_music' && kj.uid === audio.user_id && audio.loaded_len > 0) {
            $('#search_preloader').hide();
            var text = kj.uid === audio.user_id ? 'У вас' : 'У ' + audio.uname;
            $('#atitle').html('<div class="audio_page_title">' + text + ' ' + declOfNum(audio.loaded_len, ['аудиозапись', 'аудиозаписи', 'аудиозаписей']) + '</div>');
            var len = Math.min(40, audio.loaded_len),
                result = '',
                tpl, res = audio.audiosRes;
            for (var i = 0; i < len; i++) {
                tpl = str_replace(['{id}', '{uid}', '{plname}', '{artist}', '{name}', '{stime}', '{time}', '{url}', '{is_text}'], [res[i][1], res[i][0], res[i][7], res[i][3], res[i][4], res[i][6], res[i][5], res[i][2], res[i][9] ? 'text_avilable' : ''], audio.tpl_audio);
                tpl = tpl.replace(/\[tools\](.*?)\[\/tools\]/gim, kj.uid === audio.user_id ? '$1' : '');
                tpl = tpl.replace(/\[add\](.*?)\[\/add\]/gim, kj.uid === audio.user_id ? '' : '$1');
                result += tpl;
            }
            $('#audios_res').html(result);
            var _a = audio_player;
            _a.playLists = {};
            _a.playLists['audios' + kj.uid] = {
                data: res,
                pname: 'Сейчас играют аудиозаписи ' + audio.uname + ' | ' + declOfNum(audio.loaded_len, 'аудиозапись', 'аудиозаписи', 'аудиозаписей'),
            };
        } else {
            $.post(url, {doload: 1, ajax: 'yes'}, function (d) {
                $('#search_preloader').hide();
                d = JSON.parse(d);
                $('#atitle').html(d.title);
                $('#audios_res').html(d.result);
                $('#load_but').html(d.but);
                const _a = audio_player;
                _a.playLists = {};
                const type = audio.tabType === 'my_music' ? 'audios' + kj.uid : audio.tabType;
                _a.playLists[type] = {
                    data: [],
                    pname: d.pname
                };
                // let i;
                for (var i in d.playList)
                    _a.playLists[type].data.push(d.playList[i]);
                if (_a.pause)
                    $('#audio_' + _a.fullID).addClass('preactiv');
                else
                    _a.command('play', {
                        style_only: true
                    });
                audio.loadAll(kj.uid, 0);
            });
        }
        audio.user_id = kj.uid;
        audio.uname = kj.name;
    },
    openFriends: function (uid, fid) {
        $('#search_tab2').hide();
        $('#search_preloader').show();
        $('.audio_menu li').removeClass('active');
        $('.menu_item.active').removeClass('active');
        var url = '/audio' + uid,
            old_uid = this.user_id;
        this.user_id = uid;
        this.a_user_fid = fid;
        history.pushState({
            link: url
        }, null, url);
        this.load_page = 1;
        this.start_load = false;
        this.uname = $('#user_' + fid + ' .audioFriendsBlockName').html();
        $('#search_audio_val').val('');
        this.moreSaerch = false;
        $('#friendBlockMain li').removeClass('audioFrActive');
        $('#friendBlockMain #user_' + fid).addClass('audioFrActive');
        $.post(url, {
            doload: 1
        }, function (d) {
            d = JSON.parse(d);
            $('#search_preloader').hide();
            $('#atitle').html(d.title);
            $('#audios_res').html(d.result);
            $('#load_but').html(d.but);
            var _a = audio_player;
            _a.playLists = {};
            _a.playLists['audios' + uid] = {
                data: [],
                pname: d.pname
            };
            for (var i in d.playList) _a.playLists['audios' + uid].data.push(d.playList[i]);
            if (_a.pause) $('#audio_' + _a.fullID).addClass('preactiv');
            else _a.command('play', {
                style_only: true
            });
            audio.loadAll(audio.user_id, 0);
        });
    },
    load_page: 1,
    start_load: false,
    loadMore: function () {
        if (this.start_load) return;
        this.start_load = true;
        if (this.tabType != 'my_music') return this.moreOther();
        if (!audio.searchResult) audio.searchResult = {
            cnt: audio.loaded_len,
            data: audio.audiosRes
        };
        var offset = audio.load_page * 40,
            len = Math.min(audio.searchResult.cnt, offset + 40),
            result = '',
            res = audio.searchResult.data;
        for (var i = offset; i < len; i++) {
            tpl = str_replace(['{id}', '{uid}', '{plname}', '{artist}', '{name}', '{stime}', '{time}', '{url}', '{is_text}'], [res[i][1], res[i][0], res[i][7], res[i][3], res[i][4], res[i][6], res[i][5], res[i][2], res[i][9] ? 'text_avilable' : ''], audio.tpl_audio);
            tpl = tpl.replace(/\[tools\](.*?)\[\/tools\]/gim, kj.uid === audio.user_id ? '$1' : '');
            tpl = tpl.replace(/\[add\](.*?)\[\/add\]/gim, kj.uid === audio.user_id ? '' : '$1');
            result += tpl;
        }
        $('#audios_res').append(result);
        audio.load_page++;
        if (result) audio.start_load = false;
        else $('#audio_more_but').remove();
    },
    moreOther: function () {
        var but = $('#audio_more_but');
        but.html('<img src="/images/loading_mini.gif">');
        $.post(location.href, {
            doload: 1,
            more: 1,
            page: audio.load_page
        }, function (d) {
            d = JSON.stringify(d);

            audio.load_page++;
            if (d.result) {
                but.html('Показать больше');
                $('#audios_res').append(d.result);
                audio.start_load = false;
            } else but.remove();
        });
    },
    moreSaerch: false,
    search: function (val, pid) {
        if (!pid) pid = 0;
        audio.searchClient(val, pid);
    },
    loadMoreSearch: function () {
        if (this.start_load) return;
        this.start_load = true;
        $('#audio_more_but').html('<img src="/images/loading_mini.gif"/>');
        var q = $('#search_audio_val').val();
        $.post('/audio/search_all/', {
            doload: 1,
            page: this.load_page,
            q: q,
            more: 1
        }, function (d) {
            audio.load_page++;
            d = JSON.parse(d);
            if (d.search) {
                audio.start_load = false;
                $('#audios_res').append(d.search);
                $('#audio_more_but').html('Показать больше');
                var _a = audio_player,
                    type = _a.aInfo[7];
                for (var i = 0; i < d.audios; i++) {
                    if (type === 'search') cur.audios.data.push(d.audios[i]);
                    _a.playLists['search'].data.push(d.audios[i]);
                }
                _a.playList.data = cur.audios;
            } else $('#audio_more_but').remove();
        });
    },
    edit_box: function (id) {
        Page.Loading('start');
        $('.titleHtml').remove();
        var q = id.split('_');
        aid = q[0];
        $.post('/audio/get_info/', {
            id: aid
        }, function (d) {
            d = JSON.parse(d);
            Page.Loading('stop');
            if (d.error) addAllErr('Неизвестная ошибка');
            else {
                var content = '<div style="padding: 15px;background: #EEF0F2;">\
				<div class="audioEditDescr">Исполнитель:</div><input type="text" class="audioEditInput" id="audio_artist" value="' + d.artist + '"/><div class="clear"></div>\
				<div class="audioEditDescr">Название:</div><input type="text" class="audioEditInput" id="audio_name" value="' + d.name + '"/><div class="clear"></div>\
				<a href="/" class="audio_edit_more_btn" onClick="audio.showMoreSettings(this); return false;">Дополнительно</a>\
				<div id="audio_edit_more" class="no_display">\
				<div class="audioEditDescr">Жанр:</div><div id="audio_genre" style="width: 281px;" class="kjSelector fl_l"></div><div class="clear"></div><br/>\
				<div class="audioEditDescr">Текст:</div><textarea class="audioEditInput" id="audio_text">' + (d.text ? str_replace(['<br>', '<br />'], ['\n', '\n'], d.text) : '') + '</textarea><div class="clear"></div>\
				</div>\
				<div class="audioEditDescr"> </div><div class="button_div fl_l"><button onClick="audio.save_audio(\'' + id + '\', ' + aid + ')" id="saveabutton">Сохранить</button></div><div class="clear"></div>\
				</div>\
				<style>#audio_genre .kjSelectorTop{padding: 10px 10px}#audio_genre li{padding: 6px 10px}</style>';
                Box.Show('audio_edit', 440, 'Редактирование аудиозаписи', content, 'Закрыть');
                cur.selects = {};
                cur.selects['audio_genre'] = new Selector({
                    id: 'audio_genre',
                    data: d.genres,
                    def: d.genre
                });
            }
        });
    },
    showMoreSettings: function (el) {
        $(el).remove();
        $('#audio_edit_more').show();
    },
    save_audio: function (id, aid) {
        var artist = $('#audio_artist').val(),
            name = $('#audio_name').val(),
            genre = $('#audio_genre').val(),
            text = $('#audio_text').val();
        if (!artist) {
            setErrorInputMsg('audio_artist');
            return;
        }
        if (!name) {
            setErrorInputMsg('audio_name');
            return;
        }
        $('#saveabutton').html('<img src="/images/loading_mini.gif"/>').attr('onClick', '');
        $.post('/audio/save_edit/', {
            id: aid,
            genre: genre,
            artist: artist,
            name: name,
            text: text
        }, function () {
            Box.Close('audio_edit');
            $('#audio_' + id + ' #artist').html(artist);
            $('#audio_' + id + ' #name').html(name);
        });
    },
    delete_box: function (id, pid) {
        if (!pid) pid = 0;
        $('.titleHtml').remove();
        var aid = id.split('_');
        aid = aid[0];
        Box.Show('del', 400, 'Удаление аудиозаписи', '<div style="padding: 15px">Вы действительно хотите удалить эту аудиозапись?</div>', 'Отмена', 'Да, удалить', 'audio.start_delete(\'' + id + '\', ' + aid + ', ' + pid + ')');
    },
    start_delete: function (id, aid, pid) {
        $('#box_del .button_div_gray').remove();
        $('#box_del .button_div').html('<img src="/images/loading_mini.gif"/>');
        $.post('/audio/del_audio/', {
            id: aid
        }, function (d) {
            if (d === 'error') addAllErr('Неизвестная ошибка');
            else if (pid) Page.Go('/public/audio' + pid);
            else Page.Go('/audio/');
        });
    },
    uploadBox: function (pid) {
        Page.Loading('start');
        if (!pid) type = 'audio/upload_box/';
        else type = '/public_audio/id=' + pid;
        $.post('/' + type, {
            act: 'upload_box'
        }, function (d) {
            Box.Show('upload', 475, 'Добавление новой песни', d, 'Отмена');
            Page.Loading('stop');
            $(document).bind('drop', audio.onDropFile).bind('dragover', audio.dragOver);
            $('.audio_drop_wrap').bind('dragenter', audio.dragOver).bind('dragleave', audio.dragOut);
        });
    },
    onDropFile: function (e) {
        e = e || window.event;
        cancelEvent(e);
        $('.audio_upload_drop').hide();
        $('.chat_tab').show();
        audio.onFile(e.dataTransfer.files);
        return false;
    },
    dragOver: function (e) {
        e = e || window.event;
        $('.audio_upload_drop').show();
        $('.chat_tab').hide();
        cancelEvent(e);
        return false;
    },
    dragOut: function (e) {
        e = e || window.event;
        $('.audio_upload_drop').hide();
        $('.chat_tab').show();
        cancelEvent(e);
        return false;
    },
    audioUploadErrorBox: function (str) {
        Box.Show('err', 450, 'Ошибка', '<div style="padding:15px;line-height:160%;">' + str + '</div>', 'Закрыть');
    },
    onUpload: function () {
        let button = $('#upload');
        const uploader = new ss.SimpleUpload({
            button: button, // HTML element used as upload button
            url: '/audio/upload/', // URL of server-side upload handler
            name: 'uploadfile', // Parameter name of the uploaded file
            hoverClass: 'hover',
            focusClass: 'focus',
            multipart: true,
            allowedExtensions: ['mp3'], // for example, if we were uploading pics
            onComplete: function (filename, response) {
                response = JSON.parse(response);

            },
            onError: function () {
                // progressOuter.style.display = 'none';
                // msgBox.innerHTML = 'Unable to upload file';
                console.log('Unable to upload file');
            }
        });
        Page.Loading('stop');
    },
    onFile: function (e, pid) {
        if (!pid) pid = 0;
        var _a = audio,
            files = e.files,
            len = files.length,
            maxlen = 500;
        if (!len) return;
        var queue = [];
        if (len > maxlen) {
            var err_msg = 'За один раз Вы не можете загрузить более {name}'.replace('{name}', maxlen + ' ' + declOfNum(maxlen, [
                'аудиозапись', 'аудиозаписи', 'аудиозаписей'
            ]));
            _a.audioUploadErrorBox(err_msg);
            e.value = '';
            return;
        }
        for (var i = 0; i < len; i++) {
            var file = files[i];
            var ext = file.name.split('.');
            ext = ext[ext.length - 1];
            if (ext !== 'mp3') {
                var err_msg = 'Аудиозапись <b>{name}</b> имеет не верный формат.<br>Выбирите аудиозаписи с форматом MP3!'.replace('{name}', file.name);
                _a.audioUploadErrorBox(err_msg);
                e.value = '';
                return;
            }

            if (file.size > 209715200) {
                var err_msg = 'Аудиозапись <b>{name}</b> превышает максимально допустимый размер.<br>Выбирите аудиозапись размером не более 200 МБ'.replace('{name}', file.name);
                _a.audioUploadErrorBox(err_msg);
                e.value = '';
                return;
            }
            queue.push(file);
        }
        $('#audio_choose_wrap').hide();
        $('.audio_upload_progress').show();
        $('#box_upload #btitle').html('Идет загрузка..');
        $('#box_upload .box_bottom, #box_upload .box_close').remove();
        $('#box_upload').unbind('click');
        _a.upload_queue = queue;
        _a.uploaded_num = 0;
        _a.upload_total = len;
        _a.startUpload(queue[0], pid);
    },
    startUpload: function (file, pid) {
        var _a = audio,
            queue = _a.upload_queue;
        $('#audio_num_download').html('Загруженно {num} из {total}'.replace('{num}', _a.uploaded_num).replace('{total}', _a.upload_total));
        var xhr = new XMLHttpRequest();
        progress = $('.audio_upload_pr_line'),
            progress_str = $('.audio_upload_pr_line .str, #progress_str');
        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                var p = (e.loaded / e.total) * 100;
                progress.css('width', p + '%');
                progress_str.html(parseInt(p) + '%');
            }
        });
        xhr.onreadystatechange = function (e) {
            if (e.target.readyState === 4) {
                if (e.target.status === 200) {
                    _a.uploaded_num++;
                    $('#audio_num_download').html('Загруженно {num} из {total}'.replace('{num}', _a.uploaded_num).replace('{total}', _a.upload_total));
                    _a.upload_queue = _a.upload_queue.slice(1);
                    if (_a.upload_queue.length > 0)
                        _a.startUpload(_a.upload_queue[0], pid);
                    else {
                        $('#box_upload #btitle').html('Информация');
                        $('.audio_upload_cont').html('Загрузка завершена..');
                        setTimeout(function () {
                            if (!pid)
                                Page.Go('/audio/');
                            else Page.Go('/public/audio' + pid);
                        }, 3000);
                    }
                }
            }
        };
        if (pid)
            url = '/public_audio/upload/id=' + pid;
        else
            url = '/audio/upload/';
        xhr.open('POST', url, true);
        var form = new FormData();
        form.append('file', file);
        xhr.send(form);
    },
    add: function (id) {
        if (!id) id = audio_player.fullID;
        $('.titleHtml').remove();
        var aid = id.split('_');
        aid = aid[0];
        id = id.replace('_pad', '');
        $('#audio_' + id + ' .tools, #audio_' + id + '_pad .tools').html('<li class="icon-ok-3" style="padding-top: 2px;font-size: 16px;"></li><div class="clear"></div>');
        $('#pad_add, #pl_add').addClass('icon-ok-3').removeClass('icon-plus-6');
        $.post('/audio/add/', {
            id: aid
        });
    },
    pageFriends: 1,
    friend_tpl: '<li id="user_{fid}" onmousedown="{js}.openFriends({uid}, {fid})">\
	<img src="{ava}"/>\
		<div class="fl_l" style="line-height: 130%;margin-left: 5px;">\
			<div class="audioFriendsBlockName">{name}</div>\
			<div class="cnt_music" dir="auto">{count}</div>\
		</div>\
		<div class="clear"></div>\
	</li>',
    compile_friends: function (d) {
        var count = d.count + ' ' + declOfNum(d.count, ['аудиозапись', 'аудиозаписи', 'аудиозаписей']);
        return str_replace(['{fid}', '{uid}', '{name}', '{ava}', '{js}', '{count}'], [d.fid, d.uid, d.name, d.ava, d.js, count], audio.friend_tpl);
    },
    LoadFriends: function () {
        $.post('/audio/loadFriends/', function (d) {
            // d = JSON.parse(d);
            audio.pageFriends = 1;
            var res = '';
            for (var i = 0; i < d.res.length; i++) res += audio.compile_friends(d.res[i]);
            if (d.count > 6) res += '<div class="audioFrLoadBut" id="audioFrMainLoadBut" onClick="audio.nextFriends()">Показать следующие</div>';
            $('#friendBlockMain').html(res);
            audio.cssFr();
        });
    },
    nextFriends: function () {
        $('#audioFrMainLoadBut').html('Ждите, идёт загрузка...').attr('onClick', '');
        var q = $('#mainFrSearch').val();
        $.post('/audio/loadFriends/', {
            q: q,
            page: audio.pageFriends
        }, function (d) {
            d = JSON.parse(d);
            if (d.reset) audio.pageFriends = 0;
            else audio.pageFriends++;
            var res = '';
            for (var i = 0; i < d.res.length; i++) res += audio.compile_friends(d.res[i]);
            if (d.count > 6) res += '<div class="audioFrLoadBut" id="audioFrMainLoadBut" onClick="audio.nextFriends()">Показать следующие</div>';
            $('#friendBlockMain').html(res);
            audio.cssFr();
        });
    },
    friendSearch: function () {
        removeTimer('mainFrSearch');
        addTimer('mainFrSearch', function () {
            audio.pageFriends = 1;
            var q = $('#mainFrSearch').val();
            $.post('/audio/loadFriends/', {
                q: q
            }, function (d) {
                d = JSON.parse(d);
                var len = d.res.length;
                if (len > 0) {
                    var res = '';
                    for (var i = 0; i < len; i++) res += audio.compile_friends(d.res[i]);
                    $('#friendBlockMain').html(res);
                    audio.cssFr();
                } else $('#friendBlockMain').html('<div style="color: #666; margin-top: 20px; margin-bottom: 20px;text-align:center;">Ничего не найдено</div>');
                if (len === 8) $('#audioFrMainLoadBut').show();
                else $('#audioFrMainLoadBut').hide();
            });
        }, 300);
    },
    cssFr: function () {
        $('#friendBlockMain #user_' + this.a_user_fid).addClass('audioFrActive');
        $('#audio_content_block').css('min-height', (parseInt($('.fixed_audio_right').height()) + 'px'));
    },
    loaded_len: 0,
    searchResult: 0,
    loadAll: function (uid, page) {
        $.post('/audio/' + uid + '/load_all/', {
            page: page
        }, function (d) {
            d = JSON.stringify(d);
            ;
            page++;
            if (d.loaded === 1) {
                audio.audiosRes = d.res;
                audio.loaded_len = d.res.length;
                audio.searchResult = {
                    data: d.res,
                    cnt: audio.loaded_len
                };
            } else {

                // audio.loadAll(uid, page);
            }
            if (audio.loaded_len > 40) {
                $('#load_but').html('<div class="audioLoadBut" style="margin-top:10px" onClick="audio.loadMore()" id="audio_more_but">Показать больше</div>');
            }
        });
    },

    searchClient: function (val, pid) {
        if (val) {
            var cnt = 0,
                a, res = [];
            val = String(val).toLowerCase();
            for (var i = 0; i < audio.loaded_len; i++) {
                a = audio.audiosRes[i];
                if (String(a[3]).toLowerCase().indexOf(val) != -1 || String(a[4]).toLowerCase().indexOf(val) != -1) {
                    res.push(a);
                    cnt++;
                }
            }
            audio.searchResult = {
                data: res,
                cnt: cnt
            };
            audio_player.playLists['audios' + audio.user_id] = {
                pname: 'Сейчас играют аудиозаписи ' + audio.uname + ' | ' + declOfNum(cnt, 'аудиозапись', 'аудиозаписи', 'аудиозаписей'),
                data: res
            };
            $('.audio_menu li').removeClass('active');
            $('#search_tab2').show().addClass('active');
            if (cnt > 0) {
                var len = Math.min(40, cnt),
                    result = '',
                    tpl;
                for (var i = 0; i < len; i++) {
                    tpl = str_replace(['{id}', '{uid}', '{plname}', '{artist}', '{name}', '{stime}', '{time}', '{url}', '{is_text}'], [res[i][1], res[i][0], res[i][7], res[i][3], res[i][4], res[i][6], res[i][5], res[i][2], res[i][9] ? 'text_avilable' : ''], audio.tpl_audio);
                    tpl = tpl.replace(/\[tools\](.*?)\[\/tools\]/gim, kj.uid === audio.user_id ? '$1' : '');
                    tpl = tpl.replace(/\[add\](.*?)\[\/add\]/gim, kj.uid === audio.user_id ? '' : '$1');
                    result += tpl;
                }
                $('#audios_res').html(result);
                if (audio_player.pause) $('#audio_' + audio_player.fullID).addClass('preactiv');
                else audio_player.command('play', {
                    style_only: true
                });
                if (cnt < 15) audio.searchServer(val, pid);
            } else {
                $('#audios_res').html('');
                audio.searchServer(val, pid);
            }
        } else {
            if (audio.tabType === 'publicaudios' + audio.user_id) Page.Go('/public/audio' + audio.user_id);
            else if (kj.uid === audio.user_id) audio.change_tab('my_music');
            else audio.openFriends(audio.user_id, audio.a_user_fid);
            $('#search_preloader').hide();
        }
    },
    searchServer: function (val, pid) {
        removeTimer('search');
        addTimer('search', function () {
            audio.start_load = false;
            $('#search_preloader').show();
            $.post('/audio/search_all/', {
                q: val,
                pid: pid
            }, function (d) {
                audio.moreSaerch = true;
                d = JSON.parse(d);
                audio_player.playLists['search'] = {
                    pname: 'Сейчас играют результаты поиска',
                    data: d.audios
                };
                $('#atitle').html('<div class="audio_page_title" style="margin: 15px 0;">В поиске найдено ' + d.search_cnt + ' ' + declOfNum(d.search_cnt, ['аудиозапись', 'аудиозаписи', 'аудиозаписей']) + '</div>');
                $('#audios_res').append(d.search);
                if (d.search_cnt > 40) $('#load_but').html('<div class="audioLoadBut" style="margin-top:10px" onClick="audio.loadMoreSearch()" id="audio_more_but">Показать больше</div>');
                else $('#load_but').html('');
                $('#search_preloader').hide();
                if (audio_player.pause) $('#audio_' + audio_player.fullID).addClass('preactiv');
                else audio_player.command('play', {
                    style_only: true
                });
            });
        });
    },
    tpl_audio: '<div class="audio" id="audio_{id}_{uid}_{plname}" onclick="playNewAudio(\'{id}_{uid}_{plname}\', event);">\
		<div class="audio_cont">\
			<div class="play_btn icon-play-4"></div>\
			<div class="name"><span id="artist" onClick="Page.Go(\'/?go=search&query=&type=5&q={artist}\')">{artist}</span> – <span id="name" class="{is_text}" onClick="audio_player.get_text(\'{id}_{uid}_{plname}\', this);">{name}</span></div>\
			<div class="fl_r">\
				<div class="time" id="audio_time_{id}_{uid}_{plname}">{stime}</div>\
				<div class="tools">\
					[tools]<li class="icon-pencil-7" onclick="audio.edit_box(\'{id}_{uid}_{plname}\')" id="edit_tt_{id}_{uid}_{plname}" onmouseover="showTooltip(this, {text: \'Редактировать аудиозапись\', shift:[0,7,0]});"></li>\
					<li class="icon-cancel-3" onclick="audio.delete_box(\'{id}_{uid}_{plname}\')" id="del_tt_{id}_{uid}_{plname}" onmouseover="showTooltip(this, {text: \'Удалить аудиозапись\', shift:[0,5,0]});"></li>[/tools]\
					[add]<li class="icon-plus-6" onclick="audio.add(\'{id}_{uid}_{plname}\')" id="add_tt_{id}_{uid}_{plname}" onmouseover="showTooltip(this, {text: \'Добавить аудиозапись\', shift:[0,7,0]});"></li>[/add]\
					<div class="clear"></div>\
				</div>\
			</div>\
			<input type="hidden" value="{url},{time},user_audios" id="audio_url_{id}_{uid}_{plname}"/>\
			<div class="clear"></div>\
		</div>\
		<div id="audio_text_res"></div>\
	</div>'
};

function playNewAudio(id, event) {
    if ($(event.target).parents('.tools, #no_play, .audioPlayer').length !== 0 || $(event.target).filter('.text_avilable, #audio_text_res, #artist, #no_play').length !== 0)
        return;
    cancelEvent(event);
    audio_player.playNew(id);

}
