<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Return the only the valid parameters.
     *
     * @param Request $request
     * @param array $keys
     * @param $validator
     * @return array
     */
    protected function onlyValidParameters(Request $request, Validator $validator, $keys = [])
    {
        $valid = [];

        if ($validator->fails()) {

            $errorKeys = array_keys($validator->errors()->messages());

            foreach ($keys as $key) {
                if (in_array($key, $errorKeys, true)) {
                    continue;
                }
                $valid[$key] = $request->get($key);
            }

            return $valid;
        }

        return $request->only($keys);
    }
}
