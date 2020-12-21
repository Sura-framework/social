@extends('app.app')
@section('content')
<style type="text/css" media="all">
    .texta_profileedit {width: 195px;padding-top: 5px;}
</style>
<script type="text/javascript">
    $(document).ready(function(){
        myhtml.checked(['{settings-audio}','{settings-contact}','{settings-comments}','{settings-videos}']);
        $('#descr').autoResize({extraSpace:0,limit:608});
        if($('#public_category').val() == 0) $('#pcategory').hide();
    });
</script>
<div class="container-lg">
    <div class="row">
        <div class="col-4">
            {{ $menu }}
        </div>
        <div class="col-8">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/public{{ $id }}">{{ $id }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Настройки</li>
                    </ol>
                </nav>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection