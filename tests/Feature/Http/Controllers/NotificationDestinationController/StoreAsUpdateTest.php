<?php

namespace Tests\Feature\Http\Controllers\NotificationDestinationController;

use App\Enums\User\PlanType;
use App\Http\Controllers\Controller;
use App\Models\Entities\Contact;
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
     * @var Contact
     */
    private Contact $myContact;

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
        [$this->myContact, $data] = $data($this);

        $this->actingAs($this->actingUser);
        $response = $this->post(route('notice.address.edit', ['id' => $this->myContact->id]), $data);

        $actual = Contact::where('user_id', $this->actingUser->id)->latest()->first();
        $response->assertRedirect("notice/address/{$actual->id}/edit");
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
                    $contact = Contact::factory()->for($case->actingUser)->emailVerified()->create();
                    Carbon::setTestNow();
                    return [
                        $contact,
                        [
                            'contact_total' => 0,
                            'contact_email' => $contact->email,
                            'contact_name' => $contact->name,
                            'contact_description' => $contact->description,
                        ],
                    ];
                },
                'assertion' => function (StoreAsUpdateTest $case, Contact $actual) {
                    self::assertSame($case->myContact->id, $actual->id);
                    self::assertSame($case->myContact->user_id, $actual->user_id);
                    self::assertSame($case->myContact->email, $actual->email);
                    self::assertSame($case->myContact->name, $actual->name);
                    self::assertSame($case->myContact->description, $actual->description);
                    self::assertSame(
                        $case->myContact->email_verified_at->toString(),
                        $actual->email_verified_at->toString()
                    );
                    self::assertSame(
                        $case->myContact->send_verify_at->toString(),
                        $actual->send_verify_at->toString()
                    );
                    self::assertSame(
                        $case->myContact->created_at->toString(),
                        $actual->created_at->toString()
                    );
                    self::assertSame(
                        $case->myContact->updated_at->toString(),
                        $actual->updated_at->toString()
                    );
                    self::assertNull($actual->deleted_at);
                },
            ],
            'すべて変更' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    Carbon::setTestNow(now()->subMinute());
                    $contact = Contact::factory()->for($case->actingUser)->emailNotVerified()->create();
                    Carbon::setTestNow();
                    return [
                        $contact,
                        [
                            'contact_total' => 0,
                            'contact_email' => 'update-notification-to@test.dev.ikitell.me',
                            'contact_name' => '変更後通知先名',
                            'contact_description' => '変更後通知先説明',
                        ],
                    ];
                },
                'assertion' => function (StoreAsUpdateTest $case, Contact $actual) {
                    self::assertSame($case->myContact->id, $actual->id);
                    self::assertSame('update-notification-to@test.dev.ikitell.me', $actual->email);
                    self::assertSame('変更後通知先名', $actual->name);
                    self::assertSame('変更後通知先説明', $actual->description);
                    self::assertNull($actual->email_verified_at);
                    self::assertNull($actual->send_verify_at);
                    self::assertSame(
                        $case->myContact->created_at->toString(),
                        $actual->created_at->toString()
                    );
                    self::assertGreaterThan($case->myContact->created_at, $actual->updated_at);
                    self::assertNull($actual->deleted_at);
                },
            ],
            '削除済みメールアドレス' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    $contact = Contact::factory()->for($case->actingUser)->emailNotVerified()->create();
                    $deletedContact = Contact::factory()->for($case->actingUser)->create();
                    $deletedContact->delete();
                    return [
                        $contact,
                        [
                            'contact_total' => 0,
                            'contact_email' => $deletedContact->email,
                            'contact_name' => '削除した通知先メールアドレス指定OK',
                        ],
                    ];
                },
                'assertion' => function (StoreAsUpdateTest $case, Contact $actual) {
                    $contacts = Contact::where('email', $actual->email)->withTrashed()->get();
                    self::assertCount(2, $contacts);
                    self::assertCount(1, $contacts->whereNull('deleted_at')->all());
                    self::assertCount(1, $contacts->whereNotNull('deleted_at')->all());
                },
            ],
            '承認依頼メール送信済, メールアドレス変更' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    $contact = Contact::factory()->for($case->actingUser)->emailVerifyRequested()->create();
                    return [
                        $contact,
                        [
                            'contact_total' => 0,
                            'contact_email' => 'ignore-email@test.ikitell.me',
                            'contact_name' => '変更度通知先名',
                            'contact_description' => '承認依頼メール送信済のメールアドレスの変更は無視',
                        ],
                    ];
                },
                'assertion' => function (StoreAsUpdateTest $case, Contact $actual) {
                    self::assertSame($case->myContact->email, $actual->email);
                    self::assertNotSame('ignore-email@test.ikitell.me', $actual->name);
                    self::assertSame('変更度通知先名', $actual->name);
                    // FIXME: 仕様再検討
                    self::assertSame('承認依頼メール送信済のメールアドレスの変更は無視', $actual->description);
                },
            ],
            'メールアドレス承認済, メールアドレス変更' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    $contact = Contact::factory()->for($case->actingUser)->emailVerified()->create();
                    return [
                        $contact,
                        [
                            'contact_total' => 0,
                            'contact_email' => 'ignore-email@test.ikitell.me',
                            'contact_name' => '変更度通知先名',
                            'contact_description' => 'メールアドレス承認済のメールアドレスの変更は無視',
                        ],
                    ];
                },
                'assertion' => function (StoreAsUpdateTest $case, Contact $actual) {
                    self::assertSame($case->myContact->email, $actual->email);
                    self::assertNotSame('ignore-email@test.ikitell.me', $actual->name);
                    self::assertSame('変更度通知先名', $actual->name);
                    // FIXME: 仕様再検討
                    self::assertSame('メールアドレス承認済のメールアドレスの変更は無視', $actual->description);
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
        [$this->myContact, $data] = $data($this);

        $this->actingAs($this->actingUser);
        $response = $this->post(route('notice.address.edit', ['id' => $this->myContact->id]), $data);

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
                    $contact = Contact::factory()->for(User::factory())->emailNotVerified()->create();
                    return [
                        $contact,
                        [
                            'contact_email' => 'update-notification-to@test.dev.ikitell.me',
                            'contact_name' => '変更後通知先名',
                        ],
                    ];
                },
                'assertion' => function (TestResponse $response, StoreAsUpdateTest $case) {
                    $response->assertNotFound();
                },
            ],
            '削除済み通知先を指定' => [
                'actingUser' => fn(): User => User::where('plan', PlanType::PERSONAL->value)->first(),
                'data' => function (StoreAsUpdateTest $case) {
                    $contact = Contact::factory()->for($case->actingUser)->emailNotVerified()->create();
                    $contact->delete();
                    return [
                        $contact,
                        [
                            'contact_email' => 'update-notification-to@test.dev.ikitell.me',
                            'contact_name' => '変更後通知先名',
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
