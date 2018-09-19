@extends('admin.layouts.app')
@section('title', 'Admin Home')
@section('content')

    <section class="content-header">
        <h1>Dashboard
            <small>Control panel</small>
        </h1>
        <ol class="breadcrumb">
            <li class="active"><a href="{{route('admin.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12 col-md-offset-0">
                <div class="">
                    <div class="panel-heading">Dashboard</div>
                    <div class="row">
                        <h2>Аctivity graph</h2>
                        <div class="col-xs-12 col-lg-6">
                            <form action="" method="GET">
                                <div class="col-xs-6">
                                <select id="driver_type" name="driver_type" class="form-control input-sm">
                                    <option @if(empty($t) || $t=='all') selected @endif value="all">All</option>
                                    <option @if($t=='2') selected @endif value="2">Drivers</option>
                                    <option @if($t=='1') selected @endif value="1">Clients</option>
                                </select>
                                </div>
                                <div class="col-xs-6">
                                    <input type="submit" value="ok" class="btn btn-success btn-sm">
                                </div>
                                <div class="clearfix"></div>
                            </form>
                        </div>
                        <div class="col-xs-12 col-lg-6">
                            <div class="admin_online_block text-right">
                                <div class="d_online">Drivers online : <span>{{ $active[1] }}</span></div>
                                <div class="c_online">Clients online : <span>{{ $active[0] }}</span></div>
                                <div class="s_online">Subscriptions : <span>{{ $active[2] }}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {!! Charts::assets() !!}
                        <div class="col-xs-12 col-lg-6">
                            {!! $chart_1->render() !!}
                        </div>
                        <div class="col-xs-12 col-lg-6">
                            {!! $chart_2->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
