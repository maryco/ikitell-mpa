{{--

    The list of the specs.

--}}
        <div class="layout-panel panel-with-picture bg-main-theme">
            <div class="panel-body">
                <dl class="panel-data-list">
                    <div class="data-item-pair">
                        <dt>登録可能な端末数</dt>
                        <dd>{{ config('specs.making_device_max.basic') }}</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>タイマーリセット機能</dt>
                        <dd>○</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>タイマーリセット間隔</dt>
                        <dd>{{ config('specs.device_report_interval') }}分に1回まで</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>タイマーリセット期限</dt>
                        <dd>{{ config('specs.time_limit_min')/24 }}日〜{{ config('specs.time_limit_max')/24 }}日まで設定可能</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>休止機能
                            @component('components.help') 指定した期間、タイマーリセットの期限を過ぎても通知メールを送信しません @endcomponent
                        </dt>
                        <dd>○</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>登録可能な通知先数</dt>
                        <dd>{{ config('specs.making_contacts_max.basic') }}</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>1つの端末に設定可能な通知先数</dt>
                        <dd>{{ config('specs.notify_targets_max.basic') }}</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>通知回数</dt>
                        <dd>0〜{{ config('specs.send_notice_max.basic') }}回まで設定可能</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>通知メールへの追加メッセージ</dt>
                        <dd>○</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt>登録可能な通知ルール数</dt>
                        <dd>{{ config('specs.making_rule_max.basic') }}</dd>
                    </div>
                    <div class="data-item-pair">
                        <dt></dt>
                        <dd></dd>
                    </div>
                </dl>
            </div>
        </div>
