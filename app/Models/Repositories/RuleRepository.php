<?php
namespace App\Models\Repositories;

use App\Models\Entities\Rule;
use Illuminate\Database\Eloquent\Collection;
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
     * @inheritDoc
     */
    public function findByUserId(int $userId, int $ruleId): ?Rule
    {
        return Rule::userId($userId)
            ->id($ruleId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function getByUserId(int $userId, bool $withDevice = true): Collection
    {
        $query = Rule::userId($userId);

        if ($withDevice) {
            $query->with('device');
        }

        // FIXME:
        $query->orderByDesc('updated_at');

        return $query->get();
    }

    /**
     * @inheritDoc
     */
    public function store($data): Rule
    {
        return DB::transaction(function () use ($data) {
            if ($id = Arr::get($data, 'id')) {
                $rule = $this->findByUserId($data['user_id'], $id);
            } else {
                $rule = $this->makeModel();
            }

            $rule->mergeData($data);
            $rule->save();

            return $rule;
        });
    }

    /**
     * @inheritDoc
     */
    public function delete($ruleId, $userId): bool
    {
        return DB::transaction(function () use ($ruleId, $userId) {
            $rule = Rule::id($ruleId)
                ->userId($userId)
                ->lockForUpdate()
                ->first();

            if (!$rule) {
                Log::error('Not found target rule', ['ruleId' => $ruleId, 'userId' => $userId]);
                return false;
            }

            if (count($rule->device) !== 0) {
                Log::error('This rule has using on some devices.', ['ruleId' => $ruleId]);
                return false;
            }

            return (bool) $rule->delete();
        });
    }
}
