@extends('app.app')
@section('content')
    <script type="text/javascript">
        $(document).click(function(event){
            settings.event(event);
        });
    </script>
    <div class="container-lg">
        <div class="row">
            <div class="col-4">
                {{ $menu }}
            </div>
            <div class="col-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/settings/">@_e('settings')</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@_e('settings_privacy')</li>
                    </ol>
                </nav>
                <div class="clear" style="margin-top:25px"></div>
                <div class="err_yellow no_display" id="ok_update" style="font-weight:normal;">@_e('privacy_ok')</div>
                <div class="texta color_000" style="width:300px">@_e('privacy1')</div>
                <div class="sett_privacy" onClick="settings.privacyOpen('msg')" id="privacy_lnk_msg">{{ $val_msg_text }}</div>
                <div class="sett_openmenu no_display" id="privacyMenu_msg">
                    <div id="selected_p_privacy_lnk_msg" class="sett_selected" onClick="settings.privacyClose('msg')">{{ $val_msg_text }}</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_msg', 'Все пользователи', '1', 'privacy_lnk_msg')">@_e('all_users')</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_msg', 'Только друзья', '2', 'privacy_lnk_msg')">@_e('all_friends')</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_msg', 'Никто', '3', 'privacy_lnk_msg')">@_e('no_users')</div>
                </div>
                <input type="hidden" id="val_msg" value="{{ $val_msg }}" />
                <div class="mgclr"></div>
                <div class="texta color_000" style="width:300px">@_e('privacy2')</div>
                <div class="sett_privacy" onClick="settings.privacyOpen('wall1')" id="privacy_lnk_wall1">{{ $val_wall1_text }}</div>
                <div class="sett_openmenu no_display" id="privacyMenu_wall1" style="margin-top:-1px">
                    <div id="selected_p_privacy_lnk_wall1" class="sett_selected" onClick="settings.privacyClose('wall1')">{{ $val_wall1_text }}</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_wall1', 'Все пользователи', '1', 'privacy_lnk_wall1')">@_e('all_users')</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_wall1', 'Только друзья', '2', 'privacy_lnk_wall1')">@_e('all_friends')</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_wall1', 'Только я', '3', 'privacy_lnk_wall1')">@_e('all_you')</div>
                </div>
                <input type="hidden" id="val_wall1" value="{{ $val_wall1 }}" />
                <div class="mgclr"></div>
                <div class="texta color_000" style="width:300px">@_e('privacy3')</div>
                <div class="sett_privacy" onClick="settings.privacyOpen('wall2')" id="privacy_lnk_wall2">{{ $val_wall2_text }}</div>
                <div class="sett_openmenu no_display" id="privacyMenu_wall2" style="margin-top:-1px">
                    <div id="selected_p_privacy_lnk_wall2" class="sett_selected" onClick="settings.privacyClose('wall2')">{{ $val_wall2_text }}</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_wall2', 'Все пользователи', '1', 'privacy_lnk_wall2')">@_e('all_users')</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_wall2', 'Только друзья', '2', 'privacy_lnk_wall2')">@_e('all_friends')</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_wall2', 'Только я', '3', 'privacy_lnk_wall2')">@_e('all_you')</div>
                </div>
                <input type="hidden" id="val_wall2" value="{{ $val_wall2 }}" />
                <div class="mgclr"></div>
                <div class="texta color_000" style="width:300px">@_e('privacy4')</div>
                <div class="sett_privacy" onClick="settings.privacyOpen('wall3')" id="privacy_lnk_wall3">{{ $val_wall3_text }}</div>
                <div class="sett_openmenu no_display" id="privacyMenu_wall3" style="margin-top:-1px">
                    <div id="selected_p_privacy_lnk_wall3" class="sett_selected" onClick="settings.privacyClose('wall3')">{{ $val_wall3_text }}</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_wall3', 'Все пользователи', '1', 'privacy_lnk_wall3')">@_e('all_users')</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_wall3', 'Только друзья', '2', 'privacy_lnk_wall3')">@_e('all_friends')</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_wall3', 'Только я', '3', 'privacy_lnk_wall3')">@_e('all_you')</div>
                </div>
                <input type="hidden" id="val_wall3" value="{{ $val_wall3 }}" />
                <div class="mgclr"></div>
                <div class="texta color_000" style="width:300px">@_e('privacy5')</div>
                <div class="sett_privacy" onClick="settings.privacyOpen('info')" id="privacy_lnk_info">{{ $val_info_text }}</div>
                <div class="sett_openmenu no_display" id="privacyMenu_info" style="margin-top:-1px">
                    <div id="selected_p_privacy_lnk_info" class="sett_selected" onClick="settings.privacyClose('info')">{val_info_text}</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_info', 'Все пользователи', '1', 'privacy_lnk_info')">@_e('all_users')</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_info', 'Только друзья', '2', 'privacy_lnk_info')">@_e('all_friends')</div>
                    <div class="sett_hover" onClick="settings.setPrivacy('val_info', 'Только я', '3', 'privacy_lnk_info')">@_e('all_you')</div>
                </div>
                <input type="hidden" id="val_info" value="{{ $val_info }}" />
                <div class="mgclr"></div>
                <div class="texta color_000" style="width:300px">&nbsp;</div>
                <button onClick="settings.savePrivacy(); return false" id="savePrivacy" class="btn btn-success">@_e('save')</button>
                <div class="mgclr"></div>
            </div>
        </div>
    </div>
@endsection