<div class="msg_one [new]msg_new[/new]" id="bmsg_{mid}">
 <div class="msg_pad">
  <div class="msg_ava"><a href="/u{user-id}" onClick="Page.Go(this.href); return false"><img src="{ava}" alt="" /></a></div>
  <div class="msg_left_col">
   <a href="/u{user-id}" onClick="Page.Go(this.href); return false"><span>{name}</span></a>
   <div><span>{online}</span></div>
   <div><small>{date}</small></div>
  </div>
  <a href="/messages/show/{mid}" onClick="Page.Go(this.href); return false"><div class="msg_right_col"><span>{subj}</span><div>{text}&nbsp;</div><div>{attach}</div></div></a>
  <div class="msg_del_link" style="margin-top:-9px;line-height:17px">
  <a href="/messages/show/{mid}" onClick="Page.Go(this.href); return false">Ответить</a>
  <a href="/" onClick="messages.delet({mid}, '{folder}'); return false" id="del_text_{mid}">Удалить</a><img src="/images/loading_mini.gif" alt="" id="del_load_{mid}" class="no_display" />
  </div>
  <div class="clear"></div>
 </div>
</div>