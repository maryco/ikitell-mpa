<?php

namespace App\Enums\User;

enum PlanType: int
{
    /*
    |--------------------------------------------------------------------------
    | The Subscription types.
    |--------------------------------------------------------------------------
    |
    | 'users.plan'
    |
    | 0: 'Personal' for the personal use.
    | 1: 'Business' for the business use.
    | 3: 'Limited' for the report only user. (Register by business user)
    */
    case PERSONAL = 0;
    case BUSINESS = 1;
    case LIMITED = 2;

    /**
     * @return bool
     */
    public function isLimited(): bool
    {
        return $this->value === self::LIMITED->value;
    }

    /**
     * @return string
     */
    public function lowerName(): string
    {
        return strtolower($this->name);
    }
}
