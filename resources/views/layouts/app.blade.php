@include('parts.header')
<div id="app">
    @include('parts.navigation')
    @include('parts.msg')
    @yield('content')
</div>
@include('parts.footer')