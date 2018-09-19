@extends('layouts.site')
@section('content')
<content class="container-fluid">
    <div class="row">
        <div class="col-sm-6 col-xs-12 no-shift box-flip">
            <div class="features-wrapper">
                <div class="features-cell">
                    <div class="features-logo">
                        <img src="html/images/logo_client_white.png">
                    </div>
                    <div class="features-text">
                        <?php echo $_text[ 'flip_desc' ]; ?>
                    </div>
                    <div class="features-button">
                        <a href="{{route('page.role', 'passenger')}}" class="transition button-white"><?php echo $_text[ 'read_more' ]; ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xs-12 no-shift box-driver">
            <div class="features-wrapper">
                <div class="features-cell">
                    <div class="features-logo">
                        <img src="html/images/logo_driver_white.png">
                    </div>
                    <div class="features-text">
                        <?php echo $_text[ 'driver_desc' ]; ?>
                    </div>
                    <div class="features-button">
                        <a href="{{route('page.role', 'driver')}}" class="transition button-white"><?php echo $_text[ 'read_more' ]; ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</content>
@endsection