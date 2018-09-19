@if(Session::has('msg-default'))<span class="label label-default">{{Session::get('msg-default')}}</span>@endif
@if(Session::has('msg-primary'))<span class="label label-primary">{{Session::get('msg-primary')}}</span>@endif
@if(Session::has('msg-success'))<span class="label label-success">{{Session::get('msg-success')}}</span>@endif
@if(Session::has('msg-info'))<span class="label label-info">{{Session::get('msg-info')}}</span>@endif
@if(Session::has('msg-warning'))<span class="label label-warning">{{Session::get('msg-warning')}}</span>@endif
@if(Session::has('msg-danger'))<span class="label label-danger">{{Session::get('msg-danger')}}</span>@endif