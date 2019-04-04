@extends('layouts.app')

@section('pageTitle', __('label.page_title.terms'))

@section('content')

<div class="container">

    @include('layouts.menu')

    <!--contents-->
    <div class="layout-contents">

        <div class="layout-section-title" id="summary">
            <h2 class="text-italic">{{ __('label.section_title.terms.summary') }}</h2>
        </div>

        <div class="layout-panel panel-with-picture bg-pale-green">
            <div class="panel-title">
                <p class="title-with-icon-m">本サービスをご利用の場合は以下の点についてご承諾ください</p>
            </div>
            <div class="panel-body bg-whitish">
                <p class="text-readable">
                    <svg role="img" class="icon badge icon-white badge-alert"><use xlink:href="#info"></use><title>Info</title></svg>
                    システムがなんらかの原因(ハード、ソフト)によりダウン等した場合は正常な通知が行われない可能性があります。</p>
                <p class="text-readable">
                    <svg role="img" class="icon badge icon-white badge-alert"><use xlink:href="#info"></use><title>Info</title></svg>
                    運用コストが運用者の経済的負担になった場合はサービスを終了します。</p>
                <p class="text-readable">
                    <svg role="img" class="icon badge icon-white badge-alert"><use xlink:href="#info"></use><title>Info</title></svg>
                    運用者自身が運用を続けられない状況に陥った場合は予告なくサービスを終了します。</p>
                <p class="frame-box text-readable text-center text-attention">ご利用は例によって自己責任でお願いします。</p>
            </div>
        </div>

        <div class="layout-section-title" id="details">
            <h2 class="text-italic">{{ __('label.section_title.terms.details') }}</h2>
        </div>

        <div class="layout-panel panel-with-picture panel-flat">
            <div class="panel-title">1. サービスについて</div>
            <div class="panel-body">
                <dl class="panel-data-list">
                    <div class="data-item-pair">
                        <dt>1.1 機能及びサービス内容の変更</dt>
                        <dd>運営者は利用者の承諾を得ることなく、また利用者への事前の通知なく、機能及びサービス内容を変更することができます。利用者は、機能及びサービス内容の変更について、異議を唱えないものとします。</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="layout-panel panel-with-picture panel-flat">
            <div class="panel-title">2. アカウント</div>
            <div class="panel-body">
                <dl class="panel-data-list">
                    <div class="data-item-pair">
                        <dt>2.1 アカウントの設定</dt>
                        <dd>
                            <p>利用者は、本サービスを利用するためにアカウントを作成する必要があります。</p>
                            <p>アカウントは有効なメールアドレスとパスワードで構成されます。</p>
                            <p>利用者は、アカウントに他人のメールアドレスを使用してはなりません。</p>
                        </dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>2.2 アカウントの安全性についての責任</dt>
                        <dd>
                            <p>利用者は、ご自分のアカウントに基づき生じる事態について責任を負うものとします。</p>
                            <p>また、アカウントの無断使用から生じる損害について運営者は責任を負いません。</p>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="layout-panel panel-with-picture panel-flat">
            <div class="panel-title">3. サービスの中断、停止</div>
            <div class="panel-body">
                <dl class="panel-data-list">
                    <div class="data-item-pair">
                        <dt>3.1 利用の中断</dt>
                        <dd>
                            <p>利用者は、本サービスが以下の事由により一時的に利用中断されることがあることを認識し、利用者は、運営者が利用者に対して、この事による責任を一切負わないことに同意するものとします。</p>
                            <p class="text-xs">本サービスの運用サーバその他の設備またはソフトウェアの保守の実施</p>
                            <p class="text-xs">不具合の発生</p>
                            <p class="text-xs">その他、やむを得ない事情によって運営上または技術上運営者が必要とする場合のサービス提供の一時中断</p>
                        </dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>3.2 サービスの変更または中止</dt>
                        <dd>
                            <p>運営者はいつでも本サービスの全部または一部を、一時的または恒久的に、利用者への通知の有無を問わず、変更または中止する権利を有するものとします。</p>
                            <p>利用者は、運営者が利用者に対して、この事による責任を一切負わないことに同意するものとします。</p>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="layout-panel panel-with-picture panel-flat">
            <div class="panel-title">4. 規約の変更</div>
            <div class="panel-body">
                <p>運営者は利用者に明示的な同意なく、サイト上に掲示する事により、本既約を随時変更することができます。</p>
                <p>変更された規約は、規約の変更前から登録されている利用者のコンテンツにも適用されるものとします。</p>
            </div>
        </div>

    </div><!--/.layout-contents-->

</div><!-- /.container -->
@endsection

@section('add-on-content')
    @guest @include('modals.login') @endguest
@endsection

