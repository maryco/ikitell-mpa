@extends('layouts.app')

@section('pageTitle', __('label.page_title.about'))

@section('content')
    @include('svg.logo_variety')

<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title" id="outline">
            <h2 class="text-italic">{{ __('label.section_title.about.outline') }}</h2>
        </div>

        <div class="layout-panel panel-with-picture">
            <div class="panel-body">
                <p class="text-readable">本サービスは、「おひとりさま」の不測の事態を誰かにお知らせするための自己防衛サービスです。</p>
                <p class="text-readable">好んで「ひとり」の人も、やむなく「ひとり」の人も、もしもの時に多くの人に迷惑をかけてしまう事態を発生させてしまうのは本望ではないと思います。その最悪の事態を防ぐことを目的としています。</p>
                <p class="text-readable">基本的に自分自身に必要だと危機感を持っていたので自分用ですが、ひとりで使用するにはサーバのリソースが勿体無いため、同じように必要としている人にもご利用いただけるようなWEBアプリケーションとして整備しました。</p>
                <p class="text-xs">(最低限動作すると思いますが「IE(11以上)」でのご利用は非推奨です。)</p>
                <ul class="layout-h-btn-box">
                    <li><a class="btn btn-theme-main-flip" href="#usage">使い方</a></li>
                    <li><a class="btn btn-theme-main-flip" href="{{ url('/') }}">Homeへ</a></li>
                </ul>
            </div>
        </div>

        <div class="layout-section-title" id="usage">
            <h2 class="text-italic">{{ __('label.section_title.about.usage') }}</h2>
        </div>

        <collapse-panel
            v-bind:initial-open="{{ boolstr($panelState['usage_step01']) }}"
            v-bind:panel-base-class="{{ json_encode(['panel-with-picture' => true, 'panel-theme-green' => true]) }}"
            v-bind:panel-body-class="{{ json_encode(['bg-whitish' => true, 'text-center' => true]) }}">
            <div slot="panelTitle" class="panel-title" id="usage_step01">
                <p><span class="text-emp sp-break">ステップ-01.</span>アカウントを作成する<p>
            </div>
            <template slot="panelBody">
                <img src="{{ asset('images/illustration/howto_step01.png') }}" title="" class="panel-main-picture"></img>
            </template>
        </collapse-panel>

        <collapse-panel
            v-bind:initial-open="{{ boolstr($panelState['usage_step02']) }}"
            v-bind:panel-base-class="{{ json_encode(['panel-with-picture' => true, 'panel-theme-green' => true]) }}"
            v-bind:panel-body-class="{{ json_encode(['bg-whitish' => true]) }}">
            <div slot="panelTitle" class="panel-title" id="usage_step02">
                <p><span class="text-emp sp-break">ステップ-02.</span>通知先を作成する<p>
            </div>
            <template slot="panelBody">
                <img src="{{ asset('images/illustration/howto_step02.png') }}" title="" class="panel-main-picture"></img>
                <p class="frame-box">
                    <svg role="img" class="icon icon-circle-l icon-white badge-info"><use xlink:href="#info"></use><title>Info</title></svg>
                    通知先としてお願いする方の「承認」が必要ですので、別途事前に連絡しておくことをお勧めします。</p>
                <p class="frame-box">
                    <svg role="img" class="icon icon-circle-l icon-white badge-info"><use xlink:href="#info"></use><title>Info</title></svg>
                    通知先としてお願いできそうな人がそもそも思い当たらない場合は、お住いの家の大家さん、管理会社、地域の民生委員などを当たってみるとよいかもしれません。</p>
            </template>
        </collapse-panel>

        <collapse-panel
            v-bind:initial-open="{{ boolstr($panelState['usage_step03']) }}"
            v-bind:panel-base-class="{{ json_encode(['panel-with-picture' => true, 'panel-theme-green' => true]) }}"
            v-bind:panel-body-class="{{ json_encode(['bg-whitish' => true]) }}">
            <div slot="panelTitle" class="panel-title" id="usage_step03">
                <p><span class="text-emp sp-break">ステップ-03.</span>通知ルールを作成する<p>
            </div>
            <template slot="panelBody">
                <img src="{{ asset('images/illustration/howto_step03.png') }}" title="" class="panel-main-picture"></img>
                <p>通知回数、通知メールの内容などの通知のルールを作成します。</p>
            </template>
        </collapse-panel>

        <collapse-panel
            v-bind:initial-open="{{ boolstr($panelState['usage_step04']) }}"
            v-bind:panel-base-class="{{ json_encode(['panel-with-picture' => true, 'panel-theme-green' => true]) }}"
            v-bind:panel-body-class="{{ json_encode(['bg-whitish' => true]) }}">
            <div slot="panelTitle" class="panel-title" id="usage_step04">
                <p><span class="text-emp sp-break">ステップ-04.</span>端末に作成した通知先とルールを設定する<p>
            </div>
            <template slot="panelBody">
                <img src="{{ asset('images/illustration/howto_step04.png') }}" title="" class="panel-main-picture"></img>
                <p>ステップ02、03で作成した通知先とルールを端末に設定します。</p>
                <p class="frame-box">
                    <svg role="img" class="icon icon-circle-l icon-white badge-info"><use xlink:href="#info"></use><title>Info</title></svg>
                    リセット期限は、もしもの時に助かる可能性を上げたい場合は短めに。面倒な(または腐らなければよい)場合は長めに設定しておくと良いでしょう。</p>
            </template>
        </collapse-panel>

        <div class="layout-panel panel-with-picture bg-main-theme" style="margin-bottom: 12px;">
            <div class="panel-title" id="how_reset">
                <p><span class="text-emp sp-break">完了！</span>自分で指定した期限内に端末のタイマーをリセットする</p>
            </div>
        </div>

        <dashboard-panel
            :is-first="{{ boolstr($panelState['how_reset']) }}"
            :initial-device-info="{{ json_encode($mockDashboard->toArray()) }}">
        </dashboard-panel>

        <collapse-panel
            v-bind:initial-open="{{ boolstr($panelState['how_alert']) }}"
            v-bind:panel-base-class="{{ json_encode(['panel-with-picture' => true, 'panel-theme-alert' => true]) }}"
            v-bind:panel-body-class="{{ json_encode(['bg-whitish' => true]) }}">
            <div slot="panelTitle" class="panel-title" id="how_alert">
                <p>もし、タイマーの期限を過ぎてしまった場合。。。<p>
            </div>
            <template slot="panelBody">
                <img src="{{ asset('images/illustration/how_alert.png') }}" title="" class="panel-main-picture"></img>
                <p class="frame-box">
                    <svg role="img" class="icon icon-circle-l icon-white badge-alert"><use xlink:href="#info"></use><title>Info</title></svg>
                    通知の０回目は予告として自分にだけ通知メールを送信します。</p>
                <p class="frame-box">
                    <svg role="img" class="icon icon-circle-l icon-white badge-alert"><use xlink:href="#info"></use><title>Info</title></svg>
                    １回目以降は自分と設定した通知先へ通知メールを送信します。</p>
            </template>
        </collapse-panel>

        <div class="layout-section-title" id="specs">
            <h2 class="text-italic">{{ __('label.section_title.about.specs') }}</h2>
        </div>

        @include('docs.about_specs')

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection

@section('add-on-content')
    @guest @include('modals.login') @endguest
@endsection
