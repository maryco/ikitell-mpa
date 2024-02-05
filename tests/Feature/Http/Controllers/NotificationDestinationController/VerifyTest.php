<?php

namespace Tests\Feature\Http\Controllers\NotificationDestinationController;

use App\Enums\User\PlanType;
use App\Models\Entities\Contact;
use App\Models\Entities\User;
use App\Notifications\VerifiedContactsNotification;
use App\Notifications\VerifyRequestContactsNotification;
use Closure;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class VerifyTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Contact
     */
    private Contact $contact;

    /**
     * @return void
     * @throws Exception
     */
    public function test_success(): void
    {
        Notification::fake();
        $user = User::where('plan', PlanType::PERSONAL->value)->first();
        $contact = Contact::factory()->for($user)->emailVerifyRequested()->create();

        $this->assertNull($contact->email_verified_at);
        $notification = new VerifyRequestContactsNotification($contact);
        $mail = $notification->toMail($contact);
        $response = $this->get($mail->actionUrl);

        $actual = Contact::find($contact->id);
        $response->assertOk();
        $response->assertViewHas('verified', true);
        $response->assertSeeText(__('message.support.thanks_verified'));
        $this->assertGreaterThanOrEqual(now()->timestamp, $actual->email_verified_at->timestamp);
        $this->assertGreaterThanOrEqual($contact->updated_at->timestamp, $actual->updated_at->timestamp);

        // 依頼者へ承諾通知
        Notification::assertNotSentTo($contact, VerifiedContactsNotification::class);
        Notification::assertSentTo($contact->user, VerifiedContactsNotification::class);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function test_queued_success(): void
    {
        $user = User::where('plan', PlanType::PERSONAL->value)->first();
        $contact = Contact::factory()->for($user)->emailVerifyRequested()->create();

        $notification = new VerifyRequestContactsNotification($contact);
        $mail = $notification->toMail($contact);
        $response = $this->get($mail->actionUrl);
        $response->assertOk();

        $jobs = DB::table('jobs')->select('*')->where('queue', 'default')->get();
        $actualPayload = json_decode($jobs->first()->payload, false, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(VerifiedContactsNotification::class, $actualPayload->displayName);
        $this->assertCount(1, $jobs);
    }

    /**
     * @param Closure $prepareMail
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_test_fail
     */
    public function test_fail(Closure $prepareMail, Closure $assertion): void
    {
        Event::fake(MessageLogged::class);
        Notification::fake();
        $user = User::where('plan', PlanType::PERSONAL->value)->first();
        [$mail, $contact] = $prepareMail($user);
        $response = $this->get($mail->actionUrl);

        $assertion($response, $contact);
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_test_fail(): array
    {
        return [
            'ID不正' => [
                'prepareMail' => function (User $user): array {
                    $contact = Contact::factory()->for($user)->emailVerifyRequested()->create();
                    $notification = new VerifyRequestContactsNotification($contact);
                    $mail = $notification->toMail($contact);
                    $mail->actionUrl = str_replace('address/' . $contact->id, 'address/999', $mail->actionUrl);

                    return [$mail, $contact];
                },
                'assertion' => function (TestResponse $response, Contact $expected) {
                    $response->assertForbidden();
                },
            ],
            'signature不正' => [
                'prepareMail' => function (User $user): array {
                    $contact = Contact::factory()->for($user)->emailVerifyRequested()->create();
                    $notification = new VerifyRequestContactsNotification($contact);
                    $mail = $notification->toMail($contact);
                    $mail->actionUrl = preg_replace('/signature=.+/', 'signature=invalid', $mail->actionUrl);

                    return [$mail, $contact];
                },
                'assertion' => function (TestResponse $response, Contact $expected) {
                    $response->assertForbidden();
                },
            ],
            '承認期限切れ' => [
                'prepareMail' => function (User $user): array {
                    Config::set('specs.verify_limit.contacts', 3);
                    $contact = Contact::factory()->for($user)->emailVerifyRequested(now()->subMinutes(4))->create();
                    return [
                        (new VerifyRequestContactsNotification($contact))->toMail($contact),
                        $contact
                    ];
                },
                'assertion' => function (TestResponse $response, Contact $expected) {
                    $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
                    Event::assertDispatched(MessageLogged::class, static function (MessageLogged $log) {
                        return $log->level === 'error' && str_contains($log->message, 'Expired for verification');
                    });
                },
            ],
            '承諾済み' => [
                'prepareMail' => function (User $user): array {
                    $contact = Contact::factory()->for($user)->emailVerified()->create();
                    return [
                        (new VerifyRequestContactsNotification($contact))->toMail($contact),
                        $contact
                    ];
                },
                'assertion' => function (TestResponse $response, Contact $expected) {
                    $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
                    Event::assertDispatched(MessageLogged::class, static function (MessageLogged $log) {
                        return $log->level === 'error' && str_contains($log->message, 'Already verified');
                    });
                    $actual = Contact::find($expected->id);
                    self::assertSame($expected->email_verified_at->timestamp, $actual->email_verified_at->timestamp);
                },
            ],
            'ユーザー(依頼者)が存在しない' => [
                'prepareMail' => function (User $user): array {
                    $contact = Contact::factory()->for($user)->emailVerifyRequested()->create();
                    $user->delete();
                    return [
                        (new VerifyRequestContactsNotification($contact))->toMail($contact),
                        $contact
                    ];
                },
                'assertion' => function (TestResponse $response, Contact $expected) {
                    $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
                    // 依頼者へのメールは送信なし
                    Notification::assertNotSentTo(
                        User::withTrashed()->find($expected->user_id),
                        VerifiedContactsNotification::class
                    );
                    // 承認は完了する
                    self::assertNotNull(Contact::find($expected->id)->email_verified_at);
                },
            ],
        ];
    }
}
