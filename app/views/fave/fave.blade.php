@extends('app.app')
@section('content')
    <div class="container">
        <script type="text/javascript" src="/js/fave.filter.js"></script>
        <label for="filter"></label>
        <input type="text" value="Начните вводить имя" class="fave_input"
               onblur="if(this.value=='') this.value='Начните вводить имя';this.style.color = '#c1cad0';"
               onfocus="if(this.value=='Начните вводить имя') this.value='';this.style.color = '#000'" id="filter" />
        <div class="clear"></div>
        <table class="food_planner" id="fave_users">
            @foreach($fave as $row)
                <tr class="onefaveu" id="user_{{ $user_id }}">
                    <td>
                        <div class="fave_del_ic" onMouseOver="myhtml.title('{{ $user_id }}', 'Удалить из закладок', 'fave_user_')"
                             onClick="fave.del_box('{{ $user_id }}'); return false" id="fave_user_{{ $user_id }}">
                        </div>
                        <a href="/u{{ $user_id }}" onClick="Page.Go(this.href); return false">
                            <img src="{{ $ava }}" alt="" />
                            <div class="fave_tpad"><b>{{ $name }}</b></div></a>
                        <span class="online">{{ $online }}</span>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

@endsection