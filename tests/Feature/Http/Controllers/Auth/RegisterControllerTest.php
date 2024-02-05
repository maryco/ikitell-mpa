<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Enums\Device\DeviceType;
use App\Enums\User\PlanType;
use App\Models\Entities\User;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @param Closure $setUp
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_register_success
     */
    public function test_register_success(Closure $setUp, Closure $assertion): void
    {
        $data = $setUp();
        $response = $this->post('register', $data);
        $response->assertRedirect('home');
        $this->assertAuthenticated();
        $assertion($response);
    }

    /**
     * @return array
     */
    public static function data_register_success(): array
    {
        return [
            '正常' => [
                'setUp' => fn() => [
                    'email' => 'register_test01@example.com',
                    'password' => '123456',
                    'password_confirmation' => '123456',
                ],
                'assertion' => function (TestResponse $response) {
                    $user = User::where('email', 'register_test01@example.com')->firstOrFail();
                    self::assertSame(PlanType::PERSONAL->value, $user->plan);
                    self::assertSame('', $user->name);
                    self::assertNull($user->email_verified_at);

                    $rule = $user->rules()->first();
                    self::assertSame($user->id, $rule->user_id);
                    self::assertSame(__('label.default.rule.name'), $rule->name);
                    self::assertSame(config('alert.default_template_id'), $rule->message_id);
                    self::assertSame(config('specs.time_limit_min'), $rule->time_limits);
                    self::assertSame(config('specs.send_notice_max.basic'), $rule->notify_times);

                    $device = $user->devices()->first();
                    self::assertSame($user->id, $device->owner_id);
                    self::assertSame(DeviceType::GENERAL->value, $device->type);
                    self::assertSame($rule->id, $device->rule_id);
                    self::assertSame(__('label.default.device.name'), $device->name);
                    self::assertSame(__('label.default.device.user_name'), $device->user_name);
                },
            ],
            'email, 最大文字数' => [
                'setUp' => fn() => [
                    'email' => str_pad('@example.com', 200, 'a', STR_PAD_LEFT),
                    'password' => '123456',
                    'password_confirmation' => '123456',
                ],
                'assertion' => function (TestResponse $response) {
                    $user = User::where('email', 'LIKE', '%aaaaa@example.com')->first();
                    self::assertNotNull($user);
                },
            ],
        ];
    }

    /**
     * @param Closure $setUp
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_register_fail
     */
    public function test_register_fail(Closure $setUp, Closure $assertion): void
    {
        $data = $setUp();
        $response = $this->postJson('register', $data);
        $this->assertGuest();
        $assertion($response);
    }

    /**
     * @return array
     */
    public static function data_register_fail(): array
    {
        return [
            '必須未入力' => [
                'setUp' => fn() => [],
                'assertion' => function (TestResponse $response) {
                    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
                    $response->assertJsonValidationErrors([
                        'email' => '「メールアドレス」は必須です',
                        'password' => '「パスワード」は必須です',
                    ]);
                },
            ],
            'email, 最大文字数' => [
                'setUp' => fn() => [
                    'email' => str_pad('@example.com', 201, 'a', STR_PAD_LEFT),
                    'password' => '123456',
                    'password_confirmation' => '123456',
                ],
                'assertion' => function (TestResponse $response) {
                    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
                    $response->assertJsonValidationErrors([
                        'email' => '「メールアドレス」200 文字以下で入力してください',
                    ]);
                },
            ],
            'email, 重複' => [
                'setUp' => function () {
                    User::factory()->create(['email' => 'email_id_unique@example.com']);
                    return [
                        'email' => 'email_id_unique@example.com',
                        'password' => '123456',
                        'password_confirmation' => '123456',
                    ];
                },
                'assertion' => function (TestResponse $response) {
                    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
                    $response->assertJsonValidationErrors([
                        'email' => '「メールアドレス」すでに登録されています',
                    ]);
                },
            ],
            'password, 文字数' => [
                'setUp' => fn() => [
                    'email' => 'password_length@example.com',
                    'password' => '12345',
                    'password_confirmation' => '12345',
                ],
                'assertion' => function (TestResponse $response) {
                    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
                    $response->assertJsonValidationErrors([
                        'password' => '6 文字以上で入力してください',
                    ]);
                },
            ],
        ];
    }
}
