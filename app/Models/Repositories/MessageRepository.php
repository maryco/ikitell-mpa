<?php
namespace App\Models\Repositories;


use App\Models\Entities\ConcernMessage;
use ArrayObject;
use Illuminate\Support\Arr;

class MessageRepository implements MessageRepositoryInterface
{
    /**
     * @see config/alert.php
     * @var mixed
     */
    private mixed $templateConfig;

    /**
     * MessageRepository constructor.
     */
    public function __construct()
    {
        $this->templateConfig = config('alert.template');
    }

    public function makeModel($bindData = null)
    {
        return new ConcernMessage($bindData);
    }

    public function count()
    {
        // TODO: Implement count() method.
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id, int $userId): ?ConcernMessage
    {
        $template = Arr::get($this->templateConfig, $id);
        if (!$template) {
            return null;
        }

        $template['user_id'] = $userId;
        return $this->makeModel($template);
    }

    /**
     * @inheritDoc
     */
    public function getTemplate(): array
    {
        $templates = [];

        foreach ($this->templateConfig as $template) {
            $templates[] = new ArrayObject(
                $this->makeModel($template)->getBaseData(),
                ArrayObject::ARRAY_AS_PROPS
            );
        }

        return $templates;
    }
}

