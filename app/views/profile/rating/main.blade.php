<script type="text/javascript">
    $(document).ready(function(){
        $('#rate_num').focus().val('1');
        rating.update();
    });
</script>
<div class="miniature_box">
    <div class="miniature_pos" style="width:400px">
        <h2 class="miniature_title fl_l apps_box_text">@_e('rating_increase')</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onmouseover="myhtml.title('1', 'Закрыть', 'box_upload_')" onclick="viiBox.clos('rate', 1)" id="box_upload_1">
            <span aria-hidden="true">&times;</span>
        </button>
        <div class="clear"></div>
        <div class="alert alert-info" role="alert">@_e('rating_info')</div>
        <div class="clear"></div>
        <div class="rating_text"></div>
        <div class="rating_iny">
            + <label for="rate_num"></label><input type="text" class="inpst" maxlength="3" id="rate_num" onKeyUp="rating.update()" />
            <div class="rating_text_balance">@_e('you') <span id="rt">@_e('stays')</span> <b id="num">{{ $num }}</b> @_e('currencys')</div>
            <input type="hidden" id="balance" value="{{ $balance }}" />
        </div>
        <div class="button_div fl_l" style="margin-left:150px"><button onClick="rating.save('{{ $user_id }}')" id="saverate">@_e('rating_increase2')</button></div>
        <div class="clear"></div>
    </div>
    <div class="clear" style="height:20px"></div>
</div>