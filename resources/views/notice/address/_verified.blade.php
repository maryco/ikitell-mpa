@extends('layouts.app')

@section('pageTitle', __('label.page_title.verify_email'))

@section('content')
<div class="container">

    {{--@include('layouts.menu')--}}

    <!--contents-->
    <div class="layout-contents">
        <p>{{ __('message.support.thanks_verified') }}</p>

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection

@section('add-on-content')
    @include('modals.login')
@endsection
