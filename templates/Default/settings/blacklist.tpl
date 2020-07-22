<div class="container-lg">
 <div class="row">
  <div class="col-4">
   <nav class="navbar navbar-light">
    <div class="container-fluid">
     <a href="/settings/general/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Настройки</a>
    </div>
   </nav>
   <nav class="navbar navbar-light">
    <div class="container-fluid">
     <a href="/settings/privacy/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Приватность</a>
    </div>
   </nav>
   <nav class="navbar navbar-light">
    <div class="container-fluid">
     <a href="/settings/blacklist/" onClick="Page.Go(this.href); return false;" class="navbar-brand mb-0 h1">Черный список</a>
    </div>
   </nav>
   <hr>
   <nav class="navbar navbar-light">
    <div class="container-fluid">
     <a href="/balance/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Баланс</a>
    </div>
   </nav>
  </div>
  <div class="col-8">
   <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
     <li class="breadcrumb-item"><a href="/settings/">Настройки</a></li>
     <li class="breadcrumb-item active" aria-current="page">Черный список</li>
    </ol>
   </nav>

   [yes-users]<div class="margin_top_10"></div><div class="allbar_title">В Вашем черном списке находится {cnt}</div>[/yes-users]

   {bad_user}
  </div>
 </div>
</div>
