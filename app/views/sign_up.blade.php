@extends('app.app')
@section('content')
<div class="d-flex justify-content-center">
    <div class="col-5 mt-2">
        <div class="card">
            <div class="card-body">
                <h1 class="display-4 text-center mb-3">Spiso</h1>
                <p class="text-muted text-center mb-5">Чтобы продолжить, создайте аккаунт или войдите.</p>
                <h1 onClick="reg.step1(); return false" id="reg_lnk">Моментальная регистрация</h1>
                <div class="no_display" id="step2">
                    <div class="form-group mb-3">
                        Пожалуйста, укажите Вашу <b>дату рождения</b>, <b>пол</b> и <b>страну проживания</b>.
                    </div>
                    <div class="err_red no_display frmero mb-0" id="err"></div>
                    <div class="form-group mb-3">
                        <label for="sex">Выберите пол </label>
                        <select id="sex" class="form-control">
                            <option value="0">Выбрать пол</option>
                            <option value="1">мужской</option>
                            <option value="2">женский</option>
                        </select>
                    </div>
                    <div class="form-inline">
                        <div class="form-group mb-3">
                            <label for="day"></label>
                            <select id="day" class="form-control">
                                <option value="0">День рождения</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="month"></label>
                            <select id="month" class="form-control">
                                <option value="0">Месяц рождения</option><option value="1">Января</option><option value="2">Февраля</option><option value="3">Марта</option><option value="4">Апреля</option><option value="5">Мая</option><option value="6">Июня</option><option value="7">Июля</option><option value="8">Августа</option><option value="9">Сентября</option><option value="10">Октября</option><option value="11">Ноября</option><option value="12">Декабря</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="year"></label>
                            <select id="year" class="form-control">
                                <option value="0">Год рождения</option><option value="1930">1930</option><option value="1931">1931</option><option value="1932">1932</option><option value="1933">1933</option><option value="1934">1934</option><option value="1935">1935</option><option value="1936">1936</option><option value="1937">1937</option><option value="1938">1938</option><option value="1939">1939</option><option value="1940">1940</option><option value="1941">1941</option><option value="1942">1942</option><option value="1943">1943</option><option value="1944">1944</option><option value="1945">1945</option><option value="1946">1946</option><option value="1947">1947</option><option value="1948">1948</option><option value="1949">1949</option><option value="1950">1950</option><option value="1951">1951</option><option value="1952">1952</option><option value="1953">1953</option><option value="1954">1954</option><option value="1955">1955</option><option value="1956">1956</option><option value="1957">1957</option><option value="1958">1958</option><option value="1959">1959</option><option value="1960">1960</option><option value="1961">1961</option><option value="1962">1962</option><option value="1963">1963</option><option value="1964">1964</option><option value="1965">1965</option><option value="1966">1966</option><option value="1967">1967</option><option value="1968">1968</option><option value="1969">1969</option><option value="1970">1970</option><option value="1971">1971</option><option value="1972">1972</option><option value="1973">1973</option><option value="1974">1974</option><option value="1975">1975</option><option value="1976">1976</option><option value="1977">1977</option><option value="1978">1978</option><option value="1979">1979</option><option value="1980">1980</option><option value="1981">1981</option><option value="1982">1982</option><option value="1983">1983</option><option value="1984">1984</option><option value="1985">1985</option><option value="1986">1986</option><option value="1987">1987</option><option value="1988">1988</option><option value="1989">1989</option><option value="1990">1990</option><option value="1991">1991</option><option value="1992">1992</option><option value="1993">1993</option><option value="1994">1994</option><option value="1995">1995</option><option value="1996">1996</option><option value="1997">1997</option><option value="1998">1998</option><option value="1999">1999</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="country"></label>
                        <select id="country" class="form-control" onChange="Profile.LoadCity(this.value); return false;"><option value="0">Выбрать страну</option>{{ $country }}
                        </select>
                        <div class="spinner-border load_mini" role="status"  id="load_mini">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <span id="city" style="display:none;">
                        <label for="select_city"></label>
                            <select name="city" id="select_city" class="form-control"><option>Выбрать город</option></select>
                        </span>
                    </div>
                    <div class="form-group mb-3">
                        <button type="button" class="btn btn-primary" onClick="reg.step2(); return false">Следующий шаг</button>
                    </div>
                </div>
                <div id="step1">
                    <div class="err_red no_display frmero mb-0" id="err"></div>
                    <div class="form-group mb-3">
                        <label for="name">Ваше имя</label>
                        <input type="text" maxlength="30" id="name" placeholder="Ваше имя" class="mt-3 mb-3 form-control" aria-describedby="emailHelp"  oninput = "input_name()">
                        <div class="invalid-feedback mb-3" id="err_name"></div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="lastname">Ваша фамилия</label>
                        <input type="text" maxlength="30" id="lastname" placeholder="Ваша фамилия" class="mt-3 mb-3 form-control" aria-describedby="emailHelp"  oninput = "input_lastname()">
                        <div class="invalid-feedback mb-3" id="err_lastname"></div>
                    </div>
                    <div class="form-group mb-3">
                        <button type="button" class="btn btn-primary" onClick="reg.step1(); return false">Зарегистрироваться</button>
                    </div>
                </div>
                <div class="no_display" id="step3">
                    <div class="form-group mb-3">
                        Укажите <b>Ваш электронный адрес</b> и <b>пароль</b>, который Вы будете использовать при входе.
                    </div>
                    <div class="err_red no_display frmero mb-0" id="err2"></div>
                    <div class="form-group mb-3">
                        <label for="email">Электронный адрес</label>
                        <input type="text" maxlength="30" id="email" placeholder="Электронный адрес" class="mt-3 mb-3 form-control" aria-describedby="emailHelp" oninput="input_email()">
                        <div class="invalid-feedback mb-3" id="err_email"></div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="new_pass">Пароль</label>
                        <input type="text" maxlength="30" id="new_pass" placeholder="Пароль" class="mt-3 mb-3 form-control" aria-describedby="emailHelp" oninput="input_pass()">
                        <div class="invalid-feedback mb-3" id="err_new_pass"></div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="new_pass2">Еще раз пароль</label>
                        <input type="text" maxlength="30" id="new_pass2" placeholder="Еще раз пароль" class="mt-3 mb-3 form-control" aria-describedby="emailHelp" oninput="input_pass2()">
                        <div class="invalid-feedback mb-3" id="err_new_pass2"></div>
                    </div>

                    <div class="form-group mb-3">
                        @csrf('_mytoken')
                        <button type="button" class="btn btn-primary" onClick="reg.finish(); return false">Отправить</button>
                    </div>
                </div>
                <h1 onClick="reg.step1(); return false" id="reg_lnk">В чем поможет Spiso?</h1>
                <ul class="list-group">
                    <li class="list-group-item"><span>Найти людей, с которыми Вы когда-либо учились, работали или отдыхали.</span></li>
                    <li class="list-group-item"><span>Узнать больше о людях, которые Вас окружают, и найти новых друзей.</span></li>
                    <li class="list-group-item"><span>Всегда оставаться в контакте с теми, кто Вам дорог.</span></li>
                </ul>
            </div>
        </div>
        <div class="mt-3 card shadows ">
            <div class="card-body">
                У вас есть аккаунт? <a href="/" onclick="Page.Go(this.href); return false;">Войти</a>
            </div>
        </div>
    </div>
</div>
@endsection