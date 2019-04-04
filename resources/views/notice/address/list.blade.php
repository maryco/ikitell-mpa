@extends('layouts.app')

@section('pageTitle', __('label.menu.notice_address'))

@section('content')
<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title mt-section">
            <svg role="img" class="icon-prefix icon-m" aria-hidden="true"><use xlink:href="#emails"></use><title>E-mail</title></svg>
            <h2 class="title-with-icon-m">
                <span>{{ __('label.menu.notice_address') }}</span>
                @if(auth()->user()->getMaxMakingRule() > count($contacts))
                <a href="{{ route('notice.address.create') }}" class="btn btn-inline btn-theme-single-green">
                    <svg role="img" class="icon-prefix" aria-hidden="true"><use xlink:href="#plus"></use><title>{{ __('label.btn.add') }}</title></svg>{{ __('label.btn.add') }}</a>
                @endif
            </h2>
        </div>

        <ul class="list-composite">
            @foreach($contacts as $contact)
            <li class="list-item-with-btn">
                <dl class="data-list-flex">
                    <svg role="img" class="icon-prefix icon-l icon-circle-l" aria-hidden="true">
                        <use xlink:href="#email_tilted"></use>
                        <title>E-mail</title>
                    </svg>
                    <div class="data-group-text data-group-text-has-icon">
                        <dt>{{ $contact->name }}</dt>
                        <dd class="dd-explain">{{ $contact->description }}</dd>
                    </div>
                    <dd class="dd-label {{ $contact->isVerified() ? 'dd-label-green' : '' }} {{ $contact->isVerifyExpired() ? 'dd-label-tint-orange' : '' }}">
                        <svg role="img" class="icon-prefix icon-circle" aria-hidden="true">
                            <use xlink:href="#info"></use>
                            <title>Info</title>
                        </svg>{{ $contact->getVerifyStatus() }}
                    </dd>
                </dl>
                <a href="{{ route('notice.address.edit', ['id' => $contact->id]) }}"
                   class="btn btn-inline btn-theme-single-main">
                    <svg role="img" class="icon-prefix" aria-hidden="true">
                        <use xlink:href="#edit"></use>
                        <title>{{ __('label.btn.edit') }}</title>
                    </svg>{{ __('label.btn.edit') }}</a>
            </li>
            @endforeach
        </ul>

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection
