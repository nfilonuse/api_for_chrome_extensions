@extends('layouts.app')
@section('title', 'Maintenance')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">{!! HelperOption::get('site_title') !!}</div>

                    <div class="panel-body">
                        {!! HelperOption::get('site_disabled_text') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
