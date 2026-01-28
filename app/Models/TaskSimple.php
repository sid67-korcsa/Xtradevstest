<?php

namespace App\Persistence\Models;

use App\Persistence\Scopes\GetTaskFlagSimpleScope;
use App\Traits\ValidatableModel;
use Illuminate\Database\Eloquent\Model;
use App\Persistence\Models\TaskDescriptions;

/**
 * Class TaskSimple
 * @package App\Persistence\Models
 *
 * @property int id_task
 * @property string task_name
 * @property int task_priority
 * @property string task_term
 * @property string task_status
 * @property int id_task_description
 * @property int id_task_customer
 * @property enum task_flag
 * @property DateTime task_reminder
 * @property int id_task_responsible
 * @property int id_task_owner
 * @property string ai_generated_from_text
 * @property int id_task_type
 * @property int id_contact
 * @property string event_date
*/

class TaskSimple extends Model implements ValidatableModelInterface
{
    use ValidatableModel;

    protected $table = "tasks";

    protected $primaryKey = 'id_task';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_name',
        'task_priority',
        'task_term',
        'id_task_owner',
        'id_task_responsible',
        'task_status',
        'id_task_description',
        'id_task_customer',
        'task_flag',
        'task_reminder',
        'ai_generated_from_text',
        'id_task_type',
        'id_contact',
        'event_date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new GetTaskFlagSimpleScope);
    }

    public function getValidationRules(): array
    {
        return [
            'task_name' => 'required|string|min:3|max:255',
            'task_priority' => 'nullable|int|min:1',
            'task_term' => 'nullable|date_format:Y-m-d',
            'id_task_responsible' => 'int',
            'id_task_owner' => 'required|int',
            'task_status' => 'required|string|min:3|max:255',
            'id_task_description' => 'nullable|int',
            'task_flag' => 'in:S',
            'task_reminder' => 'nullable|date_format:Y-m-d H:i:s',
            'ai_generated_from_text' => 'nullable',
            'id_task_customer' => 'int|nullable',
            'id_task_type' => 'int|nullable',
            'id_contact' => 'int|nullable',
            'event_date' => 'string|nullable',
        ];
    }

    public function scopeSimpleTask($query, $id) {
        if(isset($id)) {
            return $query->where('id_task', '=', $id);
        }
    }

    public function description()
    {
        return $this->hasOne(TaskDescriptions::class, 'id_task_description', 'id_task_description');
    }

    public function customer() {
        return $this->hasOne(Customer::class, 'id_customer', 'id_task_customer');
    }

    public function owner() {
        return $this->hasOne(Employee::class, 'id_employee', 'id_task_owner');
    }

    public function responsible() {
        return $this->hasOne(Employee::class, 'id_employee', 'id_task_responsible');
    }

    public function getTaskDesctiptionAttribute()
    {
        return $this->description ? $this->description->task_description : '';
    }

    public function type() {
        return $this->hasOne(TaskType::class, 'id_task_type', 'id_task_type');
    }

}
