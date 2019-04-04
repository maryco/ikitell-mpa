<?php

namespace App\Models\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class BaseModel extends Model
{
    protected $dates = [
        'created_at', 'updated_at'
    ];

    /**
     * The default date present format.
     *
     * @var string
     */
    protected $datePrintFormat = 'Y-m-d H:i';

    /**
     * Merge data to the fillable attribute
     *
     * @param $data
     */
    public function mergeData($data)
    {
        $fillables = $this->getFillable();
        
        foreach ($data as $key => $val) {
            if (in_array($key, $fillables, true)) {
                $this->$key = $val;
            }
        }
    }
    
    /**
     * Return formatted datetime column.
     * NOTE: Not support unix timestamp
     *
     * @param $column
     * @param null $format
     * @return string
     */
    public function getDate($column, $format = null)
    {
        if (empty($this->$column)) {
            return '';
        }

        if (!$this->isDateAttribute($column)) {
            return '';
        }
        
        $carbon = Carbon::parse($this->$column);
        
        return (is_null($format)) ? $carbon->format($this->datePrintFormat) : $carbon->format($format);
    }
    
    /**
     * Return 'checked' string for a form element, if value is 1.
     *
     * NOTE: old value is priority.
     * FIXME: Checkbox uncheck value is null, so... ?
     *
     * @param $column
     * @param null $old
     * @param string $checked
     * @return string
     */
    public function getCheckedOn($column, $old = null, $checked = 'checked')
    {
        if (!in_array($column, $this->getFillable(), true)) {
            return '';
        }
        
        if (!is_null($old)) {
            return intval($old) === 1 ? $checked : '';
        } elseif (!is_null($this->$column)) {
            return intval($this->$column) === 1 ? $checked : '';
        }
        
        return '';
    }
    
    /**
     * (Reverse getCheckedOn)
     *
     * @param $column
     * @param null $old
     * @param string $checked
     * @return string
     */
    public function getCheckedOff($column, $old = null, $checked = 'checked')
    {
        return  empty($this->getCheckedOn($column, $old, $checked)) ? $checked : '';
    }

    /**
     * Remove cached data by specific key.
     * NOTE: Currently, 'Cache' use 'Session'.
     *
     * @param $key
     */
    protected function removeCache($key)
    {
        Session::forget($key);
    }
}
