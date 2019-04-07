<?php
if (! function_exists('boolstr')) {
    /**
     * Get the bool value as a string.
     *
     * Its return 'true' if the value strict true, others 'false'
     */
    function boolstr($val = null)
    {
        return $val === true ? 'true' : 'false';
    }
}

if (! function_exists('response_json_redirection')) {
    /**
     * Return json response of redirect order.
     *
     * @param string $message
     * @param string $url
     */
    function response_json_redirection($message, $url)
    {
        response()->json(
            [
                'message' => $message,
                'location' => $url,
            ],
            303
        );
    }
}

if (! function_exists('is_seems_ie')) {
    /**
     * Whether is access by the IE.
     * NOTE: It's check User-Agent in the request header.
     *
     * @return bool
     */
    function is_seems_ie()
    {
        $ua = request()->header('User-Agent');
        if (!$ua) {
            return false;
        }

        /**
         * IE11 : "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko"
         * @see https://garafu.blogspot.com/2015/01/ie-useragent-2.html
         */
        return (preg_match('/Trident/', $ua) === 1);
    }
}
