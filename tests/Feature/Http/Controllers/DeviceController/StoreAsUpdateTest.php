<?php

namespace Tests\Feature\Http\Controllers\DeviceController;

use App\Enums\Device\DeviceType;
use App\Enums\User\PlanType;
use App\Http\Controllers\Controller;
use App\Models\Entities\Contact;
use App\Models\Entities\Device;
use App\Models\Entities\Rule;
use App\Models\Entities\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
     * @var Device
     */
    private Device $ownedDevice;

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
        [$this->ownedDevice, $data] = $data($this);

        $this->actingAs($this->actingUser);
        $response = $this->post(route('device.edit', ['id' => $this->ownedDevice->id]), $data);

        $expected = Device::where('owner_id', $this->actingUser->id)->latest()->first();
        $response->assertRedirect("device/{$expected->id}/edit");
        $response->assertSessionHas(Controller::ACTION_RESULT_KEY_EDIT, true);

        $assertion($this, $expected);
    }

    /**
     * @return array
     */
    public static function data_update_success(): array
    {
        return [
            '変更なし' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    Carbon::setTestNow(now()->subMinute());
                    $device = Device::factory()->for($case->actingUser, 'ownerUser')
                        ->suspendNow()
                        ->for(Rule::factory()->for($case->actingUser), 'rule')
                        ->create();
                    Carbon::setTestNow();
                    $imageValue = json_decode($device->image, false, 512, JSON_THROW_ON_ERROR)->value;
                    return [
                        $device,
                        [
                            'device_total' => 0,
                            'device_name' => $device->name,
                            'device_rule_id' => $device->rule->id,
                            'device_user_name' => $device->user_name,
                            'device_image_preset' => $imageValue,
                            'device_description' => $device->description,
                            'device_reset_word' => $device->reset_word,
                            'device_suspend_start_at' => $device->suspend_start_at->format('Y-m-d'),
                            'device_suspend_end_at' => $device->suspend_end_at->format('Y-m-d'),
                            'device_notification_targets' => [],
                        ],
                    ];
                },
                'assertion' => function (StoreAsUpdateTest $case, Device $actual) {
                    self::assertSame($case->ownedDevice->owner_id, $actual->owner_id);
                    self::assertSame(
                        $case->ownedDevice->rule_id,
                        $actual->rule_id
                    );
                    self::assertSame($case->ownedDevice->name, $actual->name);
                    self::assertSame($case->ownedDevice->description, $actual->description);
                    self::assertSame($case->ownedDevice->reset_word, $actual->reset_word);
                    self::assertSame($case->ownedDevice->user_name, $actual->user_name);
                    self::assertSame($case->ownedDevice->image, $actual->image);
                    self::assertSame(
                        $case->ownedDevice->suspend_start_at->toString(),
                        $actual->suspend_start_at->toString()
                    );
                    self::assertSame(
                        $case->ownedDevice->suspend_end_at->toString(),
                        $actual->suspend_end_at->toString()
                    );
                    self::assertSame(
                        $case->ownedDevice->created_at->toString(),
                        $actual->created_at->toString()
                    );
                    self::assertSame(
                        $case->ownedDevice->updated_at->toString(),
                        $actual->updated_at->toString()
                    );

                    self::assertCount(0, $actual->contacts);
                },
            ],
            'すべて変更' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    Carbon::setTestNow('2024-01-28 19:15');
                    $device = Device::factory()
                        ->for($case->actingUser, 'ownerUser')
                        ->for(Rule::factory()->for($case->actingUser), 'rule')
                        ->imagePreset(1)
                        ->create();
                    Carbon::setTestNow();

                    $rule = Rule::factory()->for($case->actingUser)->create();
                    $contact = Contact::factory()->for($case->actingUser)->emailVerified()->create();
                    return [
                        $device,
                        [
                            'device_total' => 0,
                            'device_name' => 'テスト端末',
                            'device_rule_id' => $rule->id,
                            'device_user_name' => '端末利用者名',
                            'device_image_preset' => 2,
                            'device_description' => '説明は200文字まで',
                            'device_reset_word' => mb_str_pad('リセットボタンラベル', 20, '*'),
                            'device_suspend_start_at' => now()->addDay()->format('Y-m-d'),
                            'device_suspend_end_at' => now()->addMonth()->format('Y-m-d'),
                            'device_notification_targets' => [$contact->id],
                        ],
                    ];
                },
                'assertion' => function (StoreAsUpdateTest $case, Device $actual) {
                    self::assertSame('テスト端末', $actual->name);
                    self::assertNotSame($case->ownedDevice->rule_id, $actual->rule_id);
                    self::assertSame('説明は200文字まで', $actual->description);
                    self::assertSame('リセットボタンラベル**********', $actual->reset_word);
                    self::assertSame('端末利用者名', $actual->user_name);
                    self::assertSame(
                        json_encode(['type' => 1, 'value' => 2], JSON_THROW_ON_ERROR),
                        $actual->image
                    );
                    self::assertNotSame(
                        $case->ownedDevice->suspend_start_at?->toString(),
                        $actual->suspend_start_at->toString()
                    );
                    self::assertNotSame(
                        $case->ownedDevice->suspend_end_at?->toString(),
                        $actual->suspend_end_at->toString()
                    );

                    self::assertCount(1, $actual->contacts);
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
        [$this->ownedDevice, $data] = $data($this);

        $this->actingAs($this->actingUser);
        $response = $this->post(route('device.edit', ['id' => $this->ownedDevice->id]), $data);

        $response->assertSessionMissing(Controller::ACTION_RESULT_KEY_EDIT);
        $assertion($response, $this);
    }

    /**
     * @return array<string, mixed>
     */
    public function data_update_fail(): array
    {
        return [
            '他ユーザーの端末を指定' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    $rule = Rule::factory()->for($case->actingUser)->create();
                    $device = Device::factory()->for(User::factory()->create(), 'ownerUser')
                        ->for(Rule::factory()->for($case->actingUser), 'rule')
                        ->create();
                    return [
                        $device,
                        [
                            'device_total' => 0,
                            'device_name' => 'テスト端末',
                            'device_rule_id' => $rule->id,
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
                    $device = Device::factory()->for($case->actingUser, 'ownerUser')
                        ->suspendNow()
                        ->for(Rule::factory()->for($case->actingUser), 'rule')
                        // ->trashed() // Laravel 10^
                        ->create();
                    $device->delete();
                    return [
                        $device,
                        [
                            'device_total' => 0,
                            'device_name' => 'テスト端末',
                            'device_rule_id' => $device->rule->id,
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
