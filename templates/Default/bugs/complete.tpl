<script type="text/javascript" src="/js/bugs.js"></script>
<div class="bugsPage">
	<div class="bugsContent">	
	{load}
	</div>
	<div class="bugsPanel">
		<li onclick="if(event.target.id != 'bugs_add_btn') Page.Go('/bugs/'); return false;"> Все баги <div class="icon-plus-6" id="bugs_add_btn" onclick="bugs.box();"></div></li>
		<li onclick="Page.Go('/bugs/open/'); return false;">Открытые</li>
		<li class="active" onclick="Page.Go('/bugs/complete/');">Исправленные</li>
		<li onclick="Page.Go('/bugs/close/');">Отклоненные</li>
		<li onclick="Page.Go('/bugs/my/');">Мои баги</li>
	</div>
	<div class="clear"></div>
</div>
<div class="clear"></div>