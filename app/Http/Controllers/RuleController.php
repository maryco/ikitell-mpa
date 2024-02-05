<?php

namespace App\Http\Controllers;

use App\Http\Requests\RuleStoreRequest;
use App\Models\Repositories\MessageRepositoryInterface;
use App\Models\Repositories\RuleRepositoryInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RuleController extends Controller
{
    /**
     * @var RuleRepositoryInterface
     */
    private RuleRepositoryInterface $ruleRepo;

    /**
     * @var MessageRepositoryInterface
     */
    private MessageRepositoryInterface $messageRepo;

    /**
     * RuleController constructor.
     *
     * @param RuleRepositoryInterface $ruleRepo
     * @param MessageRepositoryInterface $messageRepo
     */
    public function __construct(
        RuleRepositoryInterface $ruleRepo,
        MessageRepositoryInterface $messageRepo
    ) {
        $this->ruleRepo = $ruleRepo;
        $this->messageRepo = $messageRepo;
    }

    /**
     * Get rule list
     *
     * @return Factory|View
     */
    public function getList(): Factory|View
    {
        $rules = $this->ruleRepo->getByUserId(Auth::id());

        return view('rule.list', compact('rules'));
    }

    /**
     * Show create/edit form.
     *
     * @param null $id
     * @return Factory|View
     */
    public function showForm($id = null): Factory|View
    {
        $rule = $this->ruleRepo->makeModel();

        if (Route::getCurrentRoute()?->named('rule.edit')) {
            $rule = $this->ruleRepo->findByUserId(Auth::id(), $id ?? abort(Response::HTTP_NOT_FOUND));
            abort_if(!$rule, Response::HTTP_NOT_FOUND);
        } else {
            $rule->fillDefault();
        }

        $ruleForm = (new RuleStoreRequest())
            ->setMailMessages($this->messageRepo->getTemplate());

        return view('rule.form', compact('rule', 'ruleForm'));
    }

    /**
     * Store the rule.
     *
     * @param RuleStoreRequest $request
     * @return RedirectResponse|Redirector
     */
    public function store(RuleStoreRequest $request): Redirector|RedirectResponse
    {
        $appInfoKey = self::ACTION_RESULT_KEY_SAVE;
        $targetId = Route::getCurrentRoute()?->parameter('id');

        if (Route::getCurrentRoute()?->named('rule.edit')) {
            $rule = $this->ruleRepo->findByUserId(Auth::id(), $targetId);
            abort_if(!$rule,Response::HTTP_NOT_FOUND);

            $appInfoKey = self::ACTION_RESULT_KEY_EDIT;
        }

        $rule = $this->ruleRepo->store(
            array_merge(
                $request->onlyForStore(),
                ['id' => $targetId, 'user_id' => Auth::id()]
            )
        );

        return redirect(route('rule.edit', ['id' => $rule->id]))->with($appInfoKey, true);
    }

    /**
     * Delete specific rule.
     *
     * @param $ruleId
     * @return RedirectResponse|Redirector
     */
    public function delete($ruleId): Redirector|RedirectResponse
    {
        $rule = $this->ruleRepo->findByUserId(Auth::id(), $ruleId);
        if (!$rule) {
            abort('404');
        }

        if (!$this->ruleRepo->delete($ruleId, Auth::id())) {
            abort('500');
        }

        return redirect(route('rule.list'))->with('deleted', true);
    }
}
