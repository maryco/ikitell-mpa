<?php

namespace Tests\Feature\Http\Controllers\DeviceController;

use App\Enums\Device\DeviceType;
use App\Enums\User\PlanType;
use App\Http\Controllers\Controller;
use App\Models\Entities\Contact;
use App\Models\Entities\Device;
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
        $response = $this->post(route('device.create'), $data($this));

        $expected = Device::where('owner_id', $this->actingUser->id)->latest()->first();
        $response->assertRedirect("device/{$expected->id}/edit");
        $response->assertSessionHas(Controller::ACTION_RESULT_KEY_SAVE, true);

        $this->assertDefaultAttributes($expected);
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
                    $rule = Rule::factory()->for($case->actingUser)->create();
                    return [
                        'device_total' => 0,
                        'device_name' => 'テスト端末',
                        'device_rule_id' => $rule->id,
                    ];
                },
                'assertion' => function (StoreAsCreateTest $case, Device $actual) {
                    self::assertSame($case->actingUser->id, $actual->owner_id);
                    self::assertSame(
                        Rule::where('user_id', $case->actingUser->id)->first()->id,
                        $actual->rule_id
                    );
                    self::assertSame('テスト端末', $actual->name);
                    self::assertNull($actual->description);
                    self::assertNull($actual->reset_word);
                    self::assertNull($actual->user_name);
                    // Default preset image
                    self::assertSame(
                        json_encode(['type' => 1, 'value' => 1], JSON_THROW_ON_ERROR),
                        $actual->image
                    );
                    self::assertNull($actual->suspend_start_at);
                    self::assertNull($actual->suspend_end_at);

                    // typeは固定
                    self::assertSame(DeviceType::GENERAL->value, $actual->type);

                    self::assertCount(0, $actual->contacts);
                },
            ],
            'すべて指定' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $rule = Rule::factory()->for($case->actingUser)->create();
                    $contact = Contact::factory()->for($case->actingUser)->emailVerified()->create();
                    $presetImage = collect(Device::getPresetImage())->pluck('value')->random(1)->first();
                    return [
                        'device_total' => 0,
                        'device_name' => 'テスト端末',
                        'device_rule_id' => $rule->id,
                        'device_user_name' => '端末利用者名',
                        'device_image_preset' => $presetImage,
                        'device_description' => '説明は200文字まで',
                        'device_reset_word' => mb_str_pad('リセットボタンラベル', 20, '*'),
                        'device_suspend_start_at' => now()->format('Y-m-d'),
                        'device_suspend_end_at' => now()->format('Y-m-d'),
                        'device_notification_targets' => [$contact->id],
                    ];
                },
                'assertion' => function (StoreAsCreateTest $case, Device $actual) {
                    self::assertSame('説明は200文字まで', $actual->description);
                    self::assertSame('リセットボタンラベル**********', $actual->reset_word);
                    self::assertSame('端末利用者名', $actual->user_name);
                    self::assertNotNull($actual->image);
                    // 休止開始は00:00:00
                    self::assertSame('00:00:00', $actual->suspend_start_at->format('H:i:s'));
                    // 休止終了は23:59:59
                    self::assertSame('23:59:59', $actual->suspend_end_at->format('H:i:s'));

                    self::assertCount(1, $actual->contacts);
                },
            ],
            '通知先最大数指定' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $rule = Rule::factory()->for($case->actingUser)->create();
                    Config::set('specs.notify_targets_max.basic', 2);
                    $contacts = Contact::factory()->count(2)->for($case->actingUser)->emailVerified()->create();
                    return [
                        'device_total' => 0,
                        'device_name' => 'テスト端末',
                        'device_rule_id' => $rule->id,
                        'device_notification_targets' => $contacts->pluck('id')->values()->all(),
                    ];
                },
                'assertion' => function (StoreAsCreateTest $case, Device $actual) {
                    self::assertCount(2, $actual->contacts);
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
        $response = $this->post(route('device.create'), $data($this));
        $response->assertSessionMissing(Controller::ACTION_RESULT_KEY_SAVE);
        $assertion($response, $this);
    }

    /**
     * @return array<string, mixed>
     */
    public function data_create_fail(): array
    {
        return [
            '必須項目' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    return [];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'device_total' => '「合計端末数」は必須です',
                        'device_name' => '「端末名」は必須です',
                        'device_rule_id' => '「通知ルール」は必須です',
                    ]);
                },
            ],
            '休止期間開始が過去日' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $rule = Rule::factory()->for($case->actingUser)->create();
                    return [
                        'device_total' => 0,
                        'device_name' => 'テスト端末',
                        'device_rule_id' => $rule->id,
                        'device_suspend_start_at' => now()->subDay()->format('Y-m-d'),
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'device_suspend_start_at' => '「休止期間(開始日)」 は 本日 以降を指定してください',
                    ]);
                },
            ],
            '休止期間開始 < 休止期間終了' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $rule = Rule::factory()->for($case->actingUser)->create();
                    return [
                        'device_total' => 0,
                        'device_name' => 'テスト端末',
                        'device_rule_id' => $rule->id,
                        'device_suspend_start_at' => now()->addDay()->format('Y-m-d'),
                        'device_suspend_end_at' => now()->format('Y-m-d'),
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'device_suspend_end_at' => '「休止期間(終了日)」 は 休止期間(開始日) 以降を指定してください',
                    ]);
                },
            ],
            '通知ルール不正' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    return [
                        'device_total' => 0,
                        'device_name' => 'テスト端末',
                        'device_rule_id' => '1A',
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'device_rule_id' => '「通知ルール」は整数を指定してください',
                    ]);
                },
            ],
            '通知ルール不正(他ユーザーデータを指定)' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $rule = Rule::factory()->for(User::factory()->create())->create();
                    return [
                        'device_total' => 0,
                        'device_name' => 'テスト端末',
                        'device_rule_id' => $rule->id,
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'device_rule_id' => '指定された「通知ルール」が見つかりません',
                    ]);
                },
            ],
            '通知先不正' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $rule = Rule::factory()->for($case->actingUser)->create();
                    return [
                        'device_total' => 0,
                        'device_name' => 'テスト端末',
                        'device_rule_id' => $rule->id,
                        'device_notification_targets' => [999],
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'device_notification_targets' => '指定された「通知先」が見つかりません',
                    ]);
                },
            ],
            '通知先不正(他ユーザーデータを指定)' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $rule = Rule::factory()->for($case->actingUser)->create();
                    $contact = Contact::factory()->for(User::factory()->create())->emailVerified()->create();
                    return [
                        'device_total' => 0,
                        'device_name' => 'テスト端末',
                        'device_rule_id' => $rule->id,
                        'device_notification_targets' => [$contact->id],
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'device_notification_targets' => '指定された「通知先」が見つかりません',
                    ]);
                },
            ],
            '端末数上限まで登録済み(plan.personal)' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $rule = Rule::factory()->for($case->actingUser)->create();
                    Device::factory()->for($case->actingUser, 'ownerUser')->create();
                    return [
                        'device_total' => 0,
                        'device_name' => 'テスト端末',
                        'device_rule_id' => $rule->id,
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'device_total' => sprintf(
                            'すでに上限数 %d 件まで登録されています',
                            config('specs.making_device_max.basic')
                        ),
                    ]);
                },
            ],
        ];
    }

    /**
     * @param Device $device
     * @return void
     */
    private function assertDefaultAttributes(Device $device): void
    {
        self::assertNull($device->assigned_user_id);
        self::assertSame(DeviceType::GENERAL->value, $device->type);
        self::assertFalse((bool)$device->in_alert);
        self::assertFalse((bool)$device->in_suspend);
        self::assertNull($device->reported_at);
        self::assertNull($device->report_reserved_at);
        self::assertNotNull($device->created_at);
        self::assertNotNull($device->updated_at);
    }
}
