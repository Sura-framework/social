<script type="text/javascript" src="/js/bugs.js"></script>
<link href="/style/bugs.css" type="text/css" rel="stylesheet">
<style>
.nav a:hover {text-decoration: none;text-shadow: 1px 1px 4px #000000;border-bottom: none;}
</style>
<div class="container">


<div class="row">
	<div class="col-6">
		<h2  onclick="Page.Go('/bugs/'); return false;">Все баги</h2>
	</div>
	<div class="col-6">
		<button type="button" class="btn btn-primary icon-plus-6" onclick="bugs.box();"  id="bugs_add_btn2" onMouseOver="myhtml.title('_btn2', 'Сообщить о баге', 'bugs_add');">Сообщить о баге</button>
	</div>
</div>
<div class="row">
	<div class="col-3">

		<ul class="nav flex-column align-items-start">
			<li class="nav-item">
				<a class="nav-link active" href="#" onclick="Page.Go('/bugs/'); return false;">Все баги</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#" onclick="Page.Go('/bugs/open/'); return false;">Открытые</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#" onclick="Page.Go('/bugs/complete/'); return false;">Исправленные</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#" onclick="Page.Go('/bugs/close/'); return false;">Отклоненные</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="#" onclick="Page.Go('/bugs/my/'); return false;">Мои баги</a>
			</li>
		</ul>
	</div>
	<div class="col-9">
		{load}
	</div>
</div>
</div>