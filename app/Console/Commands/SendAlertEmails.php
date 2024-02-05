<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\GetArgument;
use App\Models\Entities\Alert;
use App\Models\Repositories\AlertRepositoryInterface;
use App\Models\Repositories\ContactRepositoryInterface;
use App\Models\Repositories\UserRepositoryInterface;
use App\Notifications\AlertNotification;
use ArrayObject;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class SendAlertEmails extends Command
{
    use GetArgument;

    /**
     * The limit of select devices per process.
     */
    public const SELECT_LIMIT = 5;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:send {limit?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send alert notification from alerts data.';

    /**
     * @var AlertRepositoryInterface
     */
    protected AlertRepositoryInterface $alertRepo;

    /**
     * @var ContactRepositoryInterface
     */
    protected ContactRepositoryInterface $contactRepo;

    /**
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        AlertRepositoryInterface $alertRepo,
        ContactRepositoryInterface $contactRepo,
        UserRepositoryInterface $userRepo
    ) {
        parent::__construct();

        $this->alertRepo = $alertRepo;
        $this->contactRepo = $contactRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $alerts = $this->alertRepo->getActive($this->getArgumentInt('limit', self::SELECT_LIMIT));

        if (count($alerts) === 0) {
            Log::info('No notifiable alerts.');
        }

        foreach ($alerts as $alert) {
            $targets = $this->getShouldSendTarget($alert);

            foreach ($targets as $notifiable) {
                /**
                 * NOTE: If sets like below, Logging seems use
                 * latest Alert Model when Queue executed.
                 * So copy to array the Model of the current states.
                 *
                 * $notification->setAlert($alert);
                 */
                $alertCopy = new ArrayObject($alert->toArray(), ArrayObject::ARRAY_AS_PROPS);

                $notification = $alert->notification_payload;
                if ($notification instanceof AlertNotification) {
                    $notification->setAlert($alertCopy);
                }

                $notifiable->notify($notification);
            }

            // TODO: 送信先「0」の場合は強制終了(削除)

            $this->alertRepo->updateForNext($alert->id);
        }
    }

    /**
     * Return send mail target models(user or contact),
     * depends on alert status.
     *
     * NOTE:
     * When first alert, return only the device owner user
     * and assigned user.
     *
     * @param Alert $alert
     * @return array<int, mixed>
     */
    private function getShouldSendTarget(Alert $alert): array
    {
        $targets = [];

        if ($alert->notify_count > $alert->max_notify_count) {
            Log::warning('This alert already notify max.', ['alertId' => $alert->id]);
            return $targets;
        }

        $isValidUser = true;
        if ($alert->notify_count >= 0) {
            $filtered = Arr::where($alert->getSendTarget(), static function ($item, $idx) {
                return in_array((int)$item['type'], [Alert::TARGET_TYPE_OWNER, Alert::TARGET_TYPE_USER], true);
            });

            foreach ($filtered as $target) {
                $user = $this->userRepo->findByEmail($target['email']);
                if (!$user) {
                    Log::warning('The alert includes invalid user.', ['alertId' => $alert->id]);
                    $isValidUser = false;
                    break;
                }

                $targets[] = $user;
            }
        }

        if (!$isValidUser) {
            return [];
        }

        if ($alert->notify_count >= 1) {
            $filtered = Arr::where($alert->getSendTarget(), static function ($item, $idx) {
                return (int)$item['type'] === Alert::TARGET_TYPE_CONTACTS;
            });

            foreach ($filtered as $target) {
                $contact = $this->contactRepo->findByEmail($target['email']);
                if (!$contact || !$contact->isVerified()) {
                    Log::warning(
                        'The alert has deleted (or not verified) contacts.',
                        ['alertId' => $alert->id, 'contactId' => $contact?->id]
                    );
                    continue;
                }

                $targets[] = $contact;
            }
        }

        return $targets;
    }
}
