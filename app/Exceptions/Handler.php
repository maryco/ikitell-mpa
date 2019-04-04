<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->ajax()) {
            return $this->responseAsJson($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * @param HttpExceptionInterface $e
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     *
     * @see \Illuminate\Foundation\Exceptions\Handler::renderHttpException
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {
        return response()->view('panels.error', [
            'errors' => new ViewErrorBag,
            'exception' => $e,
            'defaultMessage' => $this->getDefaultMessage($e),
            'hideFooter' => $this->hideFooter(),
        ], $e->getStatusCode(), $e->getHeaders());
    }

    /**
     * Return response as json.
     *
     * @param $request
     * @param Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    private function responseAsJson($request, Exception $e)
    {
        $status = 200;

        $data = [
            'message' => $e->getMessage() ?: $this->getDefaultMessage($e),
            'errors' => [],
        ];

        if ($e instanceof HttpResponseException) {
            $status = $e->getResponse()->setStatusCode();
        } elseif ($e instanceof AuthenticationException) {
            $status = 401;
        } elseif ($e instanceof ValidationException) {
            /**
             * 422 Unprocessable Entity
             * https://developer.mozilla.org/ja/docs/Web/HTTP/Status/422
             */
            $status = $e->status;
            $data['errors'] = $e->errors();
        } else {
            $status = 500;
        }

        return response()->json($data, $status);
    }

    /**
     * Return the footer show/hide state
     * by the current route name.
     *
     * @return bool
     */
    private function hideFooter()
    {
        $hideRouteNames = [
            'notice.address.verify.preview',
            'notice.message.preview',
        ];

        return in_array(Route::currentRouteName(), $hideRouteNames, true);
    }

    /**
     * Return the error message depends on the error status code,
     * from message lang file.
     *
     * @param HttpExceptionInterface $e
     * @param $defaultLangKey
     * @return string
     *
     * @see lang/{lpcale}/message.php
     */
    private function getDefaultMessage(
        HttpExceptionInterface $e,
        $defaultLangKey = 'message.error.whoops'
    ) {
        $errorLangKey = sprintf('message.error.%s', $e->getStatusCode());

        return Lang::has($errorLangKey)
            ? __($errorLangKey)
            : __($defaultLangKey);
    }
}
