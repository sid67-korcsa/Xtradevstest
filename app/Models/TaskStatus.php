<?php

namespace App\Persistence\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ValidatableModel;
use Illuminate\Database\Eloquent\Model;
/**
 * Class TaskStatus
 * @package App\Persistence\Models
 *
 * @property int id_task_status
 * @property string task_status_name
 * @property string task_status_comment
 * @property string task_status_color
 * @property boolean is_milestone
 * @property int id_task_category
 */
class TaskStatus extends Model implements ValidatableModelInterface
{
    use ValidatableModel;

    protected $table = "task_statuses";

    protected $primaryKey = 'id_task_status';

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [ 'task_status_name', 'task_status_comment', 'task_status_color', 'is_milestone', 'id_task_category' ];

    /**
     * Validation rules
     *
     * @return string[]
     */
    public function getValidationRules(): array
    {
        return [
            'task_status_name' => 'required|string|max:20',
            'task_status_comment' => 'nullable|string',
            'task_status_color' => 'string|max:191',
            'is_milestone' => 'boolean',
            'id_task_category' => 'required'
        ];
    }

    public function tasks() {
        return $this->hasMany(TaskComplex::class, 'id_task_status', 'id_task_status');
    }

    public function category() {
        return $this->hasOne(TaskCategories::class, 'id_task_category', 'id_task_category');
    }

    public function responsibles() {
        return $this->belongsToMany(Employee::class, 'task_status_responsibles', 'id_task_status', 'id_employee');
    }
}
