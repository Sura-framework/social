@extends('app.app')
@section('content')
        @if($wall_rec_num_block AND $blacklist_block)
            @include('wall.one_record', array('wall_records' => $wall_records,))
        @else
            <div class="wall_none" >@_e('wall_null')</div>
        @endif
@endsection