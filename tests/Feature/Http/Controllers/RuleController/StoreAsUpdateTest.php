<?php

namespace Tests\Feature\Http\Controllers\RuleController;

use App\Enums\User\PlanType;
use App\Http\Controllers\Controller;
use App\Models\Entities\Rule;
use App\Models\Entities\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StoreAsUpdateTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var User
     */
    private User $actingUser;

    /**
     * @var Rule
     */
    private Rule $myRule;

    /**
     * @param Closure $actingUser
     * @param Closure $data
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_update_success
     */
    public function test_update_success(Closure $actingUser, Closure $data, Closure $assertion): void
    {
        $this->actingUser = $actingUser();
        [$this->myRule, $data] = $data($this);

        $this->actingAs($this->actingUser);
        $response = $this->post(route('rule.edit', ['id' => $this->myRule->id]), $data);

        $actual = Rule::where('user_id', $this->actingUser->id)->latest()->first();
        $response->assertRedirect("rule/{$actual->id}/edit");
        $response->assertSessionHas(Controller::ACTION_RESULT_KEY_EDIT, true);

        $assertion($this, $actual);
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_update_success(): array
    {
        return [
            '変更なし' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    Carbon::setTestNow(now()->subMinute());
                    $rule = Rule::factory()->for($case->actingUser)->create([
                        'time_limits' => 24 * 5,
                        'notify_times' => 2,
                        'message_id' => self::MESSAGE_TEMPLATE_IDS[0],
                    ]);
                    Carbon::setTestNow();
                    return [
                        $rule,
                        [
                            'rule_total' => 0,
                            'rule_name' => $rule->name,
                            'rule_time_limits' => $rule->time_limits,
                            'rule_notify_times' => $rule->notify_times,
                            'rule_message_id' => $rule->message_id,
                            'rule_description' => $rule->description,
                            'rule_embedded_message' => $rule->embedded_message,
                        ],
                    ];
                },
                'assertion' => function (StoreAsUpdateTest $case, Rule $actual) {
                    self::assertSame($case->myRule->user_id, $actual->user_id);
                    self::assertSame($case->myRule->id, $actual->id);
                    self::assertSame($case->myRule->name, $actual->name);
                    self::assertSame($case->myRule->description, $actual->description);
                    self::assertSame($case->myRule->time_limits, $actual->time_limits);
                    self::assertSame($case->myRule->notify_times, $actual->notify_times);
                    self::assertSame($case->myRule->message_id, $actual->message_id);
                    self::assertSame($case->myRule->embedded_message, $actual->embedded_message);
                    self::assertSame(
                        $case->myRule->created_at->toString(),
                        $actual->created_at->toString()
                    );
                    self::assertSame(
                        $case->myRule->updated_at->toString(),
                        $actual->updated_at->toString()
                    );
                },
            ],
            'すべて変更' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    Carbon::setTestNow(now()->subMinute());
                    $rule = Rule::factory()->for($case->actingUser)->create([
                        'time_limits' => 24 * 5,
                        'notify_times' => 2,
                        'message_id' => self::MESSAGE_TEMPLATE_IDS[0],
                    ]);
                    Carbon::setTestNow();
                    return [
                        $rule,
                        [
                            'rule_total' => 0,
                            'rule_name' => '新通知ルール',
                            'rule_time_limits' => 48,
                            'rule_notify_times' => 1,
                            'rule_message_id' => self::MESSAGE_TEMPLATE_IDS[1],
                            'rule_description' => '新通知ルールの説明',
                            'rule_embedded_message' => '新追加のメッセージ',
                        ],
                    ];
                },
                'assertion' => function (StoreAsUpdateTest $case, Rule $actual) {
                    self::assertSame($case->myRule->user_id, $actual->user_id);
                    self::assertSame($case->myRule->id, $actual->id);
                    self::assertSame('新通知ルール', $actual->name);
                    self::assertSame('新通知ルールの説明', $actual->description);
                    self::assertSame(48, $actual->time_limits);
                    self::assertSame(1, $actual->notify_times);
                    self::assertSame(self::MESSAGE_TEMPLATE_IDS[1], $actual->message_id);
                    self::assertSame('新追加のメッセージ', $actual->embedded_message);
                    self::assertSame($case->myRule->created_at->toString(), $actual->created_at->toString());
                    self::assertGreaterThan($case->myRule->updated_at->timestamp, $actual->updated_at->timestamp);
                },
            ],
        ];
    }

    /**
     * @param Closure $actingUser
     * @param Closure $data
     * @param Closure $assertion
     * @return void
     * @see StoreAsCreateTest Requestクラス共通のため、path parameterのみ検証
     *
     * @dataProvider data_update_fail
     */
    public function test_update_fail(Closure $actingUser, Closure $data, Closure $assertion): void
    {
        $this->actingUser = $actingUser();
        [$this->myRule, $data] = $data($this);

        $this->actingAs($this->actingUser);
        $response = $this->post(route('rule.edit', ['id' => $this->myRule->id]), $data);

        $response->assertSessionMissing(Controller::ACTION_RESULT_KEY_EDIT);
        $assertion($response, $this);
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_update_fail(): array
    {
        return [
            '他ユーザーのデータを指定' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    $rule = Rule::factory()->for(User::factory()->create())
                        ->create(['message_id' => self::MESSAGE_TEMPLATE_IDS[0]]);
                    return [
                        $rule,
                        [
                            'rule_total' => 0,
                            'rule_name' => $rule->name,
                            'rule_time_limits' => 24,
                            'rule_notify_times' => 1,
                            'rule_message_id' => $rule->message_id,
                        ],
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsUpdateTest $case) {
                    $response->assertNotFound();
                },
            ],
            '削除端末を指定' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    $rule = Rule::factory()->for($case->actingUser)
                        ->create(['message_id' => self::MESSAGE_TEMPLATE_IDS[0]]);
                    $rule->delete();
                    return [
                        $rule,
                        [
                            'rule_total' => 0,
                            'rule_name' => '通知ルール(削除済)',
                            'rule_time_limits' => 24,
                            'rule_notify_times' => 1,
                            'rule_message_id' => $rule->message_id,
                        ],
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsUpdateTest $case) {
                    $response->assertNotFound();
                },
            ],
        ];
    }
}
