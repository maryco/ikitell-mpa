@extends('layouts.app')

@section('pageTitle', __('label.page_title.welcome'))

{{-- NOTE: Activate this comment out section if hide header. --}}
{{--@section('header')--}}
{{--@endsection--}}

@section('content')
<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection

{{-- NOTE: Activate this comment out section if hide footer. --}}
{{--@section('footer')--}}
{{--@endsection--}}

@section('add-on-content')
@endsection
