<?php

namespace Tests\Feature\Http\Controllers\NotificationDestinationController;

use App\Enums\User\PlanType;
use App\Http\Controllers\Controller;
use App\Models\Entities\Contact;
use App\Models\Entities\User;
use Carbon\Carbon;
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
        $response = $this->post(route('notice.address.create'), $data($this));

        $expected = Contact::where('user_id', $this->actingUser->id)->latest()->first();
        $response->assertRedirect("notice/address/{$expected->id}/edit");
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
                    return [
                        'contact_total' => 100, // TODO: この項目は必須だがPOST値に意味はないので除去する
                        'contact_email' => 'notification-to@example.com',
                        'contact_name' => 'テスト通知先',
                    ];
                },
                'assertion' => function (StoreAsCreateTest $case, Contact $actual) {
                    self::assertSame($case->actingUser->id, $actual->user_id);
                    self::assertSame('notification-to@example.com', $actual->email);
                    self::assertSame('テスト通知先', $actual->name);
                    self::assertNull($actual->description);
                    self::assertNull($actual->email_verified_at);
                    self::assertNull($actual->send_verify_at);
                    self::assertNotNull($actual->created_at);
                    self::assertNotNull($actual->updated_at);
                    self::assertNull($actual->deleted_at);
                },
            ],
            'すべて指定' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    // 上限までデータ作成、1件削除
                    Config::set('specs.making_contacts_max.basic', 3);
                    Carbon::setTestNow(now()->subMinutes(3));
                    $contacts = Contact::factory()->count(3)->for($case->actingUser)->create();
                    $contacts->first()->delete();
                    Carbon::setTestNow();
                    return [
                        'contact_total' => 0,
                        'contact_email' => str_pad('notification-to@example.com', 200, 'n', STR_PAD_LEFT),
                        'contact_name' => str_pad('テスト通知先', 50, '*'),
                        'contact_description' => str_pad('テスト通知先の説明', 300, '*'),
                    ];
                },
                'assertion' => function (StoreAsCreateTest $case, Contact $actual) {
                    self::assertSame($case->actingUser->id, $actual->user_id);
                    self::assertSame(200, strlen($actual->email));
                    self::assertSame('テスト通知先********************************', $actual->name);
                    self::assertSame(300, strlen($actual->description));
                    self::assertNull($actual->email_verified_at);
                    self::assertNull($actual->send_verify_at);
                    self::assertNotNull($actual->created_at);
                    self::assertNotNull($actual->updated_at);
                    self::assertNull($actual->deleted_at);
                },
            ],
            '他ユーザーアカウントのメールアドレス' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    return [
                        'contact_total' => 0,
                        'contact_email' => User::factory()->create()->email,
                        'contact_name' => '他ユーザーアカウントのメールアドレス指定OK',
                    ];
                },
                'assertion' => function (StoreAsCreateTest $case, Contact $actual) {
                    self::assertTrue(User::where('email', $actual->email)->exists());
                },
            ],
            '削除済みメールアドレス' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $contact = Contact::factory()->for($case->actingUser)->create();
                    $contact->delete();
                    return [
                        'contact_total' => 0,
                        'contact_email' => $contact->email,
                        'contact_name' => '削除した通知先メールアドレス指定OK',
                    ];
                },
                'assertion' => function (StoreAsCreateTest $case, Contact $actual) {
                    $contacts = Contact::where('email', $actual->email)->withTrashed()->get();
                    self::assertCount(2, $contacts);
                    self::assertCount(1, $contacts->whereNull('deleted_at')->all());
                    self::assertCount(1, $contacts->whereNotNull('deleted_at')->all());
                },
            ],
            '削除済みメールアドレス(他ユーザー)' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $contact = Contact::factory()->for(User::factory())->create();
                    $contact->delete();
                    return [
                        'contact_total' => 0,
                        'contact_email' => $contact->email,
                        'contact_name' => '他ユーザーの削除した通知先メールアドレス指定OK',
                    ];
                },
                'assertion' => function (StoreAsCreateTest $case, Contact $actual) {
                    $contacts = Contact::where('email', $actual->email)->withTrashed()->get();
                    self::assertCount(2, $contacts);
                    self::assertCount(1, $contacts->whereNull('deleted_at')->all());
                    self::assertCount(1, $contacts->whereNotNull('deleted_at')->all());
                    self::assertNotSame($contacts[0]->user_id, $contacts[1]->user_id);
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
        $response = $this->post(route('notice.address.create'), $data($this));
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
                        'contact_total' => '「合計通知先数」は必須です',
                        'contact_email' => '「通知先メールアドレス」は必須です',
                        'contact_name' => '「通知先名」は必須です',
                    ]);
                },
            ],
            '自身のアカウントのメールアドレス' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    return [
                        'contact_total' => 0,
                        'contact_email' => $case->actingUser->email,
                        'contact_name' => '自分のメールアドレスは指定不可',
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'contact_email' => 'ログインIDとして使用しているアドレスは使用できません',
                    ]);
                },
            ],
            '登録済みメールアドレス(自身)' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $contact = Contact::factory()->for($case->actingUser)->create();
                    return [
                        'contact_total' => 0,
                        'contact_email' => $contact->email,
                        'contact_name' => '登録済みメールアドレスは指定不可',
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'contact_email' => '「通知先メールアドレス」すでに登録されています',
                    ]);
                },
            ],
            '登録済みメールアドレス(他ユーザー)' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    $contact = Contact::factory()->for(User::factory()->create())->create();
                    return [
                        'contact_total' => 0,
                        'contact_email' => $contact->email,
                        'contact_name' => '他ユーザーの登録済みメールアドレスは指定不可',
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'contact_email' => '「通知先メールアドレス」すでに登録されています',
                    ]);
                },
            ],
            '上限まで登録済み(plan.personal)' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsCreateTest $case) {
                    Config::set('specs.making_contacts_max.basic', 3);
                    Contact::factory()->count(3)->for($case->actingUser)->create();
                    return [
                        'contact_total' => 0,
                        'contact_email' => 'over-max@text.ikitell.me',
                        'contact_name' => '上限まで登録済',
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsCreateTest $case) {
                    $response->assertInvalid([
                        'contact_total' => 'すでに上限数 3 件まで登録されています',
                    ]);
                },
            ],
        ];
    }
}
