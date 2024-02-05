<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Enums\User\PlanType;
use App\Models\Entities\User;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * NOTE: 'remember_token'の検証未実装
     *
     * @param Closure $data
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_login_success
     */
    public function test_login_success(Closure $data, Closure $assertion): void
    {
        $response = $this->post('login', $data());
        $response->assertRedirect('home');
        $this->assertAuthenticated();
        $assertion($response);
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_login_success(): array
    {
        return [
            'Personalプランユーザー' => [
                'data' => function () {
                    $user = User::where('plan', PlanType::PERSONAL->value)->first();
                    return [
                        'email' => $user->email,
                        'password' => 'secret',
                    ];
                },
                'assertion' => fn(TestResponse $response) => null,
            ],
            'Email未承認ユーザー' => [
                'data' => function () {
                    $user = User::where('plan', PlanType::PERSONAL->value)->first();
                    $user->update(['email_verified_at' => null]);
                    return [
                        'email' => $user->email,
                        'password' => 'secret',
                    ];
                },
                'assertion' => fn(TestResponse $response) => null,
            ],
            'Businessプランユーザー' => [
                'data' => function () {
                    $user = User::where('plan', PlanType::BUSINESS->value)->first();
                    $user->update(['email_verified_at' => null]);
                    return [
                        'email' => $user->email,
                        'password' => 'secret',
                    ];
                },
                'assertion' => fn(TestResponse $response) => null,
            ],
            'Limitedプランユーザー' => [
                'data' => function () {
                    $user = User::where('plan', PlanType::LIMITED->value)->first();
                    $user->update(['email_verified_at' => null]);
                    return [
                        'email' => $user->email,
                        'password' => 'secret',
                    ];
                },
                'assertion' => fn(TestResponse $response) => null,
            ],
        ];
    }

    /**
     * @param Closure $data
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_login_fail
     */
    public function test_login_fail(Closure $data, Closure $assertion): void
    {
        $response = $this->post('login', $data());
        $this->assertGuest();
        $assertion($response);
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_login_fail(): array
    {
        return [
            '未入力' => [
                'data' => function () {
                    return [
                        'email' => '',
                        'password' => '',
                    ];
                },
                'assertion' => function (TestResponse $response) {
                    $response->assertInvalid([
                        'email' => '「メールアドレス」は必須です',
                        'password' => '「パスワード」は必須です',
                    ]);
                },
            ],
            'Email(誤)' => [
                'data' => function () {
                    return [
                        'email' => 'missing@example.com',
                        'password' => 'secret',
                    ];
                },
                'assertion' => function (TestResponse $response) {
                    $response->assertInvalid([
                        'email' => 'メールアドレスまたはパスワードが一致しません。',
                    ]);
                },
            ],
            'Email正, Password不正' => [
                'data' => function () {
                    $user = User::where('plan', PlanType::PERSONAL->value)->first();
                    return [
                        'email' => $user->email,
                        'password' => '123456',
                    ];
                },
                'assertion' => function (TestResponse $response) {
                    $response->assertInvalid([
                        'email' => 'メールアドレスまたはパスワードが一致しません。',
                    ]);
                },
            ],
            // TODO: 'Banユーザー' => [], // 未実装
            // TODO: 'Email承認期限切れ' => [], // 未実装
        ];
    }

    /**
     * @return void
     */
    public function test_already_login(): void
    {
        $user = User::where('plan', PlanType::PERSONAL->value)->first();
        $this->actingAs($user);

        $response = $this->post('login', ['email' => $user->email, 'password' => 'secret']);
        $response->assertRedirect('home');
        $this->assertAuthenticated();
    }
}
