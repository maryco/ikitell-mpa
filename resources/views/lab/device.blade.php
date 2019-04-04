@extends('layouts.app')

@section('pageTitle', __('label.page_title.welcome'))

@section('content')
    <div class="container container-lab">

        @include('layouts.menu')

        <div class="layout-contents">

        @foreach($deviceDetails as $detail)
            @php $dashboard = $detail->toArray(); @endphp
            <ul class="list-composite">
                <li class="list-item-with-btn">
                    <dl class="data-list-flex">
                        <div class="data-group-text">
                            <dt>{{ $dashboard['name'] }}</dt>
                            <dd class="dd-explain">
                                <p>最終リセット日時：{{ $dashboard['lastResetAt'] }}</p>
                                <p>リセット期限：{{ $dashboard['resetLimitAt'] }}</p>
                            </dd>
                        </div>
                    </dl>
                    <form action="{{ route('lab.alert', ['id' => $dashboard['id']]) }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-inline btn-theme-single-tint-orange">
                            <svg role="img" class="icon-prefix" aria-hidden="true"><use xlink:href="#alert"></use></svg>
                        </button>
                    </form>
                </li>
                <li>
                    <dashboard-panel
                        :is-first="{{ ($loop->first) ? 'true' : 'false' }}"
                        :initial-device-info="{{ json_encode($detail->toArray()) }}">
                    </dashboard-panel>
                </li>

            </ul>
        @endforeach

        </div>

    </div><!-- /.container -->
@endsection

@section('add-on-content')
@endsection
