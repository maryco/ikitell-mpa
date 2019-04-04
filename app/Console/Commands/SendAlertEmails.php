<?php

namespace App\Console\Commands;

use App\Models\Entities\Alert;
use App\Models\Repositories\AlertRepositoryInterface;
use App\Models\Repositories\ContactRepositoryInterface;
use App\Models\Repositories\UserRepositoryInterface;
use App\Notifications\AlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class SendAlertEmails extends Command
{
    /**
     * The limit of select devices per process.
     */
    const SELECT_LIMIT = 5;

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
    protected $description = 'Send alert notification via database table alerts.';

    /**
     * @var AlertRepositoryInterface
     */
    protected $alertRepo;

    /**
     * @var ContactRepositoryInterface
     */
    protected $contactRepo;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepo;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $alerts = $this->alertRepo->getActive($this->argument('limit') ?? self::SELECT_LIMIT);

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
                $alertCopy = new \ArrayObject($alert->toArray(), \ArrayObject::ARRAY_AS_PROPS);

                $notification = $alert->notification_payload;
                if ($notification instanceof AlertNotification) {
                    $notification->setAlert($alertCopy);
                }

                $notifiable->notify($notification);
            }

            $this->alertRepo->updateForNext($alert->id);
        }

        return;
    }

    /**
     * Return send mail target models(user or contact),
     * depends on alert status.
     *
     * NOTE:
     * When first alert, return only the device owner user
     * and assigned user.
     *
     * @return array
     */
    private function getShouldSendTarget($alert)
    {
        $targets = [];

        if ($alert->notify_count > $alert->max_notify_count) {
            Log::warning('This alert already notify max. [%id]', ['%id' => $alert->id]);
            return $targets;
        }

        if ($alert->notify_count >= 0) {
            $filtered = Arr::where($alert->getSendTarget(), function ($item, $idx) {
                return in_array(intval($item['type']), [Alert::TARGET_TYPE_OWNER, Alert::TARGET_TYPE_USER], true);
            });

            foreach ($filtered as $target) {
                $user = $this->userRepo->findByEmail($target['email']);
                if (!$user) {
                    Log::warning(
                        'The alert includes a not found user. [%alert]',
                        ['%alert' => $alert->id]
                    );
                    continue;
                }

                $targets[] = $user;
            }
        }

        if ($alert->notify_count >= 1) {
            $filtered = Arr::where($alert->getSendTarget(), function ($item, $idx) {
                return intval($item['type']) === Alert::TARGET_TYPE_CONTACTS;
            });

            foreach ($filtered as $target) {
                $contact = $this->contactRepo->findByEmail($target['email']);
                if (!$contact || !$contact->isVerified()) {
                    Log::warning(
                        'The alert has a deleted (or not verified) contacts. [%alert]',
                        ['%alert' => $alert->id]
                    );
                    continue;
                }

                $targets[] = $contact;
            }
        }

        return $targets;
    }
}
