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
