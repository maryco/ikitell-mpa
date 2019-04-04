<?php
namespace App\Models\Repositories;

use App\Models\Entities\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RuleRepository implements RuleRepositoryInterface
{
    public function makeModel($bindData = null)
    {
        $model = new Rule();
        if ($bindData) {
            $model->mergeData($bindData);
        }

        return $model;
    }

    public function count()
    {
        return Auth::guest() ? 0 : Rule::userId(Auth::id())->count();
    }

    /**
     * @see \App\Models\Repositories\RuleRepositoryInterface::findByUserId
     */
    public function findByUserId($userId, $ruleId)
    {
        return Rule::userId($userId)
            ->id($ruleId)
            ->first();
    }

    /**
     * @see \App\Models\Repositories\RuleRepositoryInterface::getByUserId
     */
    public function getByUserId($userId, $withDeviece = true)
    {
        $query = Rule::userId($userId);

        if ($withDeviece) {
            $query->with('device');
        }

        // FIXME:
        $query->orderByDesc('updated_at');

        return $query->get();
    }

    /**
     * @see \App\Models\Repositories\RuleRepositoryInterface::store
     * @throws \Throwable
     */
    public function store($data)
    {
        // TODO: Implement store() method.
        return DB::transaction(function () use ($data) {
            if (Arr::get($data, 'id', null)) {
                $rule = $this->findByUserId($data['user_id'], $data['id']);
            } else {
                $rule = $this->makeModel();
            }

            $rule->mergeData($data);

            Log::debug('Merged Model: []', ['' => $rule]);

            $rule->save();

            return $rule;
        });
    }

    /**
     * @see \App\Models\Repositories\RuleRepositoryInterface::delete
     */
    public function delete($ruleId, $userId)
    {
        return DB::transaction(function () use ($ruleId, $userId) {
            $rule = Rule::id($ruleId)
                ->userId($userId)
                ->lockForUpdate()
                ->first();

            if (!$rule) {
                Log::error(
                    'Not found target rule [%id] [%user]',
                    ['%id' => $ruleId, '%user' => $userId]
                );
                return false;
            }

            if (count($rule->device) !== 0) {
                Log::error('This rule has using on some devices. [%ruleId]', ['%ruleId' => $ruleId]);
                return false;
            }

            return $rule->delete();
        });
    }
}
