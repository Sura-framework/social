@extends('app.app')
@section('content')

    <h1>Панель управления</h1>



    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">

                        <!-- Avatar -->
                        <a href="project-overview.html" class="avatar avatar-4by3">
                            <img src="https://demosite.josh.qd2.ru/images/inc/settings.png" alt="..." class="avatar-img rounded">
                        </a>

                    </div>
                    <div class="col ml-n2">

                        <!-- Title -->
                        <h4 class="mb-1">
                            <a href="/admin/">Homepage Redesign</a>
                        </h4>

                        <!-- Text -->
                        <p class="small text-muted mb-0">
                            Настройка общих параметров скрипта, а также настройка системы безопасности скрипта
                            <time datetime="2018-06-21">Updated 2hr ago</time>
                        </p>

                    </div>
                    <div class="col-auto">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" style="">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </div>
                </div> <!-- / .row -->
            </div> <!-- / .card-body -->
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">

                        <!-- Avatar -->
                        <a href="project-overview.html" class="avatar avatar-4by3">
                            <img src="https://demosite.josh.qd2.ru/images/inc/settings.png" alt="..." class="avatar-img rounded">
                        </a>

                    </div>
                    <div class="col ml-n2">

                        <!-- Title -->
                        <h4 class="mb-1">
                            <a href="/admin/">Homepage Redesign</a>
                        </h4>

                        <!-- Text -->
                        <p class="small text-muted mb-0">
                            Настройка общих параметров скрипта, а также настройка системы безопасности скрипта
                            <time datetime="2018-06-21">Updated 2hr ago</time>
                        </p>

                    </div>
                    <div class="col-auto">

                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a href="#" class="dropdown-ellipses dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" style="">
                                <a href="#!" class="dropdown-item">
                                    Action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Another action
                                </a>
                                <a href="#!" class="dropdown-item">
                                    Something else here
                                </a>
                            </div>
                        </div>

                    </div>
                </div> <!-- / .row -->
            </div> <!-- / .card-body -->
        </div>

        @foreach($modules as $row)
            <a href="/admin/{{ $row['link'] }}/" onclick="Page.Go(this.href); return false" class="col-6 mt-2">
                <div class="row">
                    <div class="col-3">
                        <img src="/images/inc/{{ $row['img'] }}" alt="{title}" title="{{ $row['title'] }}" />
                    </div>
                    <div class="col-9">
                        <h2>{{ $row['title'] }}</h2>
                        <div>{{ $row['description'] }}</div>
                    </div>
                </div>
            </a>
        @endforeach


    </div>

<style>
    .content {
        padding: 2.5rem 2.5rem 1.5rem;
        flex: 1;
        direction: ltr;
    }
</style>

<div class="content">
    <div class="container-fluid p-0">
        <div class="row">
            @foreach($modules as $row)
                {{--            /admin/{{ $row['link'] }}/--}}
                <div class="col-12 col-sm-6 col-xxl d-flex" onclick="Page.Go('/admin/{{ $row['link'] }}/'); return false;">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="media">
                                <div class="media-body">
                                    <div class="">
                                        <div class="illustration-text p-3 m-1">
                                            <h4 class="illustration-text">{{ $row['title'] }}</h4>
                                            <p class="mb-0">{{ $row['description'] }}</p>
                                        </div>
                                    </div>
                                    <div class=" align-self-end text-right">
                                        <img src="/images/inc/{{ $row['img'] }}" alt="{{ $row['title'] }}" class="img-fluid illustration-img">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-12 col-sm-6 col-xxl d-flex">
                <div class="card illustration flex-fill">
                    <div class="card-body p-0 d-flex flex-fill">
                        <div class="row no-gutters w-100">
                            <div class="col-6">
                                <div class="illustration-text p-3 m-1">
                                    <h4 class="illustration-text">Welcome Back, Chris!</h4>
                                    <p class="mb-0">AppStack Dashboard</p>
                                </div>
                            </div>
                            <div class="col-6 align-self-end text-right">
                                <img src="img/illustrations/customer-support.png" alt="Customer Support" class="img-fluid illustration-img">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xxl d-flex">
                <div class="card flex-fill">
                    <div class="card-body py-4">
                        <div class="media">
                            <div class="media-body">
                                <h3 class="mb-2">$ 24.300</h3>
                                <p class="mb-2">Total Earnings</p>
                                <div class="mb-0">
                                    <span class="badge badge-soft-success mr-2"> <i class="mdi mdi-arrow-bottom-right"></i> +5.35% </span>
                                    <span class="text-muted">Since last week</span>
                                </div>
                            </div>
                            <div class="d-inline-block ml-3">
                                <div class="stat">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign align-middle text-success"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xxl d-flex">
                <div class="card flex-fill">
                    <div class="card-body py-4">
                        <div class="media">
                            <div class="media-body">
                                <h3 class="mb-2">43</h3>
                                <p class="mb-2">Pending Orders</p>
                                <div class="mb-0">
                                    <span class="badge badge-soft-danger mr-2"> <i class="mdi mdi-arrow-bottom-right"></i> -4.25% </span>
                                    <span class="text-muted">Since last week</span>
                                </div>
                            </div>
                            <div class="d-inline-block ml-3">
                                <div class="stat">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-bag align-middle text-danger"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xxl d-flex">
                <div class="card flex-fill">
                    <div class="card-body py-4">
                        <div class="media">
                            <div class="media-body">
                                <h3 class="mb-2">$ 18.700</h3>
                                <p class="mb-2">Total Revenue</p>
                                <div class="mb-0">
                                    <span class="badge badge-soft-success mr-2"> <i class="mdi mdi-arrow-bottom-right"></i> +8.65% </span>
                                    <span class="text-muted">Since last week</span>
                                </div>
                            </div>
                            <div class="d-inline-block ml-3">
                                <div class="stat">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign align-middle text-info"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection