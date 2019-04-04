<?php

namespace App\Rules;

use App\Models\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Validation\Rule;

class MaxStored implements Rule
{
    /**
     * @var
     */
    private $repository;

    /**
     * @var int
     */
    private $max;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(BaseRepositoryInterface $repository, $max)
    {
        $this->repository = $repository;
        $this->max = $max;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->max > $this->repository->count();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.max.store', ['max' => $this->max]);
    }
}
