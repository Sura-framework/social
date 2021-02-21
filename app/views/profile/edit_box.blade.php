<div class="miniature_box" >
    <div class="miniature_pos" style="width:70%;max-width: 500px;">
        <div class="card">
            <div class="card-header text-center">Редактировать профиль
                <button type="button" class="close float-right" data-dismiss="modal" aria-label="Close" onClick="viiBox.clos('edit_box', 1)">
                    <span aria-hidden="true">&times;</span>
                </button>
                <script type="text/javascript" src="/js/profile_edit.js"></script>
            </div>
            <div class="card-body mt-3">
                <div class="row mb-3">
                    <div class="col">
                        <h5 class="card-title text-info mb-3">Основное</h5>
                    </div>
                    <div class="col">

                        <div class="spinner-border load_mini float-right" role="status" id="saveform_general_load">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="err_yellow" id="info_save" style="display:none;font-weight:normal;"></div>

                <div class="mb-3">
                    <label for="sex" class="form-label">Пол:</label>
                    <select class="form-select" aria-label="Default select example"  id="sex" onChange="sp.check()">
                        {{ $sex }}
                    </select>
                    <div id="sexHelp" class="form-text"> </div>
                </div>
                <div class="mb-3">
                    <div class="[sp-all]no_display[/sp-all] d-none" id="sp_block"><div class="texta">Семейное положение:</div>
                        <div class="padstylej">
                            <div class="[user-m]no_display[/user-m]" id="sp_sel_m">
                                <label for="sp"></label>
                                <select id="sp" class="inpst" onChange="sp.openfriends()">
                                    <option value="0">- Не выбрано -</option>
                                    <option value="1" [instSelect-sp-1]>Не женат</option>
                                    <option value="2" [instSelect-sp-2]>Есть подруга</option>
                                    <option value="3" [instSelect-sp-3]>Помовлен</option>
                                    <option value="4" [instSelect-sp-4]>Женат</option>
                                    <option value="5" [instSelect-sp-5]>Влюблён</option>
                                    <option value="6" [instSelect-sp-6]>Всё сложно</option>
                                    <option value="7" [instSelect-sp-7]>В активном поиске</option>
                                </select>
                            </div>
                            <div class="[user-w]no_display[/user-w]" id="sp_sel_w">
                                <select id="sp_w" class="inpst" onChange="sp.openfriends()">
                                    <option value="0">- Не выбрано -</option>
                                    <option value="1" [instSelect-sp-1]>Не замужем</option>
                                    <option value="2" [instSelect-sp-2]>Есть друг</option>
                                    <option value="3" [instSelect-sp-3]>Помовлена</option>
                                    <option value="4" [instSelect-sp-4]>Замужем</option>
                                    <option value="5" [instSelect-sp-5]>Влюблена</option>
                                    <option value="6" [instSelect-sp-6]>Всё сложно</option>
                                    <option value="7" [instSelect-sp-7]>В активном поиске</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="[sp]no_display[/sp] d-none" id="sp_type">
                        <div class="texta" id="sp_text">{{ $sp_text }}</div>
                        <div class="padstylej fl_l"><div style="margin-top:3px;margin-bottom:10px;padding-left:1px;float:left"><a href="/" id="sp_name" onClick="sp.openfriends(); return false">{{ $sp_name }}</a></div><img src="/images/close_a_wall.png" class="sp_del" onClick="sp.del()" /></div>

                        <input type="hidden" id="sp_val" />
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="exampleInputEmail1" class="form-label">Дата рождения:</label>
                    <div class="row">
                        <div class="col">
                            <select class="form-select" aria-label="Default select example"  id="day">
                                <option>- День -</option>{{ $user_day }}
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-select" aria-label="Default select example"  id="month">
                                <option>- Месяц -</option>{{ $user_month }}
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-select" aria-label="Default select example"  id="year">
                                <option>- Год -</option>{{ $user_year }}
                            </select>
                        </div>
                    </div>
                    <div id="emailHelp" class="form-text">Годовщина рождения.</div>
                </div>

                <div class="mb-3">
                    <div class="row">
                        <div class="col">
                            <label for="country" class="form-label">Страна:</label>
                            <select class="form-select" id="country" aria-label="Default select example" onChange="Profile.LoadCity(this.value); return false;">
                                <option value="0">- Не выбрано -</option>{{ $country }}
                            </select>
                        </div>
                        <div class="col">
                            <label for="select_city" class="form-label">Город:</label>
                            <select class="form-select" id="select_city" aria-label="Default select example">
                                <option value="0">- Не выбрано -</option>{{ $city }}
                            </select>
                        </div>

                        <img src="/images/loading_mini.gif" alt="" class="load_mini" id="load_mini1" />
                    </div>
                    <div class="row">

                    </div>
                </div>

                <button id="saveform_general1" class="btn btn-secondary"  onclick="Profile_edit.General()">Сохранить</button>
            </div>

            <div class="card-body mt-3 d-none">
                <div class="row mb-3">
                    <div class="col">
                        <h5 class="card-title text-info mb-3">Контакты</h5>
                    </div>
                    <div class="col">
                        <div class="spinner-border load_mini float-right" role="status" id="saveform_contact_load">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>

                <div class="err_yellow" id="info_contacts_save" style="display:none;font-weight:normal;"></div>
                <div class="mb-3">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Мобильный телефон:</label>
                        <input type="text" class="form-control" id="phone" value="{{ $phone }}" aria-describedby="emailHelp">
                        <div id="validPhone" class="form-text">Личный или рабочий номер.</div>
                    </div>
                    <div class="mb-3">
                        <label for="site" class="form-label">Личный сайт:</label>
                        <input type="text" class="form-control" id="site" value="{{ $site }}" aria-describedby="emailHelp">
                        <div id="validSite" class="form-text">Веб-ресурс, который может рассказать о себе</div>
                    </div>
                </div>

                <button name="save" id="saveform_contact1" class="btn btn-secondary" onclick="Profile_edit.Contacts()">Сохранить</button>

            </div>
            <div class="card-body mt-3">
                <div class="row mb-3">
                    <div class="col">
                        <h5 class="card-title text-info mb-3">Биография</h5>
                    </div>
                    <div class="col">
                        <div class="spinner-border load_mini float-right" role="status" id="saveform_interests_load">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>

                <div class="err_yellow" id="info_interests_save" style="display:none;font-weight:normal;"></div>
                <div class="mb-3">
                    <label for="myinfo" class="form-label">О себе:</label>
                    <textarea class="form-control" id="myinfo" rows="3">{{ $myinfo }}</textarea>
                </div>
                <button name="save" id="saveform_interests1" class="btn btn-secondary" onclick="Profile_edit.Interests()">Сохранить</button>
            </div>
            <div class="card-footer">
                <div class="fl_r"><button class="btn btn-secondary" onClick="viiBox.clos('edit_box', 1)">Закрыть</button></div>
            </div>
        </div>

    </div>

</div>

<style>.miniature_pos{padding: 0;}</style>