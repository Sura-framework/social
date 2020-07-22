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
     <a href="/balance/invite/" onClick="Page.Go(this.href); return false;" class="navbar-brand">Пригласить друга</a>
    </div>
   </nav>
   <nav class="navbar navbar-light">
    <div class="container-fluid">
     <a href="/balance/invited/" onClick="Page.Go(this.href); return false;" class="navbar-brand mb-0 h1">Приглашённые друзья</a>
    </div>
   </nav>
  </div>
  <div class="col-8 text-center">
   <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
     <li class="breadcrumb-item"><a href="/balance/">Баланс</a></li>
     <li class="breadcrumb-item active" aria-current="page">Приглашённые друзья</li>
    </ol>
   </nav>
   <div class="mt-3"></div><div class="allbar_title" style="border-bottom:0px;margin-bottom:0px">Последние 100 человек которых вы пригласили</div>
   {invited}
  </div>
 </div>


</div>
