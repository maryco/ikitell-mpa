<?php

namespace Tests\Feature\Http\Controllers\NotificationDestinationController;

use App\Enums\User\PlanType;
use App\Models\Entities\Contact;
use App\Models\Entities\User;
use App\Notifications\VerifyRequestContactsNotification;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SendVerificationTest extends TestCase
{
    use DatabaseTransactions;

    public static string $errorMessage = '通知先が「承認済み」、または最後の承認依頼メールの送信から「%d分」経過していない場合は送信できません';

    /**
     * @var User
     */
    private User $actingUser;

    /**
     * @var Contact
     */
    private Contact $myContact;

    /**
     * @param Closure $data
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_test_success
     */
    public function test_success(Closure $data, Closure $assertion): void
    {
        Notification::fake();
        $this->actingUser = User::where('plan', PlanType::PERSONAL->value)->first();
        $this->myContact = $data($this);

        $this->actingAs($this->actingUser);
        $response = $this->post(route('notice.address.verify.send', ['id' => $this->myContact->id]));

        $actual = Contact::where('user_id', $this->actingUser->id)->latest()->first();
        $response->assertRedirect('notice/address/list');
        $response->assertSessionHas('verify_requested', true);

        Notification::assertSentTo($actual, VerifyRequestContactsNotification::class);
        Notification::assertSentTo($this->actingUser, VerifyRequestContactsNotification::class);

        $assertion($this, $actual);
        Carbon::setTestNow();
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_test_success(): array
    {
        return [
            '初回送信' => [
                'data' => function (SendVerificationTest $case) {
                    Carbon::setTestNow('2024-01-31 15:00');
                    return Contact::factory()->for($case->actingUser)->emailNotVerified()->create();
                },
                'assertion' => function (SendVerificationTest $case, Contact $actual) {
                    self::assertNull($case->myContact->send_verify_at);
                    self::assertSame(
                        Carbon::parse('2024-01-31 15:00')->toString(),
                        $actual->send_verify_at->toString()
                    );
                },
            ],
            '2回目以降' => [
                'data' => function (SendVerificationTest $case) {
                    Config::set('specs.send_contacts_verify_interval', 3);
                    Carbon::setTestNow('2024-01-31 15:36');
                    $lastSendAt = now()->subMinutes(3);
                    return Contact::factory()->for($case->actingUser)->emailVerifyRequested($lastSendAt)->create();
                },
                'assertion' => function (SendVerificationTest $case, Contact $actual) {
                    self::assertNotNull($case->myContact->send_verify_at);
                    self::assertSame(
                        Carbon::parse('2024-01-31 15:36')->toString(),
                        $actual->send_verify_at->toString()
                    );
                },
            ],
        ];
    }

    /**
     * @return void
     */
    public function test_queued_success(): void
    {
        $user = User::where('plan', PlanType::PERSONAL->value)->first();
        $contact = Contact::factory()->for($user)->emailNotVerified()->create();

        $this->actingAs($user);
        $response = $this->post(route('notice.address.verify.send', ['id' => $contact->id]));
        $response->assertRedirect('notice/address/list');

        $jobs = DB::table('jobs')->select('*')->where('queue', 'default')->get();
        $this->assertCount(2, $jobs);
    }

    /**
     * @param Closure $data
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_test_fail
     */
    public function test_fail(Closure $data, Closure $assertion): void
    {
        Notification::fake();
        $this->actingUser = User::where('plan', PlanType::PERSONAL->value)->first();
        $this->myContact = $data($this);

        $this->actingAs($this->actingUser);
        $response = $this->post(route('notice.address.verify.send', ['id' => $this->myContact->id]));
        $response->assertSessionMissing('verify_requested');

        Notification::assertNothingSent();

        $assertion($response, $this);
        Carbon::setTestNow();
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_test_fail(): array
    {
        return [
            '他ユーザーのデータを指定' => [
                'data' => function (SendVerificationTest $case) {
                    return Contact::factory()->for(User::factory())->emailNotVerified()->create();
                },
                'assertion' => function (TestResponse $response, SendVerificationTest $case) {
                    $response->assertNotFound();
                },
            ],
            '削除済み通知先を指定' => [
                'data' => function (SendVerificationTest $case) {
                    $contact = Contact::factory()->for($case->actingUser)->emailNotVerified()->create();
                    $contact->delete();
                    return $contact;
                },
                'assertion' => function (TestResponse $response, SendVerificationTest $case) {
                    $response->assertNotFound();
                },
            ],
            '送信可能インターバル未経過' => [
                'data' => function (SendVerificationTest $case) {
                    Config::set('specs.send_contacts_verify_interval', 3);
                    Carbon::setTestNow('2024-01-31 15:43');
                    $lastSendAt = now()->subMinutes(2);
                    return Contact::factory()->for($case->actingUser)->emailVerifyRequested($lastSendAt)->create();
                },
                'assertion' => function (TestResponse $response, SendVerificationTest $case) {
                    $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
                    $response->assertSeeText(sprintf(self::$errorMessage, 3));
                },
            ],
            '送信後に削除したデータが送信可能インターバル未経過' => [
                'data' => function (SendVerificationTest $case) {
                    Config::set('specs.send_contacts_verify_interval', 2);
                    $trash = Contact::factory()->for($case->actingUser)->emailVerifyRequested()->create();
                    $trash->delete();
                    return Contact::factory()->for($case->actingUser)->emailNotVerified()->create();
                },
                'assertion' => function (TestResponse $response, SendVerificationTest $case) {
                    $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
                    $response->assertSeeText(sprintf(self::$errorMessage, 2));
                },
            ],
            '承認済み' => [
                'data' => function (SendVerificationTest $case) {
                    return Contact::factory()->for($case->actingUser)->emailVerified(now()->subHour())->create();
                },
                'assertion' => function (TestResponse $response, SendVerificationTest $case) {
                    $interval = Config::get('specs.send_contacts_verify_interval');
                    $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
                    $response->assertSeeText(sprintf(self::$errorMessage, $interval));
                },
            ],
        ];
    }
}
