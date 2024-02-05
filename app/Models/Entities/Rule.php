<?php

namespace App\Models\Entities;

use Database\Factories\RuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $time_limits
 * @property int $notify_times
 */
class Rule extends BaseModel
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'time_limits',
        'notify_times',
        'message_id',
        'embedded_message'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Device>
     */
    public function device(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * @return RuleFactory
     */
    protected static function newFactory(): RuleFactory
    {
        return RuleFactory::new();
    }

    /**
     * @param Builder $query
     * @param int $userId
     * @return void
     */
    public function scopeUserId(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * @param Builder $query
     * @param int $id
     * @return void
     */
    public function scopeId(Builder $query, $id): void
    {
        $query->where('id', $id);
    }

    /**
     * Fill default values.
     *
     * @return static
     */
    public function fillDefault(): static
    {
        $this->time_limits = config('specs.time_limit_min');
        $this->notify_times = config('specs.send_notice_max.basic');

        return $this;
    }

    /**
     * Get time_limits as a days
     *
     * @return int
     */
    public function getTimeLimitsDays(): int
    {
        return $this->time_limits > 0  ? (int)($this->time_limits / 24) : 0;
    }
}
