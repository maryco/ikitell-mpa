<?php
namespace App\Models\Repositories;


use App\Models\Entities\ConcernMessage;
use Illuminate\Support\Arr;

class MessageRepository implements MessageRepositoryInterface
{
    /**
     * @see config/alert.php
     * @var array
     */
    private $templateConfig;

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
     * @see \App\Models\Repositories\MessageRepositoryInterface::findById
     */
    public function findById($id, $userId)
    {
        $template = Arr::get($this->templateConfig, $id, null);
        $template['user_id'] = $userId;

        return ($template) ? $this->makeModel($template) : null;
    }

    /**
     * @see \App\Models\Repositories\MessageRepositoryInterface::getTemplate
     */
    public function getTemplate()
    {
        $templates = [];

        foreach ($this->templateConfig as $template) {
            $templates[] = new \ArrayObject(
                $this->makeModel($template)->getBaseData(),
                \ArrayObject::ARRAY_AS_PROPS
            );
        }

        return $templates;
    }
}

