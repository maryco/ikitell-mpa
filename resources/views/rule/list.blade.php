@extends('layouts.app')

@section('pageTitle', __('label.menu.rule'))

@section('content')
<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title mt-section">
            <svg role="img" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#alert"></use><title>Alert</title></svg>
            <h2 class="title-with-icon-m">
                <span>{{ __('label.menu.rule') }}</span>
                @if(auth()->user()->getMaxMakingRule() >  count($rules))
                <a href="{{ route('rule.create') }}" class="btn btn-inline btn-theme-single-green"><svg role="img" class="icon-prefix" aria-hidden="true"><use xlink:href="#plus"></use><title>{{ __('label.btn.add') }}</title></svg>{{ __('label.btn.add') }}</a>
                @endif
            </h2>
        </div>

        <ul class="list-composite">
            @foreach($rules as $rule)
            <li class="list-item-with-btn">
                <dl class="data-list-flex">
                    <div class="data-group-text">
                        <dt>{{ $rule->name }}</dt>
                        <dd class="dd-explain">{{ $rule->description }}</dd>
                    </div>
                    <dd class="dd-icons">
                    @foreach($rule->device as $device)
                        {{--<img src="" title="{{ $device->name }}">--}}
                        @if($device->getImage() && \Illuminate\Support\Arr::has($device->getImage(), ['ref', 'class']))
                        <svg role="img"
                             class="icon-m icon-circle-l {{ $device->getImage()['class'] }}">
                            <use xlink:href="#{{ $device->getImage()['ref'] }}"></use>
                            <title>{{ $device->name }}</title>
                        </svg>
                        @endif
                    @endforeach
                    </dd>
                </dl>
                <a href="{{ route('rule.edit', ['id' => $rule->id]) }}"
                   class="btn btn-inline btn-theme-single-main">
                    <svg role="img" class="icon-prefix" aria-hidden="true"><use xlink:href="#edit"></use><title>{{ __('label.btn.edit') }}</title></svg>{{ __('label.btn.edit') }}</a>
            </li>
            @endforeach
        </ul>

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection
