<?php

namespace App\Http\Controllers;

use App\Http\Requests\RuleStoreRequest;
use App\Models\Repositories\MessageRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NoticeMessageController extends Controller
{
    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepo;

    /**
     * NoticeMessageController constructor.
     *
     * @param MessageRepositoryInterface $messageRepo
     */
    public function __construct(MessageRepositoryInterface $messageRepo)
    {
        $this->messageRepo = $messageRepo;
    }

    /**
     * Render the specified mail body for preview.
     *
     * @param Request $request
     * @return mixed
     * @throws \HttpException
     */
    public function preview(Request $request)
    {
        $validParams = $this->getValidPreviewParameters($request);
        if (!array_key_exists('rule_message_id', $validParams)) {
            abort(500);
        }

        $message = $this->messageRepo->findById($validParams['rule_message_id'], Auth::id());

        if (!$message) {
            abort(404);
        }

        if (!$message->buildContentMock()) {
            abort(404);
        }

        return $message->mergeContent($validParams)->renderAsMarkDown();
    }

    /**
     * Validate request, and return only valid parameters.
     *
     * @param Request $request
     * @return array
     */
    private function getValidPreviewParameters(Request $request)
    {
        $parameters = [
            'rule_time_limits',
            'rule_notify_times',
            'rule_message_id',
            'rule_embedded_message',
        ];

        $templates = $this->messageRepo->getTemplate();

        $validator = Validator::make(
            $request->only($parameters),
            RuleStoreRequest::rulesPreviewMail(data_get($templates, '*.id'))
        );

        return $this->onlyValidParameters($request, $validator, $parameters);
    }
}
