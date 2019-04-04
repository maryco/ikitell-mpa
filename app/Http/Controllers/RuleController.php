<?php

namespace App\Http\Controllers;

use App\Http\Requests\RuleStoreRequest;
use App\Models\Repositories\MessageRepositoryInterface;
use App\Models\Repositories\RuleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class RuleController extends Controller
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepo;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepo;

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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getList()
    {
        $rules = $this->ruleRepo->getByUserId(Auth::id());

        return view('rule.list', compact('rules'));
    }

    /**
     * Show create/edit form.
     *
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showForm($id = null)
    {
        $rule = $this->ruleRepo->makeModel();

        if (Route::getCurrentRoute()->named('rule.edit')) {
            $rule = $this->ruleRepo->findByUserId(Auth::id(), $id);
            if (!$rule) {
                abort('404');
            }
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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(RuleStoreRequest $request)
    {
        $appInfoKey = 'saved';
        $targetId = Route::getCurrentRoute()->parameter('id', null);

        if (Route::getCurrentRoute()->named('rule.edit')) {
            $rule = $this->ruleRepo->findByUserId(Auth::id(), $targetId);

            if (!$rule) {
                abort('404');
            }

            $appInfoKey = 'edited';
        }

        $rule = $this->ruleRepo->store(
            array_merge(
                $request->onlyForStore(),
                ['id' => $targetId, 'user_id' => Auth::id()]
            )
        );

        return redirect(route('rule.edit', ['id' => $rule->id]))
            ->with($appInfoKey, true);
    }

    /**
     * Delete specific rule.
     *
     * @param $ruleId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete($ruleId)
    {
        $rule = $this->ruleRepo->findByUserId(Auth::id(), $ruleId);
        if (!$rule) {
            abort('404');
        }

        if (!$this->ruleRepo->delete($ruleId, Auth::id())) {
            abort('500');
        }

        return redirect(route('rule.list'))
            ->with('deleted', true);
    }
}
