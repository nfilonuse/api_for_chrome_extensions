<?php
/**
 * Project Name: TaxiLand
 * Description: HTML skins files
 * Author: Amcon Soft
 * Author URI: https://www.amconsoft.com/
 * Version: 0.0.1
 *
 * Current file: HTML header section
 */

if ( isset( $page ) ) {
    switch ( $page ) {
        case 'driver' : $body_id = 'page-driver'; $title = 'driver'; $favicon_dir = 'driver/';
            break;
        case 'passenger' : $body_id = 'page-passenger'; $title = 'client'; $favicon_dir = 'client/';
            break;
        default : $body_id = 'page-selectype'; $title = ''; $favicon_dir = '';
            break;
    }
} else {
    $body_id = 'page-selectype';
    $title = '';
    $favicon_dir = '';
}

if ( isset( $_COOKIE[ 'lang' ] ) ) {
    switch ( $_COOKIE[ 'lang' ] ) {
        case 'eng' : $lang = 'eng';
            break;
        case 'rus' : $lang = 'rus';
            break;
        default : $lang = 'eng';
            break;
    }
} else {
    $lang = 'eng';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $_text[ 'title' ]; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="{{url('html/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{url('html/css/styles.css')}}">
    <link rel="stylesheet" href="{{url('html/css/response.css')}}">

    <!-- Favicon section -->
    <link rel="shortcut icon" href="{{url('html/images/'.$favicon_dir.'favicon.ico')}}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{url('html/images/'.$favicon_dir.'apple-touch-icon.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{url('html/images/'.$favicon_dir.'favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{url("html/images/".$favicon_dir."favicon-16x16.png")}}">
    <link rel="manifest" href="{{url("html/images/".$favicon_dir.'manifest.json')}}">
    <link rel="mask-icon" href="{{url("html/images/safari-pinned-tab.svg")}}" color="#5bbad5">
    <meta name="theme-color" content="#ffffff">
    <!-- Favicon section -->

    <script src="{{url("html/js/jquery-3.2.1.min.js")}}"></script>
    <script src="{{url("html/js/jquery.cookie.js")}}"></script>
    <script src="{{url("html/js/bootstrap.min.js")}}"></script>
    <script src="{{url("html/js/script.js")}}"></script>
</head>

<body id="<?php echo $body_id; ?>">

<header class="container-fluid">
    <div class="row">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <nav class="navbar navbar-default">
                        <div class="container-fluid">

                            <!-- Title -->
                            <div class="navbar-header">

                                <!-- Language for mobile -->
                                <ul class="mobile-lang">
                                    <li class="lang-select" class="dropdown">
                                        <a href="javascript://" class="dropdown-toggle transition" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <?php echo $_text[ 'language' ]; ?>
                                            <img src="{{url("html/images/flag-".$lang.".png")}}">
                                            <span class="caret"><i class="transition"></i></span>
                                        </a>
                                        <ul class="dropdown-menu lang-select transition">
                                            <li class="lang-singl">
                                                <label class="transition">
                                                    <input type="radio" name="lang" value="eng">
                                                    <?php echo 'Eng'; ?>
                                                    <img src="{{url("html/images/flag-eng.png")}}">
                                                </label>
                                            </li>
                                            <li class="lang-singl">
                                                <label class="transition">
                                                    <input type="radio" name="lang" value="rus">
                                                    <?php echo 'Рус'; ?>
                                                    <img src="{{url("html/images/flag-rus.png")}}">
                                                </label>
                                            </li>
                                            <li class="lang-singl">
                                                <label class="transition">
                                                    <input type="radio" name="lang" value="esp">
                                                    <?php echo 'ESP'; ?>
                                                    <img src="{{url("html/images/flag-esp.png")}}">
                                                </label>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>

                                <!-- Mobile button -->
                                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-main" aria-expanded="false">
                                    <span class=sr-only><?php echo $_text[ 'toggle_navigation' ]; ?></span>
                                    <span class="icon-bar transition"></span>
                                    <span class="icon-bar transition"></span>
                                    <span class="icon-bar transition"></span>
                                </button>
                                <!-- Logo -->
                                <a class="navbar-brand" href="/">
                                    <?php if ( $title ) { ?>
                                    <img src="{{url("html/images/logo_".$title."_color.svg")}}">
                                    <?php } ?>
                                </a>

                            </div>

                            <!-- Menu -->
                            <div class="collapse navbar-collapse" id="navbar-main">

                                <ul class="nav navbar-nav">
                                    <li class="active">
                                        <a href="{{route('page.passenger')}}" class="transition"><?php echo $_text[ 'passenger' ]; ?><span class="sr-only">(<?php echo $_text[ 'current' ]; ?>)</span></a>
                                    </li>
                                    <li>
                                        <a href="{{route('page.driver')}}" class="transition"><?php echo $_text[ 'driver' ]; ?></a>
                                    </li>
                                    <li>
                                        <a href="#" class="transition"><?php echo $_text[ 'contact' ]; ?></a>
                                    </li>
                                    <!-- Language for desktop -->
                                    <li class="lang-select" class="dropdown">
                                        <a href="javascript://" class="dropdown-toggle transition" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <?php echo $_text[ 'language' ]; ?>
                                            <img src="{{url("html/images/flag-".$lang.".png")}}">
                                            <span class="caret"><i class="transition"></i></span>
                                        </a>
                                        <ul class="dropdown-menu lang-select transition">
                                            <li class="lang-singl">
                                                <label class="transition">
                                                    <input type="radio" name="lang" value="eng">
                                                    <?php echo 'Eng'; ?>
                                                    <img src="{{url("html/images/flag-eng.png")}}">
                                                </label>
                                            </li>
                                            <li class="lang-singl">
                                                <label class="transition">
                                                    <input type="radio" name="lang" value="rus">
                                                    <?php echo 'Рус'; ?>
                                                    <img src="{{url("html/images/flag-rus.png")}}">
                                                </label>
                                            </li>
                                            <li class="lang-singl">
                                                <label class="transition">
                                                    <input type="radio" name="lang" value="esp">
                                                    <?php echo 'Esp'; ?>
                                                    <img src="{{url("html/images/flag-esp.png")}}">
                                                </label>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>

                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>