<div class="cover_edit_title">Всего {{ $photo_num }}</div>
<div class="clear"></div>
<div style="padding:10px;padding-bottom:15px;">
@foreach($sql_ as $row)
{{ $row['content'] }}

@endforeach
    <div class="clear">

    </div>
</div>