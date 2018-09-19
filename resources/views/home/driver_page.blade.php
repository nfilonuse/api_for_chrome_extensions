@extends('layouts.site')
@section('content')
    <content>
        <div class="container-fluid slider-box">
            <div class="row">
                <div class="col-xs-12">
                    <div class="container">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="slider-box__left">
                                    <img src="html/images/Iphone_driver.png" alt="">
                                </div>
                                <div class="slider-box__right">
                                    <div class="slider-box__wrapp">

                                        <div class="slider-box__title">
                                            <?php echo $_text[ 'banner_driver_title' ]; ?>
                                        </div>
                                        <div class="slider-box__desc">
                                            <?php echo $_text[ 'banner_driver_desc' ]; ?>
                                        </div>
                                        <div class="slider-box__buttons">
                                            <div class="slider-box__buttons-GP">
                                                <a href="https://play.google.com/store/apps/details?id=com.amconsoft.guyanataxi.driver&hl=ru" class="transition button-white">
                                                    <img src="html/images/button_google_play.png" alt="" class="transition">
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container how-work">
            <div class="row">
                <div class="col-xs-12">
                    <h2 class="how-work__h2">
                        <?php echo $_text[ 'h2_work_d' ]; ?>
                    </h2>
                </div>
            </div>
            <div class="row how-work__second-row">
                <div class="col-sm-4 col-xs-12">
                    <img src="html/images/Iphone_driver_1.png" alt="" class="how-work__second-img">
                    <p class="how-work__second-desc">
                        <?php echo $_text[ 'h2_work_d_desc_1' ]; ?>
                    </p>
                </div>
                <div class="col-sm-4 col-xs-12">
                    <img src="html/images/Iphone_driver_2.png" alt="" class="how-work__second-img">
                    <p class="how-work__second-desc">
                        <?php echo $_text[ 'h2_work_d_desc_2' ]; ?>
                    </p>
                </div>
                <div class="col-sm-4 col-xs-12">
                    <img src="html/images/Iphone_driver_3.png" alt="" class="how-work__second-img">
                    <p class="how-work__second-desc">
                        <?php echo $_text[ 'h2_work_d_desc_3' ]; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="container more-info">
            <div class="row">
                <div class="col-xs-12">
                    <h2 class="how-info__h2">
                        <?php echo $_text[ 'h2_info_d' ]; ?>
                    </h2>
                    <p class="how-info__description">
                        <?php echo $_text[ 'h2_info_d_desc' ]; ?>
                    </p>
                </div>
            </div>
            <div class="row more-info__second-row">
                <div class="col-sm-6 col-xs-12">
                    <div class="more-info__title">
                        <h3 class="h3 more-info__h3">
                            <?php echo $_text[ 'h2_info_d_title_1' ]; ?>
                        </h3>
                        <p class="more-info__text">
                            <?php echo $_text[ 'h2_info_d_text_1' ]; ?>
                        </p>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <div class="more-info__title">
                        <h3 class="h3 more-info__h3">
                            <?php echo $_text[ 'h2_info_d_title_2' ]; ?>
                        </h3>
                        <p class="more-info__text">
                            <?php echo $_text[ 'h2_info_d_text_2' ]; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </content>
@endsection