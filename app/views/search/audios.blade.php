@if($search)
    @foreach($search as $row)
<div class="audioPage audioElem search search_item"
id="audio_{$row['id']}_{$row['oid']}_{$plname}"
onclick="playNewAudio('{$row['id']}_{$row['oid']}_{$plname}', event);">
<div class="area">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tbody>
        <tr>
            <td>
                <div class="audioPlayBut new_play_btn"><div class="bl"><div class="figure"></div></div></div>
                <input type="hidden" value="{$row['url']},{$row['duration']},page"
                id="audio_url_{$row['id']}_{$row['oid']}_{$plname}">
                </td>
            <td class="info">
                <div class="audioNames"><b class="author" onclick="Page.Go('/?go=search&query=&type=5&q='+this.innerHTML);" id="artist">{{ $row['artist'] }}</b> – <span
                    class="name" id="name">{{ $row['title'] }}</span> <div class="clear"></div></div>
                <div class="audioElTime" id="audio_time_{$row['id']}_{$row['oid']}_{$plname}">{$stime}</div>
                <div class="vk_audio_dl_btn cursor_pointer fl_l" href="{$row['url']}" style="
position: absolute;
right: 28px;
top: 9px;
display: none;
" onclick="vkDownloadFile(this,'{$row['artist']} - {$row['title']} - kalibri.co.ua');
cancelEvent(event);" onMouseOver="myhtml.title('{$row['id']}', 'Скачать песню', 'ddtrack_', 4)"
                id="ddtrack_{$row['id']}"></div>
<div class="audioSettingsBut"><li class="icon-plus-6"
    onClick="gSearch.addAudio('{$row['id']}_{$row['oid']}_{$plname}')"
    onmouseover="showTooltip(this, {text: 'Добавить в мой список', shift: [6,5,0]});"
    id="no_play"></li><div class="clear"></div></div>
</td>
</tr>
</tbody>
</table>
<div id="player{$row['id']}_{$row['oid']}_{$plname}" class="audioPlayer" border="0"
cellpadding="0">
<table cellspacing="0" cellpadding="0" width="100%">
    <tbody>
    <tr>
        <td style="width: 100%;">
            <div class="progressBar fl_l" style="width: 100%;" onclick="cancelEvent(event);"
            onmousedown="audio_player.progressDown(event, this);" id="no_play"
            onmousemove="audio_player.playerPrMove(event, this)"
            onmouseout="audio_player.playerPrOut()">
            <div class="audioTimesAP" id="main_timeView"><div
                class="audioTAP_strlka">100%</div></div>
            <div class="audioBGProgress"></div>
            <div class="audioLoadProgress"></div>
            <div class="audioPlayProgress" id="playerPlayLine"><div class="audioSlider"></div></div>
            </div>
            </td>
        <td>
            <div class="audioVolumeBar fl_l" onclick="cancelEvent(event);"
            onmousedown="audio_player.volumeDown(event, this);" id="no_play">
            <div class="audioTimesAP"><div class="audioTAP_strlka">100%</div></div>
            <div class="audioBGProgress"></div>
            <div class="audioPlayProgress" id="playerVolumeBar"><div class="audioSlider"></div></div>
            </div>
            </td>
        </tr>
    </tbody>
    </table>
</div>
</div>
</div>
    @endforeach
@endif