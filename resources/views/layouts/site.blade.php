@include('parts.site_header')
    @yield('content')
@if($page=='page-selectype')
    @include('parts.site_nofooter')
@else
    @include('parts.site_footer')
@endif