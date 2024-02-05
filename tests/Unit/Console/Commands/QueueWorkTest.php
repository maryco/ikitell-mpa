<?php

namespace Tests\Unit\Console\Commands;

use App\Models\Entities\Device;
use App\Models\Entities\User;
use App\Notifications\DeviceResumedNotification;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Support\Facades\DB;
use JsonException;
use Mockery\MockInterface;
use Tests\TestCase;

class QueueWorkTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test failed_jobs.uuid add Laravel 8
     * @return void
     * @throws JsonException
     */
    public function test_failed_jobs(): void
    {
        $this->partialMock(MailChannel::class, function (MockInterface $mock) {
            $mock->shouldReceive('send')
                ->once()
                ->andThrow(new Exception('Something error'));
        });

        $user = User::where('plan', config('codes.subscription_types.basic'))->first();
        $device = Device::factory()
            ->for($user, 'ownerUser')
            ->create(['reported_at' => now()->timestamp]);
        $user->sendDeviceResumedNotification($device);

        $job = DB::table('jobs')->select('*')->get();
        self::assertCount(1, $job);

        // NOTE: jobs.payload is json
        // vendor/laravel/framework/src/Illuminate/Queue/Queue.php:105
        $actualPayload = json_decode($job->first()->payload, false, 512, JSON_THROW_ON_ERROR);
        self::assertSame('default', $job->first()->queue);
        self::assertNotNull($actualPayload->uuid);
        self::assertSame(DeviceResumedNotification::class, $actualPayload->displayName);

        $this->artisan('queue:work', ['--once' => 'default'])->assertSuccessful();

        $job = DB::table('jobs')->select('*')->get();
        self::assertCount(0, $job);

        // assert failed_jobs has uuid
        $failedJob = DB::table('failed_jobs')->select('*')->get();
        self::assertCount(1, $failedJob);
        $actualFailedPayload = json_decode($failedJob->first()->payload, false, 512, JSON_THROW_ON_ERROR);
        self::assertSame($actualPayload->uuid, $actualFailedPayload->uuid);
    }
}
