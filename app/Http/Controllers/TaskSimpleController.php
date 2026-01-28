<?php

namespace App\Http\Controllers\Task;

use Amp\Parallel\Worker\Task;
use App\Http\Components\Ajax\AjaxResponse;
use App\Http\Components\FormHelper\FormButtonFieldHelper;
use App\Http\Components\FormHelper\FormCustomHTMLFieldHelper;
use App\Http\Components\FormHelper\FormChosenSelectFieldHelper;
use App\Http\Components\FormHelper\FormFieldHelper;
use App\Http\Components\FormHelper\FormSelectFieldHelper;
use App\Http\Components\FormHelper\FormTextareaFieldHelper;
use App\Http\Components\ListHelper\ListFieldHelperInterface;
use App\Http\Components\ToolbarLink\ToolbarDropdownLink;
use App\Http\Controllers\BREADController;
use App\Persistence\Models\Holiday;
use App\Persistence\Models\TaskType;
use App\Persistence\Scopes\GetTaskFlagSimpleResolvedScope;
use App\Persistence\Scopes\GetTaskFlagSimpleScope;
use App\Persistence\Scopes\SelectedYearScope;
use Carbon\CarbonInterface;
use Carbon\Traits\Date;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
//detect mobile client library
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use App\Http\Components\FormHelper\FormCheckboxFieldHelper;
use App\Http\Components\FormHelper\FormDropDownFieldHelper;
use App\Http\Components\FormHelper\FormHelper;
use App\Http\Components\FormHelper\FormInputFieldHelper;
use App\Http\Components\ListHelper\ListFieldHelper;
use App\Http\Components\ListHelper\ListHelper;
use App\Http\Components\Modal\Modal;
use App\Http\Components\Modal\RowActionModal;
use App\Http\Components\ToolbarLink\Link;
use App\Http\Components\ToolbarLink\ToolbarLinks;
//Models
use App\Persistence\Models\TaskSimple;
use App\Persistence\Models\TaskDescriptions;
use App\Persistence\Models\Role;

use App\Traits\HasGetRequestFunction;

use DB;

class TaskSimpleController extends BREADController
{
    use HasGetRequestFunction;

    protected $modal;
    protected $agent;

    public function __construct()
    {
        $this->modelClass = TaskSimple::class;
        $this->agent = new Agent();
        app('Presenter')->addJs('task.js');
        app('Presenter')->addCSS('task.css');
        app('Presenter')->addJs('datatables.js');
    }

    protected function buildFormHelper($model)
    {
        if(Auth::user()->id_employee !== null) {
            $userId = Auth::user()->id_employee;
            $userName = Auth::user()->name;
            $taskStatus = "NEW";
            $taskStatusFlag = "S";
        }

        return FormHelper::to('tasks', $model, [
            //FormInputFieldHelper::toHidden('hidden_warning', '')->setColClass('d-none'),
            ($this->agent->isMobile()) ? FormInputFieldHelper::toText('task_name', __('Feladat megnevezése'))->setElementId('task_name_mobile')->setRequired()->setColClass('col-10') : FormInputFieldHelper::toText('task_name', __('Feladat megnevezése'))->setClass("task_name_custom")->setRequired(),
            FormCustomHTMLFieldHelper::to("hidden_warning", "<p style='display: none;' id='hidden_warning_text'></p>"),
            //FormButtonFieldHelper::toButton('', 'add_simple_task', 'save_button')->setIconClass('fas fa-save fa-lg'),
            FormButtonFieldHelper::toButton('', 'add_simple_task', 'save_button')->setIconClass('custom_save_button fas fa-plus-circle btn-custom'),
            //FormButtonFieldHelper::toButton('', 'add_simple_task', 'save_button')->setIconClass('fas fa-plus-circle btn btn-sm btn-primary'),
            FormInputFieldHelper::toHidden('task_responsible', '')->setValue($userName)->setElementId("task_responsible")->setClass("hidden_fields")->addClass('d-none')->setColClass('d-none'),
            FormInputFieldHelper::toHidden('task_responsible_id', '')->setValue($userId)->setElementId("task_responsible_id")->setClass("hidden_fields")->addClass('d-none')->setColClass('d-none'),
            FormInputFieldHelper::toHidden('task_status', '')->setValue($taskStatus)->setElementId("task_status")->setClass("hidden_fields")->addClass('d-none')->setColClass('d-none'),
            FormInputFieldHelper::toHidden('task_flag', '')->setValue($taskStatusFlag)->setElementId("task_flag")->setClass("hidden_fields")->addClass('d-none')->setColClass('d-none')
        ]);
    }

    protected function buildListHelper()
    {
        return ListHelper::to('tasks', [
            /*($this->agent->isMobile()) ? ListFieldHelper::to('task_name', __('Feladat megnevezése'))->setWidth('110px')->setOrderable(false) : ListFieldHelper::to('task_name', __('Feladat megnevezése'))->setOrderable(false),*/
            ($this->agent->isMobile()) ? ListFieldHelper::to('task_name', __('Feladat megnevezése'))->setWidth('110px')->setType('custom')
                ->setCustomCallback(function ($model) {
                    $explodedTaskName = explode(' ', $model->task_name);

                    $maxCharacterLengthPerRow = 22;

                    $characterLengthCount = 0;

                    foreach ($explodedTaskName as $key => &$_taskNamePart) {
                        $characterLengthCount += strlen($_taskNamePart);
                        if ($characterLengthCount > $maxCharacterLengthPerRow) {
                            $characterLengthCount = strlen($_taskNamePart);
                            $_taskNamePart = '<br>' . $_taskNamePart;
                        }
                    }

                    $implodedTaskName = implode(' ', $explodedTaskName);

                    return $implodedTaskName;
                }) : ListFieldHelper::to('task_name', __('Feladat megnevezése')),
        ])
            ->setTitle(__('Elintézendők'))
            ->addRowActions(function ($model) {
                $actionsHTML = '';

                if (Auth::user()->can('update_task_simple')) {
                    $actionsHTML .= Link::to(route('updateSimpleTaskDetail', $model->getKey()), '<i class="fas fa-pencil-alt"></i>')
                        ->setClass('btn btn-primary-task btn-sm mr-1 custom-edit')
                        ->render();
                }

                return FormCustomHTMLFieldHelper::to('action', $actionsHTML)->renderTag();
            })
            ->addCheckboxes()
            ->setTemplate('task.simpleTask');

    }

    protected function buildListClosedHelper()
    {
        return ListHelper::to('tasks', [
            ListFieldHelper::to('checked-icons', __(''))
                ->setType('custom')
                ->setSearchable(false)
                ->setOrderable(false)
                ->setCustomCallback(function ($model) {
                   return '<i class="fas fa-check-square fa-2x" style="color: rgb(113, 116, 141);"></i>';
                }),
            (
                $this->agent->isMobile()) ?
                    ListFieldHelper::to('task_name', __('Feladat megnevezése'))
                        ->setWidth('110px')
                :
                    ListFieldHelper::to('task_name', __('Feladat megnevezése')),
        ])
            ->setTitle(__('Lezárt feladatok'))
            ->addRowActions(function ($model) {
                return FormDropDownFieldHelper::to('action')
                    ->addActionLinkIfCan('view-simple-task', route('showTaskSimple', $model->getKey()), '<i class="fas fa-info"></i> ')
                    ->renderTag();
            })
            /*->addRowActions(function ($model) {
                $actionsHTML = '';

                if (Auth::user()->can('update_task_simple')) {
                    $actionsHTML .= Link::to(route('updateSimpleTaskClosed', $model->getKey()), '<i class="fas fa-pencil-alt"></i>')
                        ->setClass('btn btn-primary-task btn-sm mr-1 custom-edit')
                        ->render();
                }

                return FormCustomHTMLFieldHelper::to('action', $actionsHTML)->renderTag();
            })*/
            ->setTemplate('task.simpleTask');
    }

    public function show($id) {
        $redirected = request()->session()->get('redirected', false);
        request()->session()->forget('redirected');
        if ($redirected) {
            $model = TaskSimple::query()->withoutGlobalScope(GetTaskFlagSimpleScope::class)->where('id_task', '=', $id)->first();
        } else {
            $model = TaskSimple::query()->withoutGlobalScope(GetTaskFlagSimpleScope::class)->withGlobalScope('resolved', new GetTaskFlagSimpleResolvedScope())->where('id_task', '=', $id)->first();
        }
        $form = $this->buildShowFormHelper($model);
        return $form->render();
    }

    protected function buildShowFormHelper($model) {
        if(isset($model->id_task_description)) {
            $desc = TaskDescriptions::select('task_description')->where('id_task_description', '=', $model->id_task_description)->get()->toArray()[0]['task_description'];

            if(!empty($desc)) {
                $taskDescription = $desc;
            } else {
                $taskDescription = "";
            }
        } else {
            $taskDescription = "";
        }

        return FormHelper::to('tasks', $model, [
            ($this->agent->isMobile()) ?
                FormInputFieldHelper::toText('task_name', __('Feladat megnevezése'))
                    ->setElementId('task_name_mobile')
                    ->setColClass('col-10')
                    ->setDisabled()
                :
                FormInputFieldHelper::toText('task_name', __('Feladat megnevezése'))
                    ->setClass("task_name_custom")
                    ->setDisabled(),
            ($model !== null) ?
                FormCustomHTMLFieldHelper::to('task_priority', '
                    <div class="form-group col-12 pl-lg-4 pl-sm-0">
                        <span id="created-at-date"><strong>Bejegyzés kelte:</strong> '.$model->created_at.'</span>
                        <br>
                        <br>
                        <label for="task_priority">Prioritás '.(isset($model->task_priority) ? "(<strong><span class='task-term-span'>".$model->task_priority."</span></strong>)" : "(<strong><span class='task-term-span'></span></strong>)").'</label>
                    </div>
                ')
            :
                FormCustomHTMLFieldHelper::to("hidden_temp_01", "<span style='display: none;'></span>"),
            ($model !== null) ?
                FormCustomHTMLFieldHelper::to('task_term', '
                    <div class="form-group col-12 pl-lg-4 pl-sm-0">
                        <label for="task_term">Határidő '.(isset($model->task_term) ? "(<strong><span class='task-term-span'>".$model->task_term."</span></strong>)" : "(<strong><span class='task-term-span'></span></strong>)").'</label>
                    </div>
                ')
            :
                FormCustomHTMLFieldHelper::to("hidden_temp_02", "<span style='display: none;'></span>"),
            ($model !== null) ?
                FormCustomHTMLFieldHelper::to('task_reminder', '
                    <div class="form-group col-12 pl-lg-4 pl-sm-0">
                        <label id="task-reminder-label" for="task_reminder">Emlékeztető '.(isset($model->task_reminder) ? "(<strong><span class='task-reminder-span'>".$model->task_reminder."</span></strong>)" : "(<strong><span class='task-reminder-span'></span></strong>)").'</label>
                    </div>
                ')
                :
                    FormCustomHTMLFieldHelper::to("hidden_temp_03", "<span style='display: none;'></span>"),
            ($model !== null) ?
                ($this->agent->isMobile() ?
                    FormTextareaFieldHelper::toTextarea('task_description', __('Jegyzet írása'))
                        ->setValue($taskDescription)
                        ->setRows(3)
                        ->setColClass('col-11')
                        ->setDisabled()
                :
                    FormTextareaFieldHelper::toTextarea('task_description', __('Jegyzet írása'))
                        ->setValue($taskDescription)
                        ->setRows(3)
                        ->setColClass('col-lg-6 col-sm-12')
                        ->setDisabled())

            :
                FormCustomHTMLFieldHelper::to("hidden_temp_04", "<span style='display: none;'></span>"),
            ($model !== null) ?
                ($model->ai_generated_from_text) ?
                    FormInputFieldHelper::toTextarea('ai_generated_from_text', __('Eredeti szöveg'))
                        ->setColClass('col-lg-6 col-sm-12')
                        ->setRows(3)
                        ->setDisabled(true)
                :
                    FormInputFieldHelper::toHidden('ai_generated_from_text', '')
                        ->addClass('d-none')
                        ->setColClass('d-none')
            :
                FormInputFieldHelper::toHidden('ai_generated_from_text', '')
                    ->addClass('d-none')
                    ->setColClass('d-none'),

            FormCustomHTMLFieldHelper::to("hidden_warning", "<p style='display: none;' id='hidden_warning_text'></p>"),
        ])
            ->setWithoutSubmit(true);
    }

    protected function buildFormDetailHelper($model)
    {
        if(Auth::user()->id_employee !== null) {
            $userId = Auth::user()->id_employee;
            $userName = Auth::user()->name;
            $taskStatus = "NEW";
            $taskStatusFlag = "S";
        }

        if(isset($model->task_term)) {
            $taskTermDate = $model->task_term;
            $taskTermDateExplode = explode("-", $taskTermDate);
            if(is_array($taskTermDateExplode) && !empty($taskTermDateExplode)) {
                array_push($taskTermDateExplode, "09");
                array_push($taskTermDateExplode, "00");
                array_push($taskTermDateExplode, "00");
            }

            $taskTermDateCarbon = Carbon::createFromDate($taskTermDateExplode[0], $taskTermDateExplode[1], $taskTermDateExplode[2]);
            //$taskTermDateCarbon = Carbon::createFromFormat("Y-m-d H:i:s", $taskTermDateExplode[0]."-".$taskTermDateExplode[1]."-".$taskTermDateExplode[2]." ".$taskTermDateExplode[3].":".$taskTermDateExplode[4].":".$taskTermDateExplode[5]);

            $taskTermDateCarbon = Carbon::createFromDate($taskTermDateExplode[0], $taskTermDateExplode[1], $taskTermDateExplode[2]);
            $one = $taskTermDateCarbon->subDays(1)->toDateString()." 09:00:00";
            $taskTermDateCarbon = Carbon::createFromDate($taskTermDateExplode[0], $taskTermDateExplode[1], $taskTermDateExplode[2]);
            $two = $taskTermDateCarbon->subDays(2)->toDateString()." 09:00:00";
            $taskTermDateCarbon = Carbon::createFromDate($taskTermDateExplode[0], $taskTermDateExplode[1], $taskTermDateExplode[2]);
            $three = $taskTermDateCarbon->subDays(3)->toDateString()." 09:00:00";
        }

        if( TaskSimple::query()
            ->pluck('id_task', 'id_task')
            ->toArray()
        ) {
            $taskSimpleIdMax = max(TaskSimple::query()
                    ->pluck('id_task', 'id_task')
                    ->toArray())+1;
        } else {
            $taskSimpleIdMax = 1;
        }

        if(isset($model->id_task_description)) {
            $desc = TaskDescriptions::select('task_description')->where('id_task_description', '=', $model->id_task_description)->get()->toArray()[0]['task_description'];

            if(!empty($desc)) {
                $taskDescription = $desc;
            } else {
                $taskDescription = "";
            }
        } else {
            $taskDescription = "";
        }

        if(isset($model->task_priority)) {
            $taskPriority = $model->task_priority;
        }

        $taskHeaders =
        [
            'priority' => isset($model->task_priority) ? $model->task_priority : null,
            'term' => isset($model->task_term) ? $model->task_term : null,
            'reminder' => isset($model->task_reminder) ? $model->task_reminder : null
        ];
        app('Presenter')->addJsVar('task_simples', $taskHeaders);

        $taskDetailDates = [
            'today' => Carbon::now()->toDateString(),
            'tomorrow' => Carbon::now()->addDay()->toDateString(),
            'three_day' => Carbon::now()->addDay(3)->toDateString(),
            'one_week' => Carbon::now()->addWeek(1)->toDateString(),
            'two_week' => Carbon::now()->addWeek(2)->toDateString(),
            'reminder_today' => Carbon::now()->toDateString().' 16:00:00',
            'reminder_tomorrow' => Carbon::now()->addDay()->toDateString().' 09:00:00',
            'reminder_one_day' => "",
            'reminder_two_day' => "",
            'reminder_three_day' => ""
        ];
        app('Presenter')->addJsVar('task_dates', $taskDetailDates);

        return FormHelper::to('tasks', $model, [
            ($this->agent->isMobile()) ? FormInputFieldHelper::toText('task_name', __('Feladat megnevezése'))->setElementId('task_name_mobile')->setRequired()->setColClass('col-10') : FormInputFieldHelper::toText('task_name', __('Feladat megnevezése'))->setClass("task_name_custom")->setRequired(),
            ($model !== null) ? FormCustomHTMLFieldHelper::to('task_priority', '
            <div class="form-group col-12 pl-lg-4 pl-sm-0">
            <span id="created-at-date"><strong>Bejegyzés kelte:</strong> '.$model->created_at.'</span>
            <br>
            <br>
            <label for="task_priority">Prioritás</label>
            <div class="row">
            <div class="input-group simple-task-center">
                <div class="task-priority-div">
                    <label>
                        <input type="radio" title="1" style="border: 0px" id="task-priority-1" class="form-control radio-task-priority task-priority-edit" name="task_priority" 
                            value="1" data-waschecked="true"><span style="width: 52px;">1</span>
                    </label>
                </div>
                <div class="task-priority-div">
                    <label>
                        <input type="radio" title="2" style="border: 0px" id="task-priority-2" class="form-control radio-task-priority task-priority-edit" name="task_priority" value="2"><span>2</span>
                    </label>
                </div>
                <div class="task-priority-div">
                    <label>
                        <input type="radio" title="3" style="border: 0px" id="task-priority-3" class="form-control radio-task-priority task-priority-edit" name="task_priority" value="3"><span>3</span>
                    </label>
                </div>
                <div class="task-priority-div">
                    <label>
                        <input type="radio" title="4" style="border: 0px" id="task-priority-4" class="form-control radio-task-priority task-priority-edit" name="task_priority" value="4"><span>4</span>
                    </label>
                </div>
                <div class="task-priority-div">
                    <label>
                        <input type="radio" title="5" style="border: 0px" id="task-priority-5" class="form-control radio-task-priority task-priority-edit" name="task_priority" value="5" checked><span>5</span>
                    </label>
                </div>
            </div>
            </div>
            </div>
                ') : FormCustomHTMLFieldHelper::to("hidden_temp_01", "<span style='display: none;'></span>"),
            ($model !== null) ? FormCustomHTMLFieldHelper::to('task_term', '
            <div class="form-group col-12 pl-lg-4 pl-sm-0">
            <label for="task_term">Határidő '.(isset($taskTermDate) ? "(<strong><span class='task-term-span'>".$taskTermDate."</span></strong>)" : "(<strong><span class='task-term-span'></span></strong>)").'</label>
            <div class="row">
            <div class="input-group simple-task-center">
                <div class="task-term-div">
                    <label>
                        <input type="radio" title="Nincs" style="border: 1px" id="task-term" class="form-control radio-task-priority task-priority-edit" name="task_term" data-waschecked="true" checked
                            value=""><span class="task-btn btn btn-primary">Nincs</span>
                    </label>
                </div>
                <div class="task-term-div">
                    <label>
                        <input type="radio" title="Mai nap" style="border: 1px" id="task-term-1" class="form-control radio-task-priority task-priority-edit" name="task_term"   
                            value="'.Carbon::now()->toDateString().'"><span class="task-btn btn btn-primary">Mai nap</span>
                    </label>
                </div>
                <div class="task-term-div">
                    <label>
                        <input type="radio" title="Holnap" style="border: 1px" id="task-term-2" class="form-control radio-task-priority task-priority-edit" name="task_term"
                            value="'.Carbon::now()->addDay()->toDateString().'"><span class="task-btn btn btn-primary">Holnap</span>
                    </label>
                </div>
                <div class="task-term-div">
                    <label>
                        <input type="radio" title="3 nap" style="border: 1px" id="task-term-3" class="form-control radio-task-priority task-priority-edit" name="task_term"
                            value="'.Carbon::now()->addDay(3)->toDateString().'"><span class="task-btn btn btn-primary">3 nap</span>
                    </label>
                </div>
                <div class="task-term-div">
                    <label>
                        <input type="radio" title="1 hét" style="border: 1px" id="task-term-4" class="form-control radio-task-priority task-priority-edit" name="task_term"
                            value="'.Carbon::now()->addWeek(1)->toDateString().'"><span class="task-btn btn btn-primary">1 hét</span>
                    </label>
                </div>
                <div class="task-term-div">
                    <label>
                        <input type="radio" title="2 hét" style="border: 1px;" id="task-term-5" class="form-control radio-task-priority task-priority-edit" name="task_term"
                            value="'.Carbon::now()->addWeek(2)->toDateString().'"><span class="task-btn btn btn-primary">2 hét</span>
                    </label>
                </div>
            </div>
            </div>
            </div>
                ') : FormCustomHTMLFieldHelper::to("hidden_temp_02", "<span style='display: none;'></span>"),
            ($model !== null) ? FormCustomHTMLFieldHelper::to('task_reminder', '
            <div class="form-group col-12 pl-lg-4 pl-sm-0">
            <label id="task-reminder-label" for="task_reminder">Emlékeztető '.(isset($model->task_reminder) ? "(<strong><span class='task-reminder-span'>".$model->task_reminder."</span></strong>)" : "(<strong><span class='task-reminder-span'></span></strong>)").'</label><!--<button class="btn btn-secondary btn-reminder-default-button" type="reset">Alaphelyzet</button>-->
            <div class="row">
            <div class="input-group simple-task-center">
                <div class="task-reminder-div" id="task-reminder-div-0">
                    <label>
                        <input type="radio" title="Nincs" style="border: 0px" id="task-reminder-0" class="form-control radio-task-priority task-priority-edit" name="task_reminder" data-waschecked="true" checked
                            value=""><span class="task-btn btn btn-primary">Nincs</span>
                    </label>
                </div>
                <div class="task-reminder-div" id="task-reminder-div-01">
                    <label>
                        <input type="radio" title="Ma 16ó" style="border: 0px" id="task-reminder-1" class="form-control radio-task-priority task-priority-edit" name="task_reminder" 
                            value="'.Carbon::now()->toDateString().' 16:00:00"><span class="task-btn btn btn-primary">Ma 16ó</span>
                    </label>
                </div>
                <div class="task-reminder-div" id="task-reminder-div-02">
                    <label>
                        <input type="radio" title="Holnap 9ó" style="border: 0px" id="task-reminder-2" class="form-control radio-task-priority task-priority-edit" name="task_reminder"
                            value="'.Carbon::now()->addDay()->toDateString().' 09:00:00"><span class="task-btn btn btn-primary">Holnap 9ó</span>
                    </label>
                </div>
                <div class="task-reminder-div" id="task-reminder-div-03">
                    <label>
                        <input type="radio" title="1 nappal korábban" style="border: 0px" id="task-reminder-3" class="form-control radio-task-priority task-priority-edit" name="task_reminder" 
                            value="'.(isset($one) ? $one : "").'"><span class="task-btn btn btn-primary ">1 nappal korábban</span>
                    </label>
                </div>
                <div class="task-reminder-div" id="task-reminder-div-04">
                    <label>
                        <input type="radio" title="2 nappal korábban" style="border: 0px" id="task-reminder-4" class="form-control radio-task-priority task-priority-edit" name="task_reminder" 
                            value="'.(isset($two) ? $two : "").'"><span class="task-btn btn btn-primary">2 nappal korábban</span>
                    </label>
                </div>
                <div class="task-reminder-div" id="task-reminder-div-05">
                    <label>
                        <input type="radio" title="3 nappal korábban" style="border: 0px" id="task-reminder-5" class="form-control radio-task-priority task-priority-edit" name="task_reminder"
                            value="'.(isset($three) ? $three : "").'"><span class="task-btn btn btn-primary">3 nappal korábban</span>
                    </label>
                </div>
            </div>
            </div>
            </div>
                ') : FormCustomHTMLFieldHelper::to("hidden_temp_03", "<span style='display: none;'></span>"),
            //($model !== null) ? FormTextareaFieldHelper::toTextarea('id_task_description', 'Jegyzet írása')->setColClass('col-6') : FormCustomHTMLFieldHelper::to("hidden_temp_04", "<span style='display: none;'></span>"),
            ($model !== null)
                ? ($this->agent->isMobile() ? FormTextareaFieldHelper::toTextarea('task_description', __('Leírás'))->setValue($taskDescription)->setRows(3)->setColClass('col-11') : FormTextareaFieldHelper::toTextarea('task_description', __('Leírás'))->setValue($taskDescription)->setRows(3)->setColClass('col-lg-6 col-sm-12'))
                : FormCustomHTMLFieldHelper::to("hidden_temp_04", "<span style='display: none;'></span>"),
            ($model !== null) ? ($model->ai_generated_from_text) ? FormInputFieldHelper::toTextarea('ai_generated_from_text', __('Eredeti szöveg'))->setColClass('col-lg-6 col-sm-12')->setRows(3)->setDisabled(true) : FormInputFieldHelper::toHidden('ai_generated_from_text', '')->addClass('d-none')->setColClass('d-none') : FormInputFieldHelper::toHidden('ai_generated_from_text', '')->addClass('d-none')->setColClass('d-none'),
            ($model && $model->id_task_type == TaskType::TYPE_REPORT) ?
            FormCustomHTMLFieldHelper::to('report-button-row', '
                <div class="form-group col-lg-2 col-sm-12">
                ' . FormButtonFieldHelper::toButton('Jegyzőkönyv generálás', 'to-report-button', '')
                    ->setClass('btn btn-primary')
                    ->render()
                . '
                </div>
            ') : FormInputFieldHelper::toHidden('blank', ''),
            FormInputFieldHelper::toHidden('id_task_description', '')->setValue($taskSimpleIdMax)->addClass('d-none')->setColClass('d-none'),
            FormCustomHTMLFieldHelper::to("hidden_warning", "<p style='display: none;' id='hidden_warning_text'></p>"),
            //FormButtonFieldHelper::toButton('', 'add_simple_task', 'save_button')->setIconClass('fas fa-save fa-lg'),
            FormButtonFieldHelper::toButton('', 'add_simple_task', 'save_button')->setIconClass('custom_save_button fas fa-plus-circle btn-custom'),
            //FormButtonFieldHelper::toButton('', 'add_simple_task', 'save_button')->setIconClass('fas fa-plus-circle btn btn-sm btn-primary'),
            FormInputFieldHelper::toHidden('task_responsible', '')->setValue($userName)->setElementId("task_responsible")->setClass("hidden_fields")->addClass('d-none')->setColClass('d-none'),
            FormInputFieldHelper::toHidden('task_responsible_id', '')->setValue($userId)->setElementId("task_responsible_id")->setClass("hidden_fields")->addClass('d-none')->setColClass('d-none'),
            FormInputFieldHelper::toHidden('task_status', '')->setValue($taskStatus)->setElementId("task_status")->setClass("hidden_fields")->addClass('d-none')->setColClass('d-none'),
            FormInputFieldHelper::toHidden('task_flag', '')->setValue($taskStatusFlag)->setElementId("task_flag")->setClass("hidden_fields")->addClass('d-none')->setColClass('d-none')
        ]);
    }

    protected function buildListDetailHelper()
    {
        return ListHelper::to('tasks', [
            ($this->agent->isMobile()) ?
                ListFieldHelper::to('task_name', __('Feladat megnevezése'))
                    ->setWidth('300px')
                    ->setType('custom')
                    ->setCustomCallback(function ($model) {
                        $explodedTaskName = explode(' ', $model->task_name);

                        $maxCharacterLengthPerRow = 22;

                        $characterLengthCount = 0;

                        foreach ($explodedTaskName as $key => &$_taskNamePart) {
                            $characterLengthCount += strlen($_taskNamePart);
                            if ($characterLengthCount > $maxCharacterLengthPerRow) {
                                $characterLengthCount = strlen($_taskNamePart);
                                $_taskNamePart = '<br>' . $_taskNamePart;
                            }
                        }

                        $implodedTaskName = implode(' ', $explodedTaskName);

                        return $implodedTaskName;
                    }) :
                ListFieldHelper::to('task_name', __('Feladat megnevezése'))
                    ->setType('custom')
                    ->setCustomCallback(function ($model) {
                        $explodedTaskName = explode(' ', $model->task_name);

                        $maxCharacterLengthPerRow = 44;

                        $characterLengthCount = 0;

                        foreach ($explodedTaskName as $key => &$_taskNamePart) {
                            $characterLengthCount += strlen($_taskNamePart);
                            if ($characterLengthCount > $maxCharacterLengthPerRow) {
                                $characterLengthCount = strlen($_taskNamePart);
                                $_taskNamePart = '<br>' . $_taskNamePart;
                            }
                        }

                        $implodedTaskName = implode(' ', $explodedTaskName);

                        return $implodedTaskName;
                    }),
            ListFieldHelper::to('task_priority', __('Prioritás'))
                ->setClass('task-priority-td')
                ->setType('custom')
                ->setDefaultContent('-')
                ->setSearchTypeSelect(
                    collect([
                        "" => "-",
                        1 => 1,
                        2 => 2,
                        3 => 3,
                        4 => 4,
                        5 => 5
                    ]), 'task_priority'
                    )
                ->setCustomCallback(function ($model) {
                    if(isset($model->task_priority)) {
                        $value = '<span style="text-align: center;" class="task-priority">' . $model->task_priority . '</span>';
                        return $value;
                    }
                }),
            ListFieldHelper::to('task_term', __('Határidő')),
            ListFieldHelper::to('type.task_type', __('Típus'))
                ->setType('custom')
                ->setCustomCallback(function ($model) {
                    return $model->type ? '<span class="p-1 rounded" style="text-align: center; color: ' . $model->type->task_type_color . '; background-color: ' . $model->type->task_type_background_color .';">' . $model->type->task_type . '</span>': '';
                }),
        ])
            ->setTitle(__('Feladatok menedzselése'))
            ->addRowActions(function ($model) {
                $actionsHTML = '';

                if (Auth::user()->can('update_task_simple')) {
                    $actionsHTML .= Link::to(route('updateSimpleTaskDetail', $model->getKey()), '<i class="fas fa-pencil-alt"></i>')
                        ->setClass('btn btn-primary-task btn-sm mr-1 custom-edit')
                        ->render();
                }

                return FormCustomHTMLFieldHelper::to('action', $actionsHTML)->renderTag();
            })
            ->addCheckboxes()
            ->setToolbarLinkInstance(
                ToolbarLinks::make()
                    ->addLinkIfCan('create_task_simple',route('listResolvedTask'),'<i class="fas fa-check-square"></i> <span>' . __('Lezártak').'</span>')
                    ->addLinkIfCan('create_task_simple',route('newSimpleTask'),'<i class="fas fa-plus-circle"></i> <span>' . __('Új hozzáadása').'</span>')
            )
            ->setTemplate('task.simpleTaskDetail');
    }

    /**
     * @param $model
     * @return FormHelper|void
     */
    protected function getFormHelperToUpdate($model) {
        $actionsHTML = Link::to(route('newComplexTaskModal', $model->getKey()), '<i class="fas fa-pencil-ruler" placeholder="Feladatkezelőbe"></i> ')
            ->setClass('btn btn-sm btn-primary float-right d-none')
            ->setText('Feladatkezelőbe')
            ->setElementId('complex-edit-button')
            ->setIconClass('fas fa-pencil-ruler')
            ->render();

        return $this->buildFormHelper($model)
            /*->setBack(
                FormButtonFieldHelper::toBack(__('Vissza'))
                    ->setIconClass('fas fa-caret-square-left')
                    ->setClass('btn btn-warning')
                    ->setUrl($this->getSuccessRedirectUrl())
            )*/
            /*->setSubmit(
                FormButtonFieldHelper::toButton('Törlés', 'delete_button', 'Törlés')
                    ->setElementId("delete_button")
                    ->setIconClass('fa fa-trash')
                    ->setClass('btn btn-primary float-right')
                    ->setHint("Biztosan törölni akarja? A törlés végleges lesz.")
                    ->setLabel('Biztosan törölni akarja? A törlés végleges lesz.')
            )*/
            ->addField(
                FormCustomHTMLFieldHelper::to('action_update', $actionsHTML)
            )
            ->addField(
                FormCustomHTMLFieldHelper::to('deleteWarningModal',
                    '<div id="deleteWarningModal" style="display: none;">
                                 <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h5 class="modal-title">Törlés</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                          <span aria-hidden="true">&times;</span>
                                        </button>
                                      </div>
                                      <div class="modal-body">
                                        <p>Biztosan törölni akarja?</p>
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Nem</button>
                                        <button type="button" class="btn btn-primary">Igen</button>
                                      </div>
                                    </div>
                                  </div>
                            </div>')
            )
            /*->setSubmit(
                FormButtonFieldHelper::toButton('Mentés', 'save_button', 'Mentés')->setElementId("save_button")->setIconClass('far fa-save')->setClass('btn btn-primary float-right')
            )*/
            /*->addField(FormButtonFieldHelper::toButton('Törlés', 'delete_button', 'Törlés'))->addClass("delete_button")*/
            ->setTitle(
                __(':Item szerkesztése', ['item' => __('Feladat')])
            );
    }

    public function goToMakeReport() {
        $description = request('description', '');
        Session::put('reportDescription', $description);

        return AjaxResponse::make(['url' => route('makeReport')], []);
    }

    /**
     * @param $model
     * @return FormHelper|void
     */
    protected function getDetailFormHelperToUpdate($model) {
        $form = $this->buildFormDetailHelper($model)
            ->addField(
                FormCustomHTMLFieldHelper::to('deleteWarningModal',
                    '<div id="deleteWarningModal" style="display: none;">
                                 <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <h5 class="modal-title">Törlés</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                          <span aria-hidden="true">&times;</span>
                                        </button>
                                      </div>
                                      <div class="modal-body">
                                        <p>Biztosan törölni akarja?</p>
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Nem</button>
                                        <button type="button" class="btn btn-primary">Igen</button>
                                      </div>
                                    </div>
                                  </div>
                            </div>')
            )
            ->setSubmit(
                FormButtonFieldHelper::toButton('Mentés', 'save_button', 'Mentés')->setElementId("save_button")->setIconClass('far fa-save')->setClass('btn btn-primary float-right')
            )
            ->setTaskPlanner(
                Link::to('#', '<i class="fas fa-pencil-ruler" placeholder="Feladattervezés"></i> ')
                    ->setClass('btn btn-sm btn-primary d-flex justify-content-center align-items-center')
                    ->setText('Feladattervezés')
                    ->setElementId('complex-edit-button')
                    ->setIconClass('fas fa-pencil-ruler')
                    ->setAttribute('onclick', 'saveAndGoToNewModal()')
            )
            ->setTitle(
                __(':Item szerkesztése', ['item' => __('Feladat')])
            );

        return $form;
    }

    protected function getFormHelperToInsert()
    {
        $helper = parent::getFormHelperToInsert()->setTitle(__('Új :item hozzáadása', ['item' => __('feladat')]));
        return $helper;
    }

    /**
     * Override Bread index for custom listHelper
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function index(Request $request)
    {
        //$formHelper = $this->buildFormHelper(null);
        $formHelper = $this->buildFormHelper(null);
        $listHelper = $this->buildListHelper();

        if ($request->ajax()) {
            if($request->query('draw') == 1) {
                return $listHelper->createDataTables($this->collectListData()->orderBy('created_at', 'DESC'))->make(true);
            } else {
                return $listHelper->createDataTables($this->collectListData())->make(true);
            }
        }

        return $listHelper->render()->with(['formHelper' => $formHelper]);
    }

    protected function collectListData() {
        return TaskSimple::with([
            'type:id_task_type,task_type,task_type_color,task_type_background_color' => []
        ]);
    }

    /**
     * Override Bread index for custom listHelper
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function indexDetail(Request $request)
    {
        $dateFilter = request('date_filter', false);

        $formHelper = $this->buildFormDetailHelper(null);
        $listHelper = $this->buildListDetailHelper();

        if ($request->ajax()) {
            if($request->query('draw') == 1) {
                return $listHelper->createDataTables($this->collectListDataDetail($dateFilter)->orderBy('created_at', 'DESC'))->make(true);
            } else {
                return $listHelper->createDataTables($this->collectListDataDetail($dateFilter))->make(true);
            }
            //return $listHelper->createDataTables($this->collectListData())->make(true);
        }

        return $listHelper->render()->with(['formHelper' => $formHelper]);
    }

    /**
     * Save resource - override Bread insert() method
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws Exception
     */
    public function insert()
    {
        $form = $this->getFormHelperToInsert();
        $form->modelFill();

        /**
         * @var TaskSimple $model
         */
        $model = $form->getModel();

        $model->id_task_owner = Auth::id();
        $model->id_task_responsible = Auth::id();
        $model->task_priority = 5;

        if (!$model->isValid() ||!$model->save()) {
            return $form->render();
        }

        return $this->redirectSuccess(route('indexDetailSimpleTask'), __('Sikeres létrehozás'));
    }

    /**
     * Alternative edit data
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        try {
            $model = $this->modelClass::findOrFail($id);
        } catch (Exception $e) {
            $model = $this->modelClass::query()->withoutGlobalScope(GetTaskFlagSimpleScope::class)->find($id);

            if ($model) {
                request()->session()->put('redirected', true);
                return redirect()->route('showTaskSimple', $id);
            }
        }

        if (!$model) {
            return $this->redirectError($this->getFailedRedirectUrl(), __('Nem található feladat'));
        }

        $form = $this->getDetailFormHelperToUpdate($model);

        return $form->render();
    }

    /**
     * Alternative closed data
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editClosed($id)
    {
        $model = $this->modelClass::findOrFail($id);

        $form = $this->getFormHelperToUpdate($model);

        return $form->render();
    }

    public function update($id)
    {
        $model = $this->modelClass::findOrFail($id);

        if(preg_match("/detail/", request()->server()['HTTP_REFERER'])) {
            $form = $this->getDetailFormHelperToUpdate($this->modelClass::findOrFail($id));
        } else {
            $form = $this->getFormHelperToUpdate($this->modelClass::findOrFail($id));
        }

        $taskName = request('task_name', '');
        $taskPriority = request('task_priority', '');
        $taskTerm = request('task_term', '');
        $taskReminder = request('task_reminder', '');
        $taskDescription = request('task_description', '');

        if(!$model->id_task_description) {
            $descSaveResult = $model->description()->create([
                "task_description" => $taskDescription
            ]);

            $taskDescriptionId = $descSaveResult->id_task_description;
            $model->id_task_description = $taskDescriptionId;
        } else {
            $descUpdateResult = $model->description()->update([
                "task_description" => $taskDescription
            ]);
        }

        if(!empty($taskName)) {
            $model->task_name = $taskName;
        }
        if(!empty($taskPriority)) {
            $model->task_priority = $taskPriority;
        }
        if(!empty($taskTerm)) {
            $model->task_term = $taskTerm;
        } else {
            $model->task_term = null;
        }
        if(!empty($taskReminder)) {
            $model->task_reminder = $taskReminder;
        } else {
            $model->task_reminder = null;
        }

        if ($model->id_task_responsible === null) {
            $model->id_task_responsible = $model->id_task_owner;
        }

        //if (!$form->validateAndSave()) {
        if (!$model->isValid() || !$model->save()) {
            return $form->render();
        }

        return $this->redirectSuccess(route('indexDetailSimpleTask'), __('Sikeres módosítás'));
    }

    /**
     * @param Request $request
     * @return boolean
     */
    public function bulkActionDisableItems(Request $request) {

        if($request['tasks_row_selectors'] && $request['tasks_row_selectors'][0] !== null) {
            $id = $request['tasks_row_selectors'][0];
        }

        if ($request->id_task) {
            $id = $request->id_task;
        }

        $modelTask = TaskSimple::find($id);

        DB::beginTransaction();

        try {

            $modelTask->task_status = "RESOLVED";
            $taskSuccess = $modelTask->save();

            DB::commit();

            return true;

        } catch(\Exception $db) {
            DB::rollBack();
            dd($db->getMessage());
        }

    }

    /**
     * Set non required task termination  date field
     *
     * @param Request $request
     *
     * @return bool
     */
    public function postTermDate(Request $request) {

        $modelTask = TaskSimple::find($request->id);

        DB::beginTransaction();

        try {

            $modelTask->task_term = $request->term_date;
            $taskSuccess = $modelTask->save();

            DB::commit();

            return true;

        } catch(\Exception $db) {
            DB::rollBack();
            dd($db->getMessage());
        }
    }

    /**
     * Override getSuccessRedirectUrl()
     *
     * @return string
     */
    protected function getSuccessRedirectUrl()
    {
        return action('\\' . static::class . '@indexDetail');
    }

    /**
     * @param $id
     * @return string
     */
    protected function getInsertSuccessRedirectUrl($id)
    {
        return action('\\' . static::class . '@indexDetail', [$id]);
    }

    /**
     * Override collectListData()
     *
     * @param $filterDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function collectListDataDetail($dateFilter)
    {
        $query = TaskSimple::with([
            'type:id_task_type,task_type,task_type_color,task_type_background_color' => []
        ]);

        switch ($dateFilter) {
            case 'task_time_today':
                $query = $query->whereDay('task_term', '=', Carbon::today());
                break;
            case 'task_time_tomorrow':
                $query = $query->whereDay('task_term', '=', Carbon::tomorrow());
                break;
            case 'task_time_this_week':
                $query = $query->whereBetween('task_term', [Carbon::today()->startOfWeek()->startOfDay(), Carbon::today()->endOfWeek()->endOfDay()]);
                break;
            case 'task_time_next_week':
                $query = $query->whereBetween('task_term', [Carbon::today()->addWeek()->startOfWeek()->startOfDay(), Carbon::today()->addWeek()->endOfWeek()->endOfDay()]);
                break;
            case 'task_time_all': default:
                break;
        }

        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function collectListResolvedData() {
//        return TaskSimple::query()->where('task_status', '=', 'RESOLVED')->withoutGlobalScope(GetTaskFlagSimpleScope::class)->orderBy('created_at', 'desc');
        return TaskSimple::query()->withoutGlobalScope(GetTaskFlagSimpleScope::class)->withGlobalScope('resolved', new GetTaskFlagSimpleResolvedScope())->orderBy('created_at', 'desc');
    }

    protected function resolved(Request $request) {
        $formHelper = $this->buildFormHelper(null);
        $listHelper = $this->buildListClosedHelper();

        if ($request->ajax()) {
            return $listHelper->createDataTables($this->collectListResolvedData()->orderBy('created_at', 'desc'))->make(true);
        }

        return $listHelper->render()->with(['formHelper' => $formHelper]);
    }

    public function prioritizationPage() {
        $modalProvider = app('ModalProvider');

        $modalProvider->addModal(
            (new Modal('taskSimpleModal', __('Feladat szerkesztése'), view('task.modal.taskSimpleModal'), '', '', false))
                ->addButton('cancel', __('Vissza'), 'btn-primary task-back')
                ->addButton('ok', '<i class="fas fa-pencil-ruler" placeholder="Feladattervezés"></i> Feladattervezés', 'btn-primary complex-task-btn')
                ->addButton('ok', __('Mentés'), 'btn-primary save-task-prioritization')
        );

        $todayDate = Carbon::today()->toDateString();

        $tomorrowDate = Carbon::tomorrow()->isWeekend() ? Carbon::now()->next(CarbonInterface::MONDAY)->toDateString() : Carbon::tomorrow()->toDateString();

        $startOfWeek = Carbon::now()->startOfWeek();

        $endOfWeek = Carbon::now()->endOfWeek();

        $taskSimples = TaskSimple::query()->get();

        $today = new Collection();

        $tomorrow = new Collection();

        $thisWeek = new Collection();

        $notScheduled = new Collection();

        $taskWarehouse = new Collection();

        foreach ($taskSimples as $taskSimple) {
            if ($taskSimple->task_term === $todayDate) {
                $today->push($taskSimple);
            } else if ($taskSimple->task_term === $tomorrowDate) {
                $tomorrow->push($taskSimple);
            } else if ($taskSimple->task_term !== null && Carbon::parse($taskSimple->task_term)->between($startOfWeek, $endOfWeek)) {
                $thisWeek->push($taskSimple);
            } else if ($taskSimple->task_term === null && $taskSimple->task_priority == 4) {
                $notScheduled->push($taskSimple);
            } else {
                $taskWarehouse->push($taskSimple);
            }
        }

        return view('task.prioritizationPage', compact('today', 'tomorrow', 'thisWeek', 'notScheduled', 'taskWarehouse'));
    }

    public function changeTermAndPriorityOfTask() {
        $id_task = request('id_task');

        $task_term = request('task_term');

        try {
            $task = TaskSimple::findOrFail($id_task);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return AjaxResponse::make([], [], true);
        }

        $todayDate = Carbon::today()->toDateString();

        $tomorrowDate = Carbon::tomorrow()->isWeekend() ? Carbon::now()->next(CarbonInterface::MONDAY)->toDateString() : Carbon::tomorrow()->toDateString();

        switch ($task_term) {
            case 'today':
                $task->task_term = $todayDate;
                $task->task_priority = 1;
                break;
            case 'tomorrow':
                $task->task_term = $tomorrowDate;
                $task->task_priority = 2;
                break;
            case 'this-week':
                $task->task_term = Carbon::now()->next(CarbonInterface::FRIDAY);
                $task->task_priority = 3;
                break;
            case 'not-scheduled':
                $task->task_term = null;
                $task->task_priority = 4;
                break;
            case 'task-warehouse':
                $task->task_term = null;
                $task->task_priority = 5;
                break;
            default:
                return AjaxResponse::make([], [], true);
        }

        try {
            $task->save();
        } catch (\Exception $e) {
            Log::error('Error saving TaskSimple (' . $task->getKey() . ') model: ' . $e->getMessage());
            return AjaxResponse::make([], [], true);
        }

        return AjaxResponse::make([], []);
    }

    public function getTaskSimpleDatas()
    {
        $id_task = request('id_task');

        try {
            $task = TaskSimple::with('description')->findOrFail($id_task);

            return AjaxResponse::make(['task' => $task->toArray()]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return AjaxResponse::make([], [], true);
        }
    }

    public function updateTaskSimpleDatas() {
        $id_task = request('id_task');
        $task_name = request('task_name');
        $task_priority = request('task_priority');
        $task_term = request('task_term');
        $task_reminder = request('task_reminder');
        $task_description = request('task_description');

        try {
            /**
             * @var TaskSimple $task
             */
            $task = TaskSimple::findOrFail($id_task);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Error find TaskSimple (' . $id_task . ') model: ' . $e->getMessage());
            return AjaxResponse::make([], [], true);
        }

        $task->task_name = $task_name;
        $task->task_priority = $task_priority;
        $task->task_term = $task_term;
        $task->task_reminder = $task_reminder;

        if(!$task->id_task_description) {
            $descSaveResult = $task->description()->create([
                "task_description" => $task_description
            ]);

            $taskDescriptionId = $descSaveResult->id_task_description;
            $task->id_task_description = $taskDescriptionId;
        } else {
            $descUpdateResult = $task->description()->update([
                "task_description" => $task_description
            ]);
        }

        try {
            $task->save();
        } catch (Exception $e) {
            Log::error('Error saving TaskSimple (' . $task->getKey() . ') model: ' . $e->getMessage());
            return AjaxResponse::make([], [], true);
        }

        return AjaxResponse::make([], []);
    }

    public function setTaskReminder() {
        $id_task = request('id_task');
        $task_reminder = request('task_reminder');

        /**
         * @var TaskSimple $task
         */
        $task = TaskSimple::findOrFail($id_task);

        if ($task) {
            $task->task_reminder = $task_reminder;
        }

        $task->save();

        return AjaxResponse::make([], []);
    }
}
