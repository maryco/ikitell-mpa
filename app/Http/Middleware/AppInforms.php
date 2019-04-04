<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class AppInforms
{
    /**
     * The type of information.
     */
    const INFORM_TYPE_INFO = 'info';
    const INFORM_TYPE_NOTICE = 'notice';

    /**
     * The key-value pairs for the generate message targets.
     * NOTE: resent = \Illuminate\Foundation\Auth\VerifiesEmails::resend
     *
     * @var array
     */
    private $informParams = [
        'registered' => true,
        'verified' => true,
        'resent' => true,
        'saved' => true,
        'edited' => true,
        'deleted' => true,
        'verify_requested' => true,
        'deregistered' => true,
        'status' => [],
    ];

    /**
     * The Converted message bag.
     * FIXME: Create model class if need.
     *
     * @var array
     * ['type' => 'info/notice', 'text' => '']
     *
     */
    private $informs = [];

    /**
     * AppInforms constructor.
     */
    public function __construct()
    {
        // These variables are not convert.
        $this->informParams['status'] = [
          __('passwords.sent'),
          __('passwords.reset'),
        ];
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @see https://laravel.com/docs/5.7/middleware
     */
    public function handle($request, Closure $next)
    {
        /**
         * Convert app response parameters in session
         * to user readable message. And share it in globals.
         */
        $filtered = Arr::only(
            $request->session()->all(),
            array_keys($this->informParams)
        );

        $this->convertInfoMessageRecursive($filtered, $this->informParams);

        if (View::shared('errors') && View::shared('errors')->isNotEmpty()) {
            array_unshift($this->informs, [
                'type' => self::INFORM_TYPE_NOTICE,
                'text' => __('message.app.notice.has_error'),
            ]);
        }

        View::share('appInforms', $this->informs);

        return $next($request);
    }

    /**
     * Generate message from the data,
     * and set to the message bag.
     *
     * @param $data
     * @param $messages
     * @return void
     */
    private function convertInfoMessageRecursive($data, $messages)
    {
        foreach ($messages as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $idx => $nestedVal) {
                    $this->convertInfoMessageRecursive($data, [$key => $nestedVal]);
                }
                continue;
            }

            // FIXME: Use '===' !
            if (Arr::get($data, $key) == $val) {
                /**
                 * NOTE: Uggg. __() returns the "key" if not found defined value.
                 */
                $langKey = "message.app.$key";
                $langMsg = (__($langKey) !== $langKey) ? __($langKey) : $val;

                if (Str::contains($langMsg, "\\n")) {
                    foreach (explode("\\n", $langMsg) as $msg) {
                        $this->informs[] = [
                            'type' => self::INFORM_TYPE_INFO,
                            'text' => $msg,
                        ];
                    }
                } else {
                    $this->informs[] = [
                        'type' => self::INFORM_TYPE_INFO,
                        'text' => $langMsg,
                    ];
                }
            }
        }
    }
}
