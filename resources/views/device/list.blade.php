@extends('layouts.app')

@section('pageTitle', __('label.menu.device'))

@section('content')
<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title mt-section">
            <svg role="img" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#mobile"></use><title>Mobile</title></svg>
            <h2 class="title-with-icon-m"><span>{{ __('label.menu.device') }}</span>
                @if(auth()->user()->getMaxMakingDevice() > count($devices))
                <a href="{{ route('device.create') }}" class="btn btn-inline btn-theme-single-green">
                    <svg role="img" class="icon-prefix" aria-hidden="true"><use xlink:href="#plus"></use><title>{{ __('label.btn.add') }}</title></svg>{{ __('label.btn.add') }}</a>
                @endif
            </h2>
        </div>

        @foreach($devices as $device)
        <ul class="list-composite">
            <li class="list-item-with-btn">
                <dl class="data-list-flex">
                    @if($device->getImage() && \Illuminate\Support\Arr::has($device->getImage(), ['ref', 'class']))
                        <svg role="img"
                             class="icon-prefix icon-l icon-circle-l {{ $device->getImage()['class'] }}">
                            <use xlink:href="#{{ $device->getImage()['ref'] }}"></use>
                            <title>{{ $device->name }}</title>
                        </svg>
                    @endif
                    <div class="data-group-text">
                        <dt>{{ $device->name }}</dt>
                        <dd class="dd-explain">{{ $device->description }}</dd>
                    </div>
                </dl>
                <a href="{{ route('device.edit', ['id' => $device->id]) }}" class="btn btn-inline btn-theme-single-main">
                    <svg role="img" class="icon-prefix" aria-hidden="true"><use xlink:href="#edit"></use><title>{{ __('label.btn.edit') }}</title></svg>{{ __('label.btn.edit') }}
                </a>
            </li>
        </ul>
        @endforeach
    </div><!--/.layout-contents-->
</div><!-- /.container -->
@endsection
