<div class="container-lg">
    <div class="row">
        <div class="col-4">
            <nav class="navbar navbar-light">
                <div class="container-fluid">
                    <a href="/settings/general/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Настройки</a>
                </div>
            </nav>
            <hr>
            <nav class="navbar navbar-light">
                <div class="container-fluid">
                    <a href="/balance/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Баланс</a>
                </div>
            </nav>
            <nav class="navbar navbar-light">
                <div class="container-fluid">
                    <a href="/balance/invite/" onClick="Page.Go(this.href); return false;" class="navbar-brand mb-0 h1">Пригласить друга</a>
                </div>
            </nav>
            <nav class="navbar navbar-light">
                <div class="container-fluid">
                    <a href="/balance/invited/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Приглашённые друзья</a>
                </div>
            </nav>
        </div>
        <div class="col-8">
            <div class="margin_top_10"></div><div class="allbar_title">Инструкция по приглашению друга</div>
            <div class="ubm_descr">
                <div class="text-center">
                    Для приглашения друга отправьте ему ссылку на регистрацию, которая указана ниже.<br /><br />
                    <span class="color777">Ваша ссылка для приглашения:</span>&nbsp;&nbsp;
                    <input type="text"
                           class="videos_input"
                           style="width:200px"
                           onClick="this.select()"
                           value="http://site.com/reg{uid}"
                    />
                </div>
            </div>
        </div>
    </div>
</div>


