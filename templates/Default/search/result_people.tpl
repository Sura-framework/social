<div id="link_tag_{user-id}_{user-id}"></div>
<div class="card mt-3">
 <div class="card-body">
  <div class="row align-items-center">
   <div class="col-auto">
    <a href="/u{user-id}" onClick="Page.Go(this.href); return false" class="avatar"
       onmouseover="wall.showTag({user-id}, {user-id}, 1)" onmouseout="wall.hideTag({user-id}, {user-id}, 1)">
     <img src="{ava}" alt="{name}" class="avatar-img rounded-circle" style="width: 60px;height: 60px;">
    </a>
   </div>
   <div class="col ml-n2">
    <h4 class="mb-1">
     <a href="/u{user-id}" onClick="Page.Go(this.href); return false"
        onmouseover="wall.showTag({user-id}, {user-id}, 1)" onmouseout="wall.hideTag({user-id}, {user-id}, 1)">{name}</a>
    </h4>
    <p class="card-text small">
     <span class="text-success">{online}</span>
    </p>
    <p>{country}{city}</p>
    <p>{age}</p>
   </div>
   <div class="col-auto">
   </div>
  </div>
 </div>
</div>

