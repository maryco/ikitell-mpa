<?php

namespace Tests\Feature\Http\Controllers\RuleController;

use App\Enums\User\PlanType;
use App\Http\Controllers\Controller;
use App\Models\Entities\Rule;
use App\Models\Entities\User;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StoreAsCreateTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var User
     */
    private User $actingUser;

    /**
     * @param Closure $actingUser
     * @param Closure $data
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_create_success
     */
    public function test_create_success(Closure $actingUser, Closure $data, Closure $assertion): void
    {
        $this->actingUser = $actingUser();

        $this->actingAs($this->actingUser);
        $response = $this->post(route('rule.create'), $data($this));

        $expected = Rule::where('user_id', $this->actingUser->id)->latest()->first();
        $response->assertRedirect("rule/{$expected->id}/edit");
        $response->assertSessionHas(Controller::ACTION_RESULT_KEY_SAVE, true);

        $assertion($this, $expected);
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_create_success(): array
    {
        return [
            '必須項目のみ' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    Config::set('specs.time_limit_max', 2400);
                    Config::set('specs.send_notice_max.basic', 456);
                    return [
                        'rule_total' => 0,
                        'rule_name' => '通知ルール名',
                        'rule_time_limits' => 2400,
                        'rule_notify_times' => 456,
                        'rule_message_id' => self::MESSAGE_TEMPLATE_IDS[0],
                    ];
                },
                'assertion' => function (StoreAsCreateTest $case, Rule $actual) {
                    self::assertSame($case->actingUser->id, $actual->user_id);
                    self::assertSame('通知ルール名', $actual->name);
                    self::assertNull($actual->description);
                    self::assertSame(2400, $actual->time_limits);
                    self::assertSame(456, $actual->notify_times);
                    self::assertSame(self::MESSAGE_TEMPLATE_IDS[0], $actual->message_id);
                    self::assertNull($actual->embedded_message);
                    self::assertNotNull($actual->created_at);
                    self::assertNotNull($actual->updated_at);
                },
            ],
            'すべて指定' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    return [
                        'rule_total' => 0,
                        'rule_name' => mb_str_pad('通知ルール名', 200, '*'),
                        'rule_time_limits' => config_int('specs.time_limit_max', 24),
                        'rule_notify_times' => config_int('specs.send_notice_max.basic', 1),
                        'rule_message_id' => self::MESSAGE_TEMPLATE_IDS[1],
                        // 任意項目
                        'rule_description' => mb_str_pad('通知ルール説明文', 300, '*'),
                        'rule_embedded_message' => mb_str_pad('通知メール埋め込みメッセージ', 200, '*')
                    ];
                },
                'assertion' => function (StoreAsCreateTest $case, Rule $actual) {
                    self::assertSame($case->actingUser->id, $actual->user_id);
                    self::assertSame(mb_str_pad('通知ルール名', 200, '*'), $actual->name);
                    self::assertSame(mb_str_pad('通知ルール説明文', 300, '*'), $actual->description);
                    self::assertSame(config_int('specs.time_limit_max', 24), $actual->time_limits);
                    self::assertSame(config_int('specs.send_notice_max.basic', 1), $actual->notify_times);
                    self::assertSame(self::MESSAGE_TEMPLATE_IDS[1], $actual->message_id);
                    self::assertSame(mb_str_pad('通知メール埋め込みメッセージ', 200, '*'), $actual->embedded_message);
                    self::assertNotNull($actual->created_at);
                    self::assertNotNull($actual->updated_at);
                },
            ],
        ];
    }

    /**
     * @param Closure $actingUser
     * @param Closure $data
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_create_fail
     */
    public function test_create_fail(Closure $actingUser, Closure $data, Closure $assertion): void
    {
        $this->actingUser = $actingUser();

        $this->actingAs($this->actingUser);
        $response = $this->post(route('rule.create'), $data($this));
        $response->assertSessionMissing(Controller::ACTION_RESULT_KEY_SAVE);
        $assertion($response, $this);
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_create_fail(): array
    {
        return [
            '必須項目' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    return [];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'rule_total' => '「合計通知ルール数」は必須です',
                        'rule_name' => '「ルール名」は必須です',
                        'rule_time_limits' => '「タイマーリセット期限」は必須です',
                        'rule_notify_times' => '「通知回数」は必須です',
                        'rule_message_id' => '「メッセージ」は必須です',
                    ]);
                },
            ],
            'タイマーリセット期限不正' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    return [
                        'rule_total' => 0,
                        'rule_name' => '通知ルール名',
                        'rule_time_limits' => 25, // NOTE: (正)Min to Max内で24時間単位
                        'rule_notify_times' => 1,
                        'rule_message_id' => self::MESSAGE_TEMPLATE_IDS[0],
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'rule_time_limits' => '選択された 「タイマーリセット期限」 が正しくありません',
                    ]);
                },
            ],
            '通知回数不正' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    return [
                        'rule_total' => 0,
                        'rule_name' => '通知ルール名',
                        'rule_time_limits' => config_int('specs.time_limit_min', 24),
                        'rule_notify_times' => config_int('specs.send_notice_max.basic', 1) + 1,
                        'rule_message_id' => self::MESSAGE_TEMPLATE_IDS[0],
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'rule_notify_times' => '選択された 「通知回数」 が正しくありません',
                    ]);
                },
            ],
            'メッセージID不正' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    return [
                        'rule_total' => 0,
                        'rule_name' => '通知ルール名',
                        'rule_time_limits' => config_int('specs.time_limit_min', 24),
                        'rule_notify_times' => 1,
                        'rule_message_id' => 999,
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'rule_message_id' => '選択された 「メッセージ」 が正しくありません',
                    ]);
                },
            ],
            '通知ルール数上限まで登録済み(plan.personal)' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    Config::set('specs.making_rule_max.basic', 3);
                    Rule::factory()->count(3)->for($case->actingUser)->create();
                    return [
                        'rule_total' => 0,
                        'rule_name' => '通知ルール名',
                        'rule_time_limits' => config_int('specs.time_limit_min', 24),
                        'rule_notify_times' => 1,
                        'rule_message_id' => self::MESSAGE_TEMPLATE_IDS[0],
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'rule_total' => 'すでに上限数 3 件まで登録されています',
                    ]);
                },
            ],
        ];
    }
}
