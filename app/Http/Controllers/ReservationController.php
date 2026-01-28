<?php

namespace App\Http\Controllers\Reservation;

use App\Http\Components\Ajax\AjaxResponse;
use App\Http\Components\FormHelper\FormButtonFieldHelper;
use App\Http\Components\FormHelper\FormCustomHTMLFieldHelper;
use App\Http\Components\FormHelper\FormSelectFieldHelper;
use App\Http\Controllers\BREADController;
use App\Persistence\Models\LicenseType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DebugBar\DataFormatter\DataFormatterInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
use App\Persistence\Models\Employee;
use App\Persistence\Models\Reservation;
use App\Persistence\Models\ReservationRepeat;
use App\Persistence\Models\Resources;
use App\Persistence\Models\ResourcesType;

use DB;
use DateTime;

use Illuminate\Support\Facades\Auth;
use MongoDB\Driver\Session;
use Symfony\Component\Console\Input\Input;

use Spatie\Permission\Traits\HasRoles;
use App\Traits\ReservationRepeatTrait;

/**
 * Class ReservationController
 * @package App\Http\Controllers\Reservation
 * Example controller
 */
class ReservationController extends BREADController
{
    use HasRoles, ReservationRepeatTrait;

    protected $modal;

    public function __construct()
    {
        app('Presenter')->addJs('reservations.js');
        app('Presenter')->addCSS('custom_reservation.css');
        $this->modelClass = Reservation::class;
        /*app('Presenter')->addJs('startbootstrap_sb_admin_2.js');
        app('Presenter')->addCSS('sb-admin-2.css');
        app('Presenter')->addCSS('custom-sb-admin.css');*/
        /*$formHelper = self::buildFormHelper(null);
        $this->modal = Modal::modal('reservationModal', 'Eszközfoglalás', view('reservation.reservation_modal_form', [ 'formHelper' => $formHelper ]), 'primary', 'confirm', false);*/
    }

    /**
     * Index override example
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function index(Request $request)
    {

        /*$user = new Employee();
        $user->hasAllRoles('Adminisztrátor');*/

        if($request) {

            if (sizeof(Auth::user()->roles) == 1) {
                //one role
                $userRoleId = Auth::user()->roles[0]['id'];
            } else {
                $userRoleId = [];
                //more than one role
                foreach (Auth::user()->roles as $roleKey => $roleValue) {
                    $userRoleId[] = $roleValue->id;
                }
            }
        }

        //TODO/FIX: ? admin role 1 display
        $resType = $this->getResourceTypes($userRoleId);
        $res = $this->getResource($resType);

        return view('resource.resourcesIndex', [
            //'formHelper' => $this->buildFormHelper($this->modelClass)->setActionFromNamedRoute('reservationCalendar')
            'resource_types' => $resType,
            'resources' => $res
        ]);
    }

    /**
     * Override Save reservation resource
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws Exception
     */
    public function insert()
    {
        /*
         * get reservation repeat options from input
         *
         * repeat_type, repeat_role, repeat_start, repeat_end,
         * repeat_role, repeat_role_end_count_input (repeat_role_end_count)
         */

        //unit_test_modify
        if ( request()->input('reservation_date_start') !== null && request()->input('reservation_date_end') !== null && request('reservation_update_id') == null ) {
            //adott idosavban ne lehessen ugyanarra az eszkozre es eszkoztipusra foglalni
            if (request()->input('resource_type') !== null) {
                $resourceTypeCheck = trim(request()->input('resource_type'));
            }
            if (request()->input('resource') !== null) {
                $resourceCheck = trim(request()->input('resource'));
            }

            if( request('reservation_repeat') !== null ) {
                //korabbi megvalositasbol - jelenleg nem hasznalt!!
                $checkReturn = $this->checkReservation(trim(request()->input('reservation_date_start')),
                    trim(request()->input('reservation_date_end')),
                    //trim(request()->input('repeat_end')),
                    $resourceTypeCheck,
                    $resourceCheck,
                    $is_repeat = true);
            } else {
                $checkReturn = $this->checkReservation(trim(request()->input('reservation_date_start')),
                    trim(request()->input('reservation_date_end')),
                    $resourceTypeCheck,
                    $resourceCheck);
            }

            if( $checkReturn['success'] == true && $checkReturn['message'] == "E"
                && $checkReturn['data']['reservation_date_start'] == request()->input('reservation_date_start').":00"
                && $checkReturn['data']['reservation_date_end'] == request()->input('reservation_date_end').":00" )
            {
                sleep(1);
                return $this->redirectError($this->getFailedRedirectUrl(), __('Létező foglalás!'));
            } else if ($checkReturn['success'] == true && $checkReturn['message'] == "R") {
                sleep(1);
                return $this->redirectError($this->getFailedRedirectUrl(), __('Létező foglalás!'));
            } else if ($checkReturn['success'] == true && $checkReturn['message'] == "T")
            {
                $inputDateStart = trim(request()->input('reservation_date_start')).":00";
                $inputDateEnd = trim(request()->input('reservation_date_end')).":00";

                sleep(1);
                return $this->redirectError($this->getFailedRedirectUrl(), __('Létező idősáv!'));
            }

        }
        //unit_test_modify

        //Update method - update reservation by input ID
        if(request('reservation_update_id') !== null) {
            $updateReservationId = request('reservation_update_id');
            $this->updateReservation($updateReservationId);

            return $this->redirectSuccess(route('reservationCalendarView'), __('Foglalás módosítása sikeres'));
            //return $this->redirectSuccess($this->getSuccessRedirectUrl(), __('Foglalás módosítása sikeres'));
        }

        $form = $this->getFormHelperToInsert();
        $form->modelFill();
        /**
         * @var Reservation $model
         */
        $model = $form->getModel();

        if($model->reservation_all_day == "I" || $model->reservation_all_day == "i"|| $model->reservation_all_day == 1) {
            //egesz napos foglalas
            $dayStartPostfix = "00:01:00";
            $dayEndPostfix = "23:59:59";
            $reservationDateStartExplode = explode(" ", $model->reservation_date_start);
            $reservationDateEndExplode = explode(" ", $model->reservation_date_end);
            $model->reservation_date_start = $reservationDateStartExplode[0]." ".$dayStartPostfix;
            $model->reservation_date_end = $reservationDateEndExplode[0]." ".$dayEndPostfix;

        } else {
            $model->reservation_date_start = date("Y-m-d H:i:s", strtotime($model->reservation_date_start));
            $model->reservation_date_end = date("Y-m-d H:i:s", strtotime($model->reservation_date_end));
        }

        if (!$model->isValid() || !$model->save()) {
            return $form->render();
        }

        //reservation_repeat save
        $reservationRepeat = new ReservationRepeat();

        $getReservationRepeatIdField = $form->getField('reservation_repeat_id');

        $reservationRepeatId = $model->reservation_repeat_id;

        DB::beginTransaction();

        try {
            if (request('repeat_type') !== null) {
                $reservationRepeat->reservation_repeat_id = $reservationRepeatId;
                $repeatType = request('repeat_type');
                $reservationRepeat->repeat_type = $repeatType;
            }
            if (request('repeat_role') !== null) {
                $repeatRoleInput = request('repeat_role');
                $reservationRepeat->repeat_role = $repeatRoleInput;
            }
            if (request('repeat_start') !== null) {
                $repeatStartAt = request('repeat_start');
                $reservationRepeat->repeat_start = $repeatStartAt;
            }
            if (request('repeat_end') !== null) {
                $repeatEndAt = request('repeat_end');
                $reservationRepeat->repeat_end = $repeatEndAt;
            }
            if (request('repeat_role_end_count_input') !== null) {
                $repeatRoleEndCountInput = request('repeat_role_end_count_input');
                $reservationRepeat->repeat_role_end_count_input = $repeatRoleEndCountInput;
            }
            //ismetlodesek rogzitese - repeat_reserved
            if (request('repeat_end') !== null) {
                if (request('repeat_role_end_count_input') !== null) {
                    $repeatRoleEndCountInput = request('repeat_role_end_count_input');
                } else {
                    $repeatRoleEndCountInput = null;
                }
                $repeatReservedReturnData = $this->setRepeatByDates(trim(request()->input('reservation_date_start')),
                    trim(request()->input('reservation_date_end')),
                    $repeatType,
                    $resourceCheck,
                    $repeatRoleInput,
                    $repeatStartAt,
                    $repeatEndAt,
                    $repeatRoleEndCountInput);

                $repeatReservedReturnFilteredData =  array_map("unserialize", array_unique(array_map("serialize", $repeatReservedReturnData)));

                $repeatReserved = base64_encode(serialize($repeatReservedReturnFilteredData));

                if ($repeatReserved !== null) {
                    $reservationRepeat->repeat_reserved = $repeatReserved;
                }
                //ismetlodesek rogzitese - repeat_reserved
            }

            $reservationSuccess = $reservationRepeat->save();

            DB::commit();

        } catch(\Exception $db) {
            DB::rollBack();
            dd($db->getMessage());
        }

        return $this->redirectSuccess(route('reservationCalendarView'), __('Foglalás létrehozása sikeres'));
        //return $this->redirectSuccess(route('reservationCalendarView', "debug"), __('Foglalás létrehozása sikeres'));

    }

    /**
     * Override Update reservation resource
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws Exception
     */
    public function updateReservation($id)
    {
        /*
         * get reservation repeat options from input
         *
         * repeat_type, repeat_role, repeat_start, repeat_end,
         * repeat_role, repeat_role_end_count_input (repeat_role_end_count)
         */

        $form = $this->getFormHelperToUpdate($this->modelClass::findOrFail($id));
        $form->modelFill();

        /**
         * @var Reservation $model
         */
        $model = $form->getModel();

        $resourceCheck = $form->getModel()->resource;

        if($model->reservation_all_day == "I" || $model->reservation_all_day == "i" || $model->reservation_all_day == 1) {
            //egesz napos foglalas
            $dayStartPostfix = "00:01:00";
            $dayEndPostfix = "23:59:59";
            $reservationDateStartExplode = explode(" ", $form->getModel()->reservation_date_start);
            $reservationDateEndExplode = explode(" ", $form->getModel()->reservation_date_end);
            $model->reservation_date_start = $reservationDateStartExplode[0]." ".$dayStartPostfix;
            $model->reservation_date_end = $reservationDateEndExplode[0]." ".$dayEndPostfix;
            $form->getModel()->reservation_date_start = $reservationDateStartExplode[0]." ".$dayStartPostfix;
            $form->getModel()->reservation_date_end = $reservationDateEndExplode[0]." ".$dayEndPostfix;

        } else {
            $model->reservation_date_start = date("Y-m-d H:i:s", strtotime($model->reservation_date_start));
            $model->reservation_date_end = date("Y-m-d H:i:s", strtotime($model->reservation_date_end));
            $form->getModel()->reservation_date_start = date("Y-m-d H:i:s", strtotime($form->getModel()->reservation_date_start));
            $form->getModel()->reservation_date_end = date("Y-m-d H:i:s", strtotime($form->getModel()->reservation_date_end));
        }

        if (!$form->validateAndSave()) {
            return $form->render();
        }

        //reservation_repeat lekerdezes
        $reservRepeatSelect = ReservationRepeat::query()
            ->where('reservation_repeat_id', '=', $model->reservation_repeat_id)
            ->get()
            ->pluck('id');

        if(isset($reservRepeatSelect[0])) {
           $repeatId = $reservRepeatSelect[0];
        }

        //reservation_repeat update
        $modelReservationUpdate = ReservationRepeat::find($repeatId);

        DB::beginTransaction();

        try {
            if (request('repeat_type') !== null) {
                $modelReservationUpdate->reservation_repeat_id = $model->reservation_repeat_id;
                $repeatType = request('repeat_type');
                $modelReservationUpdate->repeat_type = $repeatType;
                //$modelReservationUpdate->update(['reservation_repeat_id' => $model->reservation_repeat_id]);
            }
            if (request('repeat_role') !== null) {
                $repeatRoleInput = request('repeat_role');
                $modelReservationUpdate->repeat_role = $repeatRoleInput;
            }
            if (request('repeat_start') !== null) {
                $repeatStartAt = request('repeat_start');
                $modelReservationUpdate->repeat_start = $repeatStartAt;
            }
            if (request('repeat_end') !== null) {
                $repeatEndAt = request('repeat_end');
                $modelReservationUpdate->repeat_end = $repeatEndAt;
                $modelReservationUpdate->repeat_role_end_count_input = null;
            }
            if (request('repeat_role_end_count_input') !== null) {
                $repeatRoleEndCountInput = request('repeat_role_end_count_input');
                $modelReservationUpdate->repeat_role_end_count_input = $repeatRoleEndCountInput;
                $modelReservationUpdate->repeat_start = null;
                $modelReservationUpdate->repeat_end = null;
            }
            //ismetlodesek modositasa - repeat_reserved
            if (request('repeat_end') !== null) {
                if (request('repeat_role_end_count_input') !== null) {
                    $repeatRoleEndCountInput = request('repeat_role_end_count_input');
                } else {
                    $repeatRoleEndCountInput = null;
                }
                $repeatReservedReturnData = $this->setRepeatByDates($form->getModel()->reservation_date_start,
                    $form->getModel()->reservation_date_start,
                    $repeatType,
                    $resourceCheck,
                    $repeatRoleInput,
                    $repeatStartAt,
                    $repeatEndAt,
                    $repeatRoleEndCountInput);

                $repeatReservedReturnFilteredData =  array_map("unserialize", array_unique(array_map("serialize", $repeatReservedReturnData)));

                $repeatReserved = base64_encode(serialize($repeatReservedReturnFilteredData));

                if ($repeatReserved !== null) {
                    $modelReservationUpdate->repeat_reserved = $repeatReserved;
                }
                //ismetlodesek modositasa - repeat_reserved
            }

            $reservationSuccess = $modelReservationUpdate->save();

            DB::commit();

            return $this->redirectSuccess(route('reservationCalendarView'), __('Foglalás módosítása sikeres'));
            //return $this->redirectSuccess($this->getSuccessRedirectUrl(), __('Foglalás módosítása sikeres'));

        } catch(\Exception $db) {
            DB::rollBack();
            dd($db->getMessage());
        }

        return $this->redirectSuccess(route('reservationCalendarView'), __('Foglalás módosítása sikeres'));
        //return $this->redirectSuccess($this->getSuccessRedirectUrl(), __('Foglalás módosítása sikeres'));
    }

    /**
     * korabbi foglalasok visszakerdezese, tovabbi ellenorzes celjabol
     *
     * @param string $dateStart             2023-04-16 08:00
     * @param string $dateEnd
     * @param string $resourceType
     * @param string $resource
     * @param boolean $is_repeat            true/false -> true, if reservation repeat are defined
     *
     * @return array
     */
    public function checkReservation($dateStart, $dateEnd, $resourceType, $resource, bool $is_repeat = false) {

        if( $is_repeat ) {
            /*$modelCheck = DB::table('reservation')
                ->leftJoin('reservation_repeat', 'reservation.reservation_repeat_id', '=', 'reservation_repeat.reservation_repeat_id')
                ->where('reservation.resource_type', '=', $resourceType)
                ->where('reservation.resource', '=', $resource)
                ->where('reservation.reservation_date_start', '=', $dateStart)
                ->where('reservation_repeat.repeat_end', '=', $dateEnd)
                ->get()
                ->toArray();
            $modelCheck[0] = (array) $modelCheck[0];*/

            $modelCheck = Reservation::query()
                ->where('reservation_date_start', '=', $dateStart)
                ->where('reservation_date_end', '=', $dateEnd)
                ->where('resource_type', '=', $resourceType)
                ->where('resource', '=', $resource)
                ->get()
                ->toArray();

        } else {
            $modelCheck = Reservation::query()
                ->where('reservation_date_start', '=', $dateStart)
                ->where('reservation_date_end', '=', $dateEnd)
                ->where('resource_type', '=', $resourceType)
                ->where('resource', '=', $resource)
                ->get()
                ->toArray();
        }

        if( is_array($modelCheck) && sizeof($modelCheck) > 0 ) {
            //van pontosan ugyanaz a korabbi foglalas az adott idopontban!!
            return [
                "success" => true,
                "message" => "E", //E - equal
                "data"  => $modelCheck[0]
            ];
        } else {
            //ha nincs talalat, akkor tovabbi vizsgalatok az ismetlesek ellenorzesehez!!
            $modelCheckAlter = DB::table('reservation')
                ->leftJoin('reservation_repeat', 'reservation.reservation_repeat_id', '=', 'reservation_repeat.reservation_repeat_id')
                ->where('reservation.resource', '=', $resource)
                ->where('reservation.reservation_repeat', '=', 'I')
                ->whereNotNull('reservation_repeat.repeat_reserved')
                ->get()
                ->toArray();

            if(sizeof($modelCheckAlter) > 0) {
                //
                foreach ($modelCheckAlter as $objKey => $objValue) {
                    $repeatData = $objValue->repeat_reserved;
                    $repeatDecodeData = unserialize(base64_decode($repeatData));

                    //dd($repeatDecodeData);

                    $repeatDateStart = $dateStart . ":00";
                    $repeatDateEnd = $dateEnd . ":00";

                    if (is_array($repeatDecodeData) && sizeof($repeatDecodeData) > 0 && $repeatDecodeData) {
                        foreach ($repeatDecodeData as $dateKey => $dateValue) {
                            if( explode(" ", $dateValue['start'])[0] == explode(" ", $repeatDateStart)[0] ) {
                                if (($dateValue['start'] == $repeatDateStart || $dateValue['end'] == $repeatDateEnd) ||
                                    ($dateValue['start'] <= $repeatDateStart && $dateValue['end'] >= $repeatDateEnd) ||
                                    ($dateValue['start'] >= $repeatDateStart && $dateValue['end'] <= $repeatDateEnd) /*||
                                    ($dateValue['start'] <= $repeatDateStart && $dateValue['end'] <= $repeatDateEnd)*/ /*||
                                ($dateValue['start'] >= $repeatDateStart && $dateValue['end'] >= $repeatDateEnd)*/
                                ) {
                                    return [
                                        "success" => true,
                                        "message" => "R",
                                        "data" => $modelCheck[0] = [
                                            "reservation_date_start" => $dateValue['start'],
                                            "reservation_date_end" => $dateValue['end']
                                        ]
                                    ];
                                }
                            }
                        }
                    }
                }
                //
            }

            //adott idosavon beluli korabbi foglalas!
            /*$dateStartExplode = explode(" ", $dateStart);
              $dateEndExplode = explode(" ", $dateEnd);

              $modelCheckTimeSlot = Reservation::query()
                ->where('reservation_date_start', 'LIKE', "%{$dateStartExplode[0]}%")
                ->where('reservation_date_end', 'LIKE', "%{$dateEndExplode[0]}%")
                ->where('resource', '=', $resource)
                ->get()
                ->toArray();*/

            $modelCheckTimeSlot = Reservation::query()
                ->where('reservation_date_start', '<=', $dateStart.":00")
                ->where('reservation_date_end', '>=', $dateEnd.":00")
                ->where('resource', '=', $resource)
                ->get()
                ->toArray();

            if(empty($modelCheckTimeSlot)) {
                $modelCheckTimeSlot = Reservation::query()
                    ->where('reservation_date_start', '>=', $dateStart.":00")
                    ->where('reservation_date_end', '<=', $dateEnd.":00")
                    ->where('resource', '=', $resource)
                    ->get()
                    ->toArray();

                if(empty($modelCheckTimeSlot)) {
                    return [
                        "success" => false,
                    ];
                } else {
                    return [
                        "success" => true,
                        "message" => "T", // T -idosav -time slot
                        "data"  => $modelCheckTimeSlot
                    ];
                }
            } else {
                return [
                    "success" => true,
                    "message" => "T", // T -idosav -time slot
                    "data"  => $modelCheckTimeSlot
                ];
            }
        }
    }

    /**
     * @param string|null $startDate     Time format
     * @param string|null $endDate       Time format
     * @param string|null $repeatType
     * @param string|null $resource      JSON encoded string
     * @param string|null $repeatRoleInput
     * @param string|null $repeatStartAt
     * @param string|null $repeatEndAt
     * @param string|null $repeatRoleEndCountInput
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View|RedirectResponse
     */
    public function setRepeatByDates($startDate = null, $endDate = null, $repeatType = null, $resource = null, $repeatRoleInput = null, $repeatStartAt = null, $repeatEndAt = null, $repeatRoleEndCountInput = null) {

        $setterRepeat = (object) [];

        $setterRepeat->resource = $resource;
        $setterRepeat->reservation_date_start = $startDate;
        $setterRepeat->reservation_date_end = $endDate;
        $setterRepeat->repeat_type = $repeatType;
        $setterRepeat->repeat_role = $repeatRoleInput;
        $setterRepeat->repeat_start = $repeatStartAt;
        $setterRepeat->repeat_end = $repeatEndAt;
        if($repeatRoleEndCountInput !== null) {
            $setterRepeat->repeat_role_end_count_input = $repeatRoleEndCountInput;
        } else {
            $setterRepeat->repeat_role_end_count_input = null;
        }

        if( is_array($this->setRepeatEvents($setterRepeat)) && !empty($this->setRepeatEvents($setterRepeat)) ) {
            return $this->setRepeatEvents($setterRepeat);
        }

    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View|RedirectResponse
     */
    public function delete($id)
    {
        $model = Reservation::query()
            ->where('id_reservation', '=', $id)
            ->pluck('reservation_repeat_id');

        $confirmDelete = $this->confirmDelete($id);
        if ($confirmDelete !== true) {
            return $confirmDelete;
        }

        $this->modelClass::findOrFail($id)->delete();

        //reservation_repeat lekerdezes
        $reservRepeatSelect = ReservationRepeat::query()
            ->where('reservation_repeat_id', '=', $model[0])
            ->get()
            ->pluck('id');

        if(isset($reservRepeatSelect[0])) {
            $repeatId = $reservRepeatSelect[0];
        }

        DB::delete('delete from reservation_repeat where id = :id', ['id' => $repeatId]);

        sleep(1);
        return $this->redirectSuccess(route('reservationCalendarView'), __('Sikeres törlés'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View|RedirectResponse
     */
    public function deletes(Request $request, $id)
    {

        if ($request->query('repeat_data') === null) {
            //alap foglalas kezeles!

            $model = Reservation::query()
                ->where('id_reservation', '=', $id)
                ->pluck('reservation_repeat_id');

            $confirmDelete = $this->confirmDelete($id);
            if ($confirmDelete !== true) {
                return $confirmDelete;
            }

            $this->modelClass::findOrFail($id)->delete();

            //reservation_repeat lekerdezes
            $reservRepeatSelect = ReservationRepeat::query()
                ->where('reservation_repeat_id', '=', $model[0])
                ->get()
                ->pluck('id');

            if (isset($reservRepeatSelect[0])) {
                $repeatId = $reservRepeatSelect[0];
            }

            DB::delete('delete from reservation_repeat where id = :id', ['id' => $repeatId]);

        } else {
            //foglalason beluli ismetlodes kezeles!
            //$modelReservationRepeatUpdate = ReservationRepeat::find($id);
            $modelRepeatDataId = Reservation::query()
                ->where('id_reservation', '=', $id)
                ->pluck('reservation_repeat_id');

            if($modelRepeatDataId[0]) {
                //$modelReservationRepeatUpdate = ReservationRepeat::find($modelRepeatDataId[0]);
                $modelReservationRepeatUpdate = ReservationRepeat::where('reservation_repeat_id', $modelRepeatDataId[0])->get()->toArray();
            }

            DB::beginTransaction();

            try {
                if($modelReservationRepeatUpdate[0]['repeat_desc'] !== null && mb_strlen($modelReservationRepeatUpdate[0]['repeat_desc']) > 0) {
                    //letezik korabbi ismetlesi adat kizaras!
                    $repeatReservDecodedData = base64_decode($modelReservationRepeatUpdate[0]['repeat_desc']);
                    $repeatReservDecodedArrayData = unserialize($repeatReservDecodedData);
                    if (request('repeat_data') !== null) {
                        array_push($repeatReservDecodedArrayData, request('repeat_data'));
                        $repeatReservDataCodec = base64_encode(serialize($repeatReservDecodedArrayData));
                    }
                } else {
                    if (request('repeat_data') !== null) {
                        $repeatReservData[] = request('repeat_data');
                        $repeatReservDataCodec = base64_encode(serialize($repeatReservData));
                        //$modelReservationRepeatUpdate->repeat_reserved = $repeatReservData;
                    }
                }

                //$reservationSuccess = $modelReservationRepeatUpdate->save();
                DB::statement('UPDATE reservation_repeat
                                SET repeat_desc = "'.$repeatReservDataCodec.'"
                                WHERE reservation_repeat_id = '.$modelRepeatDataId[0].'');

                DB::commit();

                sleep(1);
                return $this->redirectSuccess(route('reservationCalendarView'), __('Sikeres törlés'));

            } catch(\Exception $db) {
                DB::rollBack();
                dd($db->getMessage());
            }
        }
    }

    /**
     * @param int|array $userRoleId
     * @return \Illuminate\Support\Collection
     */
    protected function getResourceTypes($userRoleId)
    {
        if(is_array($userRoleId) && sizeof($userRoleId) > 1) {
            $returnAr = [];
            $tempAr = [];
            foreach ($userRoleId as $key => $value) {
                /*$tempAr[] = ResourcesType::query()
                    ->Id($value)
                    ->pluck('name','id_resourcetype');*/
                $tempAr[] = ResourcesType::with(['roles'])->whereHas('roles', function($query) use ($value) {
                    return $query->whereIn('roles.id', [$value]);
                })->get()->pluck('name', 'id_resourcetype')->toArray();
            }

            if(is_array($tempAr)) {
                foreach ($tempAr as $tKey => $tValue) {
                    if(is_object($tValue)) {
                        foreach ($tValue->all() as $k => $v ) {
                            $returnAr[$k] = $v;
                        }
                    }
                    if(is_array($tValue)) {
                        foreach ($tValue as $kAr => $vAr ) {
                            $returnAr[$kAr] = $vAr;
                        }
                    }
                }
            }
            if(is_array($returnAr) && sizeof($returnAr) > 0) {
                array_unique($returnAr);
                return $returnAr;
            }
        } else {
            if($userRoleId == 1) {
                //administrator
                /*return ResourcesType::query()
                    ->pluck('name','id_resourcetype');*/
                return ResourcesType::with(['roles'])->whereHas('roles', function($query) use ($userRoleId) {
                    return $query->whereIn('roles.id', [$userRoleId]);
                })->get()->pluck('name', 'id_resourcetype')->toArray();
            } else {
                return ResourcesType::with(['roles'])->whereHas('roles', function($query) use ($userRoleId) {
                    return $query->whereIn('roles.id', [$userRoleId]);
                })->get()->pluck('name', 'id_resourcetype')->toArray();
                /*return ResourcesType::query()
                    ->Id($userRoleId)
                    ->pluck('name', 'id_resourcetype');*/
            }
        }
    }

    /**
     * @param int|array $resType
     * @return \Illuminate\Support\Collection
     */
    protected function getResource($resType)
    {
        if($resType === null) {
            //admin
            $resourcesTmp = Resources::all()->toArray();
            if (is_array($resourcesTmp)) {
                foreach ($resourcesTmp as $resAdmKey => $resAdmValue) {
                    $resources[] = $resAdmValue;
                }
            }
        } else {
            if (is_array($resType) || is_array($resType->all()) || is_object($resType)) {
                $resTemp = Resources::all()->toArray();
                if (is_array($resTemp)) {
                    foreach ($resTemp as $resKey => $resValue) {
                        $tmp[] = $resValue;
                    }
                }
                if (is_object($resType)) {
                    $resTypeConv = $resType->all();
                } else {
                    $resTypeConv = $resType;
                }
                //foreach ($resType->all() as $keyResType => $valueResType) {
                foreach ($resTypeConv as $keyResType => $valueResType) {
                    foreach ($tmp as $rKey => $rValue) {
                        if (in_array($valueResType, $rValue)) {
                            $resources[$rValue['id_resource']] = $rValue['name'];
                        }
                    }
                }
            }
        }

        if(!isset($resources) || empty($resources)) {
            $resources = [];
        }

        return $resources;
        /*return $resources = Resources::query()
            ->pluck('name','id_resource');*/
    }

    protected function buildFormHelper($model)
    {
        if(isset(Auth::user()->roles[0]->id)) {
            $userId = Auth::user()->roles[0]->id;
        }

        /*{"resource_type":"Közepes konferencia terem","resource":"Közepes konferencia teremhez kivetítő"}*/

        /* if($userId == 1) {
            $resourceTypesTemp = $this->getResourceTypes($userId);
            $resourcesTemp = $this->getResource($resourceTypesTemp);

            if (is_array($resourceTypesTemp) && sizeof($resourceTypesTemp) > 0) {
                foreach ($resourceTypesTemp as $rTTKey => $rTTValue) {
                    $resourceTypes[$rTTValue] = $rTTValue;
                }
            }

            if (is_array($resourcesTemp) && sizeof($resourcesTemp) > 0) {
                foreach ($resourcesTemp as $rTKey => $rTValue) {
                    $resources[$rTValue] = $rTValue;
                }
            }
        } else {
            $resourceTypesTemp = $this->getResourceTypes($userId);
            $resourcesTemp = $this->getResource($resourceTypesTemp);

            if (is_array($resourceTypesTemp) && sizeof($resourceTypesTemp) > 0) {
                foreach ($resourceTypesTemp as $rTTKey => $rTTValue) {
                    $resourceTypes[$rTTValue] = $rTTValue;
                }
            }

            if (is_array($resourcesTemp) && sizeof($resourcesTemp) > 0) {
                foreach ($resourcesTemp as $rTKey => $rTValue) {
                    $resources[$rTValue] = $rTValue;
                }
            }
        } */

        $responseRequestBase = json_decode(base64_decode(request()->input('response')));

        if(isset($responseRequestBase->resource_type)) {
            $resourceTypes = [
                //eszkoz tipus lekerdezese - input
                $responseRequestBase->resource_type => $responseRequestBase->resource_type
            ];
            $resourceTypesDesign = $responseRequestBase->resource_type;
            session(['resourceTypes' => $resourceTypes]);
            session(['resourceTypesDesign' => $resourceTypesDesign]);
        }

        if(isset($responseRequestBase->resource) && mb_strlen($responseRequestBase->resource) > 0 ) {
            //eszkoz lekerdezese - input, ha nem talalhato meg a feluleten valasztott eszkoz a resource tablaban, akkor
            //az adatbazis lekerdezes lesz a mervado!
            /*$resources = Resources::query()
                ->Name($responseRequestBase->resource_type)
                ->ResourceName($responseRequestBase->resource)
                ->pluck('name', 'name')
                ->toArray();*/

            $resources = Resources::select()
                ->Name($responseRequestBase->resource_type)
                ->ResourceName($responseRequestBase->resource)
                ->get()
                ->toArray();

            session(['resources' => $resources]);

            if(empty($resources)) {
                /*$resources = Resources::query()
                    ->Name($responseRequestBase->resource_type)
                    ->pluck('name', 'name')
                    ->toArray();*/
                $resources = Resources::select()
                    ->Name($responseRequestBase->resource_type)
                    ->get()
                    ->toArray();
                session(['resources' => $resources]);
            }
        } else {
            if(isset($responseRequestBase->resource_type)) {
                /*$resources = Resources::query()
                    ->Name($responseRequestBase->resource_type)
                    ->pluck('name', 'name')
                    ->toArray();*/

                $resources = Resources::select()
                    ->Name($responseRequestBase->resource_type)
                    ->get()
                    ->toArray();

                session(['resources' => $resources]);
            }
        }

        if(!isset($resourceTypes)) {
            $resourceTypes = session('resourceTypes');
            $resourceTypesDesign = session('resourceTypesDesign');
            //$resourceTypes = [];
        }
        if(isset($resources)) {
            //$resources = array_merge(['Kérem válasszon' => 'Kérem válasszon'], (array)$resources);
        } else {
            $resources = session('resources');
            //$resources = [];
        }

        if( Reservation::query()
            ->pluck('id_reservation', 'id_reservation')
            ->toArray() ) {

            $reservationIdMax = max(Reservation::query()
                ->pluck('id_reservation', 'id_reservation')
                ->toArray())+1;
        } else {
            $reservationIdMax = 1;
        }

        $customResourceButton = "";
        //w_31303 - modify FormFieldHelper custom buttons
        if(is_array($resources) && !empty($resources)) {
            $customResourceButton .= "<div class='form-group col-12'>";
            foreach($resources as $resource) {
                $customResourceButton .= "
                    <button
                        class='select_resource_button'
                        style='background: ".$resource["color_code"].";
                           list-style: none;
                           color: white;
                           width: auto;
                           height: 35px;
                           margin-top: 25px;
                           margin-right: 5px;
                           text-align: center;
                           border: 1px;
                           border-radius: 10px;'
                        name='select_reserv_resource'
                        value='".$resource['name']."'
                        type='button'>".$resource['name']."
                    </button>
                ";
            }
            $customResourceButton .= "
                </div>
                <input id='resource' type='hidden' name='resource' value='".$resources[0]['name']."'>"
            ;
        }

        $repeatRoleDiv = '
            <div id="reserv_extend_block_02" class="form-group col-10">
                <div class="row">
                    <div class="col-3 pt-2">
                        <span>Ismétlődési szabályok</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-1 pt-2">
                        <input id="repeat_role_custom" class="form-control" name="repeat_role" value="" type="radio">
                    </div>
                    <div class="col-5 d-flex">
                        <input id="repeat_role" class="form-control" name="repeat_role" type="text" autocomplete="off" value="">
                        <span id="role_input_text" class="ml-2 pt-2">
                            naponta
                        </span>
                    </div>
                    <div class="col-4">
                        <div id="repeat-button-weekday">
                            <label>
                                <input type="radio" id="repeat_role_weekday" class="form-control" style="border: 0px;" title="Minden hétköznap" name="repeat_role" value="role_weekday">
                                <span>Minden hétköznap</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        ';

        $repeatDateCustomForm = '

            <div id="reserv_extend_block_03" class="form-group ml-3" style="">
                <div class="row">
                    <div class="form-group col-5 mt-3">
                        <span class="ml-2 pt-1">
                            Ismétlődés kezdete
                        </span>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <i class="fas fa-redo-alt"></i>
                            </div>
                            <input id="repeat_start" class="form-control flatpickr-input active" name="repeat_start" type="date" autocomplete="off" value="">
                        </div>
                    </div>

                    <div class="form-group col-5 mt-3">
                        <span>
                            Ismétlődés vége
                        </span>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <input id="repeat_role_end_date_check" class="form-control" name="repeat_role_end_count" value="repeat_role_end_date_check" type="radio">
                                <i class="fas fa-redo-alt"></i>
                            </div>
                            <input id="repeat_end" class="form-control flatpickr-input" name="repeat_end" type="date" autocomplete="off" value="">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-1 pt-2">
                        <input id="repeat_role_end_count" class="form-control" name="repeat_role_end_count" value="repeat_role_end_count" type="radio">
                    </div>
                    <span class="ml-2 pt-1">
                        Befejezés
                    </span>
                    <!--<div class="col-5 d-flex pt-2">-->
                    <div class="col-4">
                        <input id="repeat_role_end_count_input" class="form-control" name="repeat_role_end_count_input" type="text" autocomplete="off" value="" disabled="">
                        <!--<span class="pl-2">-->
                        <span>
                            ismétlődés után
                        </span>
                    </div>
                </div>
            </div>
        </div>
        ';

        $form = FormHelper::to('resources', $model, [
            FormCustomHTMLFieldHelper::to('user_id', '
                <input type="hidden" name="user_id" value="'.$userId.'">
            '),
            FormInputFieldHelper::toHidden('resource_type', '')->addClass('d-none')->setColClass('d-none')->setValue($resourceTypesDesign),
            /*FormSelectFieldHelper::to('resource_type', __('Eszköz típus'), $resourceTypes)->setRequired()
                ->setColClass('col-3'),*/
            //FormSelectFieldHelper::to('resource', __('Eszköz'), $resources)->setRequired()->setColClass('col-3'),
            FormCustomHTMLFieldHelper::to('resource', $customResourceButton),
            FormInputFieldHelper::toText('reservation_subject', __('Foglalás oka'))->setRequired()
                ->setColClass('col-6'),
            FormCheckboxFieldHelper::toSwitch('reservation_all_day',__('Egész napos esemény:'))->setColClass('col-3'),
            FormCheckboxFieldHelper::toSwitch('reservation_repeat',__('Ismétlődés:'), 'I')->setElementId('switch_button')->setHint('Ha a foglalás az egy napos időtartamot meghaladja, akkor az ismétlődési opciók elérhetetlenek lesznek!
            Foglalások ugyanarra az eszközre nézve, nem fedhetik egymást, azaz nem tartozhatnak ugyanahhoz az idősávhoz!')->setColClass('col-3'),
            FormInputFieldHelper::toTextarea('reservation_description', __('Leírás'))
                ->setColClass('col-6'),
            FormCustomHTMLFieldHelper::to('repeat_type_01', '
                <div class="form-group col-6">
                    <div id="reserv_extend_block_01" class="form-group">
                        <div class="row">
                            <div class="col-3">
                                <table>
                                    <tr>
                                        <td>
                                            <div id="repeat-button-day">
                                                <label>
                                                    <input type="radio" title="Naponta" id="repeat_day" style="border: 0px" class="repeat_type" class="form-control" name="repeat_type" checked="checked" value="day"><span>Naponta</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
            '),
            FormCustomHTMLFieldHelper::to('repeat_type_02', '
                <div class="col-3">
                    <table>
                        <tr>
                            <td>
                                <div id="repeat-button-week">
                                    <label>
                                        <input type="radio" title="Hetente" id="repeat_week" style="border: 0px" class="repeat_type" class="form-control" name="repeat_type" value="week"><span>Hetente</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            '),
            FormCustomHTMLFieldHelper::to('repeat_type_03', '
                    <div class="col-3">
                        <table>
                            <tr>
                                <td>
                                    <div id="repeat-button-month">
                                        <label>
                                            <input type="radio" title="Havonta" id="repeat_month" style="border: 0px;" class="repeat_type" class="form-control" name="repeat_type" value="month"><span>Havonta</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            '),
            FormCustomHTMLFieldHelper::to('repeat_role_02', $repeatRoleDiv),
            FormCustomHTMLFieldHelper::to('repeat_role_03', $repeatDateCustomForm),
            FormInputFieldHelper::toDateTime('reservation_date_start', __('Foglalás kezdete'))->setRequired()
                ->setColClass('col-3')->setElementId('reservation_date_start_input')->setIconClass('far fa-calendar'),
            FormInputFieldHelper::toDateTime('reservation_date_end', __('Foglalás vége'))->setRequired()
                ->setColClass('col-3')->setElementId('reservation_date_end_input')->setIconClass('far fa-calendar'),
            FormCustomHTMLFieldHelper::to('reservation_repeat_id', '
                <input type="hidden" name="reservation_repeat_id" value="'.$reservationIdMax.'">
            '),
            FormInputFieldHelper::toHidden('id_reservation', '')
        ]);

        //alap tipusok
        /*$repeatResourceType = FormSelectFieldHelper::to('resource_type', __('Eszköz típus'), $resourceTypes)->setRequired()
                                ->setColClass('col-2')->setElementId('resource_type');*/

        //ismetlodesi tipusok
        /*$repeatDay = FormCheckboxFieldHelper::toRadio('repeat_type', 'Naponta', 'day', 'checked')->setColClass('col-3')->setElementId('repeat_day')->setFormHelperInstance($form);
        $repeatWeek = FormCheckboxFieldHelper::toRadio('repeat_type', 'Hetente', 'week')->setColClass('col-3')->setElementId('repeat_week')->setFormHelperInstance($form);
        $repeatMonth = FormCheckboxFieldHelper::toRadio('repeat_type', 'Havonta', 'month')->setColClass('col-3')->setElementId('repeat_month')->setFormHelperInstance($form);*/

        /*$repeatDay = FormCustomHTMLFieldHelper::to('repeat_type', '
            <table>
                <tr>
                    <td>
                        <div id="repeat-button-day">
                            <label>
                                <input type="radio" title="Naponta" id="repeat_day" style="border: 0px" class="repeat_type" class="form-control" name="repeat_type" checked="checked" value="day"><span>Naponta</span>
                            </label>
                        </div>
                    </td>
                    <!--<td><span>Naponta</span></td>-->
                </tr>
            </table>
        ');*/
        /*$repeatWeek = FormCustomHTMLFieldHelper::to('repeat_type', '
            <table>
                <tr>
                    <td>
                        <div id="repeat-button-week">
                            <label>
                                <input type="radio" title="Hetente" id="repeat_week" style="border: 0px" class="repeat_type" class="form-control" name="repeat_type" value="week"><span>Hetente</span>
                            </label>
                        </div>
                    </td>
                    <!--<td><span>Hetente</span></td>-->
                </tr>
            </table>
        ');
        $repeatMonth = FormCustomHTMLFieldHelper::to('repeat_type', '
            <table>
                <tr>
                    <td>
                        <div id="repeat-button-month">
                            <label>
                                <input type="radio" title="Havonta" id="repeat_month" style="border: 0px;" class="repeat_type" class="form-control" name="repeat_type" value="month"><span>Havonta</span>
                            </label>
                        </div>
                    </td>
                    <!--<td><span>Havonta</span></td>-->
                </tr>
            </table>
        ');*/

        //ismetlodesi szabalyok
        /*$repeatRole = FormCheckboxFieldHelper::toRadio('repeat_role', 'Ismétlődési szabály', null)->setElementId('repeat_role_custom')->setFormHelperInstance($form);
        $repeatRoleWeekdays = FormCheckboxFieldHelper::toRadio('repeat_role', '', 'role_weekday')->setElementId('repeat_role_weekday')->setFormHelperInstance($form);
        $roleInput = FormInputFieldHelper::toText('repeat_role', '')->setFormHelperInstance($form);*/

        /*$repeatStartDate = FormInputFieldHelper::toDate('repeat_start', __('Kezdés dátuma'))->setElementId('repeat_start')->setIconClass('fad fa-redo-alt')->setFormHelperInstance($form);
        $repeatDateEndCheckbox = FormCheckboxFieldHelper::toRadio('repeat_role_end_count', 'Befejezés', 'repeat_role_end_count')->setElementId('repeat_role_end_count')->setFormHelperInstance($form);
        $repeatDateEndInput = FormInputFieldHelper::toText('repeat_role_end_count_input', '')->setFormHelperInstance($form);
        $repeatEndDateCheckbox = FormCheckboxFieldHelper::toRadio('repeat_role_end_count', 'Befejezés dátuma', 'repeat_role_end_date_check')->setElementId('repeat_role_end_date_check')->setFormHelperInstance($form);
        $repeatEndDate = FormInputFieldHelper::toDate('repeat_end', __('Befejezés dátuma'))->setElementId('repeat_end')->setIconClass('fad fa-redo-alt')->setFormHelperInstance($form);*/

         $customFields = FormCustomHTMLFieldHelper::to('customFields', view('reservation.partials.reservation_form_custom_fields', [
             /*'repeatDay' => $repeatDay,
             'repeatWeek' => $repeatWeek,
             'repeatMonth' => $repeatMonth,*/
             /*'repeatRole' => $repeatRole,
             'repeatRoleWeekdays' => $repeatRoleWeekdays,
             'roleInput' => $roleInput,*/
             /*'repeatStartDate' => $repeatStartDate,
             'repeatDateEndCheckbox' => $repeatDateEndCheckbox,
             'repeatDateEndInput' => $repeatDateEndInput,
             'repeatEndDateCheckbox' => $repeatEndDateCheckbox,
             'repeatEndDate' => $repeatEndDate*/
        ]));

        //$form->addField($defaultFields);
        $form->addField($customFields);

        //return $form->setFullPage(false);
        return $form;

    }

    protected function buildListHelper()
    {
        //TODO: not used
    }

    /**
     * Index view calendar
     * Get database () data in calendar view
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void $reservationCalendar
     */

    public function view(Request $request) {

        $reservationCalendarTemp = [];
        $reservationCalendar = [];

        if (isset($request->resource_type)) {
            //always required
            $resourceType = $request->resource_type;
        }

        if (isset($request->resource) && $request->resource !== null) {
            $resource = $request->resource;

            $reservationCalendarTemp = DB::table('reservation')
                ->leftJoin('reservation_repeat', 'reservation.reservation_repeat_id', '=', 'reservation_repeat.reservation_repeat_id')
                ->where('reservation.resource_type', '=', $resourceType)
                ->where('reservation.resource', '=', $resource)
                ->get()
                ->toArray();
        } else {
            $reservationCalendarTemp = DB::table('reservation')
                ->leftJoin('reservation_repeat', 'reservation.reservation_repeat_id', '=', 'reservation_repeat.reservation_repeat_id')
                ->where('reservation.resource_type', '=', $resourceType)
                ->get()
                ->toArray();
        }

        if ($reservationCalendarTemp !== NULL) {
            foreach ($reservationCalendarTemp as $resKey => $resValue) {
                //$reservationCalendar[] = $resValue;
                $reservationCalendar = [
                    //'id_reservation' => $resValue->id_reservation,
                    'title' => $resValue->reservation_subject,
                    'start' => $resValue->reservation_date_start,
                    'end' => $resValue->reservation_date_end,
                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code
                ];
            }
        }

        if($reservationCalendar) {
            return response()->json(
                [
                    'reservation' => [
                        'resource_type' => $resourceType,
                        'resource' => isset($resource) ? $resource : ''
                    ]
                ]
            );
        } else {
            return response()->json(
                [
                    'reservation' => [
                        'resource_type' => $resourceType,
                        'resource' => isset($resource) ? $resource : null
                    ]
                ]
            );
        }

    }

    /**
     * Calendar
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */

    public function viewCalendar(Request $request) {

        app('Presenter')->addCSS('custom_reservation.css');

        $modalHeaders =
        [
            'insert' => 'Új foglalás rögzítése',
            'update' => 'Foglalás módosítása',
        ];
        app('Presenter')->addJsVar('reservationModal_header', $modalHeaders);

        $formHelper = $this->buildFormHelper(null)->setActionFromNamedRoute('insertReservation');
            //->addField(FormInputFieldHelper::toHidden('id_reservation', '')); //extra mezo bovitese az eredeti modal formnak
        $this->modal = Modal::modal('reservationModal', $modalHeaders['insert'], view('reservation.reservation_modal_form', [ 'formHelper' => $formHelper ]), 'primary', 'confirm', false)
            ->addButton('url',__('Foglalás rögzítése'), 'addReservSaveButton btn-primary', '', '')
            ->addButton('url',__('Foglalás törlése'), 'addReservDeleteButton btn-primary', '', '')
            //->addButton('cancel',__('Kilépés'), '',  '#')
            ->addClass('custom-reservation-modal');

        if(isset($request->input()['response'])) {
            if(isset($request->input()['response'])) {
                $responseJs = base64_decode(trim($request->input()['response']));
                session(['response' => $request->input()['response']]);
            }
        } else {
            $responseJs = base64_decode(trim(session('response')));
        }

        if(json_decode($responseJs)->resource_type !== null){
            $resourceType = json_decode($responseJs)->resource_type;
        }

        if($resourceType && Auth::user()->roles[0]->id !== 1) {
            $resources = Resources::query()
                ->Name($resourceType)
                ->get()
                ->toArray();
        } else {
            //$resources = Resources::all()->toArray();
            $resources = Resources::query()
                ->Name($resourceType)
                ->get()
                ->toArray();
        }

        app('Presenter')->addJsVar('getResource', $resources);

        $responseJs = preg_replace("/^\"|^\'/", "", $responseJs);
        $responseJs = preg_replace("/\"$|\'$/", "", $responseJs);

        return view('resource.resourcesCalendar', [
            //'calendar' => json_decode($responseJs),
            'calendar' => $responseJs,
            'resources' => $resources
        ]);
    }

    /**
     * Calendar Update modal form
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */

    public function viewUpdateCalendar(Request $request) {

        if($request->query('reservation') !== null) {
            $reservation = json_decode(base64_decode($request->query('reservation')));
        }

        foreach ($reservation as $key => $value) {
            $reservationArray[$key] = $value;
        }

        app('Presenter')->addCSS('custom_reservation.css');
        //$formHelper = $this->buildFormHelper(null)->setActionFromNamedRoute('updateReservation'); //TODO!
        $formHelper = $this->buildFormHelper(null);
        $this->modal = Modal::modal('reservationUpdateModal', 'Foglalás módosítása', view('reservation.reservation_update_modal_form', [ 'formHelper' => $formHelper, 'reservation' => $reservationArray ]), 'primary', 'confirm', false)
            ->addButton('url',__('Módosítás'), 'addReservUpdateButton btn-primary', '', '')
            ->addButton('cancel',__('Kilépés'), '', '')
            ->addClass('custom-reservation-modal');

        $resources = Resources::all()->toArray();
    }

    /**
     * Get all Calendar events by resource_type and/or resources?!
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function getFullCalendarEvents(Request $request) {

        DB::enableQueryLog();

        $isRepeatData = false;
        $startDate = explode("T", $request->get('start',Carbon::parse('first day of this month')));
        $endDate = explode("T", $request->get('end',Carbon::parse('last day of this month')));
        $resources = trim($request->get('resources')) ? json_decode($request->get('resources')) : "";

        if(isset($resources->resource) && mb_strlen($resources->resource) > 0) {
            //resource_type minden esetben kotelezo, resource opcionalis!
            $reservationCalendarTemp = DB::table('reservation')
                ->leftJoin('reservation_repeat', 'reservation.reservation_repeat_id', '=', 'reservation_repeat.reservation_repeat_id')
                ->where('reservation.resource_type', '=', $resources->resource_type)
                ->where('reservation.resource', '=', $resources->resource)
                ->get()
                ->toArray();
        } else {
            $reservationCalendarTemp = DB::table('reservation')
                ->leftJoin('reservation_repeat', 'reservation.reservation_repeat_id', '=', 'reservation_repeat.reservation_repeat_id')
                ->where('reservation.resource_type', '=', $resources->resource_type)
                ->get()
                ->toArray();
        }

        if ($reservationCalendarTemp !== NULL) {
            foreach ($reservationCalendarTemp as $resKey => $resValue) {
                //02.15: lekerdezesek szetbontasa
                if($resValue->repeat_type == 'day' && !$resValue->repeat_role && $resValue->repeat_start && $resValue->repeat_end) {
                    //Datumtartomany: kezdes - befejezes datuma vizsgalat
                    $reservationDateStart =  Carbon::parse($resValue->reservation_date_start);
                    $reservationDateEnd =  Carbon::parse($resValue->reservation_date_end);
                    $reservationRepeatDateStart = Carbon::parse($resValue->repeat_start);
                    $reservationRepeatDateEnd = Carbon::parse($resValue->repeat_end);

                    //$reservationRepeatPeriod = CarbonPeriod::make($reservationRepeatDateStart, '1 day', $reservationRepeatDateEnd);
                    /*$explodeStartDate = explode(" ", $resValue->reservation_date_start);
                    $explodeEndDate = explode(" ", $resValue->reservation_date_end);*/

                    $reservationRepeatDiffInDays = $reservationDateStart->diffInDays($reservationRepeatDateEnd);

                    $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);

                    for ($t = 0; $t <= $reservationRepeatDiffInDays+1; $t++) {

                        if($t == 0) {
                            $reservationDateStart->add('0 day');
                            $reservationDateEnd->add('0 day');
                        } /*elseif ($t == $reservationRepeatDiffInDays) {
                            $reservationDateStart->add('2 day');
                            $reservationDateEnd->add('2 day');
                        }*/ else {
                            $reservationDateStart->add('1 day');
                            $reservationDateEnd->add('1 day');
                        }

                        if($t == 0) {
                            $reservationCalendar[] = [
                                'id' => $resValue->id_reservation,
                                'title' => $resValue->reservation_subject,
                                'start' => $reservationDateStart->format('Y-m-d H:i:s'),
                                'end' => $reservationDateEnd->format('Y-m-d H:i:s'),
                                'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                'textColor' => 'white',
                                'reserved' => $resValue->repeat_reserved
                            ];
                        } else {
                            if($repeatDatas !== null && in_array($reservationDateStart->format('Y-m-d'), array_unique($repeatDatas))) {
                                $isRepeatData = true;
                            } else {
                                $isRepeatData = false;
                            }

                            if(!$isRepeatData) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    'start' => $reservationDateStart->format('Y-m-d H:i:s'),
                                    'end' => $reservationDateEnd->format('Y-m-d H:i:s'),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white',
                                    'resourceEditable' => false,
                                    'reserved' => $resValue->repeat_reserved
                                ];
                            } else {
                                $reservationCalendar[] = [
                                    'eventDisplay' => 'none'
                                ];
                            }
                        }
                    }
                } else if($resValue->repeat_type == 'day' && $resValue->repeat_role && $resValue->repeat_role !== 'role_weekday' && $resValue->repeat_start && $resValue->repeat_end) {
                    //Datumtartomany: kezdes - befejezes datuma vizsgalat + role
                    $reservationDateStart =  Carbon::parse($resValue->reservation_date_start);
                    $reservationDateEnd =  Carbon::parse($resValue->reservation_date_end);
                    $reservationRepeatDateStart = Carbon::parse($resValue->repeat_start);
                    $reservationRepeatDateEnd = Carbon::parse($resValue->repeat_end);

                    $reservationRepeatDiffInDays = $reservationDateStart->diffInDays($reservationRepeatDateEnd);

                    $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);

                    for ($t = 0; $t <= $reservationRepeatDiffInDays+1; $t++) {

                        if($t == 0) {
                            $reservationDateStart->add('0 day');
                            $reservationDateEnd->add('0 day');
                        } else {
                            $reservationDateStart->add((int)$resValue->repeat_role . ' day');
                            $reservationDateEnd->add((int)$resValue->repeat_role . ' day');
                        }

                        if($reservationDateStart->format('Y-m-d') <= $resValue->repeat_end) {

                            if($t == 0) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    'start' => $reservationDateStart->format('Y-m-d H:i:s'),
                                    'end' => $reservationDateEnd->format('Y-m-d H:i:s'),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white'
                                ];
                            } else {
                                if($repeatDatas !== null && in_array($reservationDateStart->format('Y-m-d'), array_unique($repeatDatas))) {
                                    $isRepeatData = true;
                                } else {
                                    $isRepeatData = false;
                                }

                                if(!$isRepeatData) {
                                    $reservationCalendar[] = [
                                        'id' => $resValue->id_reservation,
                                        'title' => $resValue->reservation_subject,
                                        'start' => $reservationDateStart->format('Y-m-d H:i:s'),
                                        'end' => $reservationDateEnd->format('Y-m-d H:i:s'),
                                        'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                        'textColor' => 'white',
                                        'resourceEditable' => false
                                    ];
                                } else {
                                    $reservationCalendar[] = [
                                        'eventDisplay' => 'none'
                                    ];
                                }
                            }
                        }
                    }
                } else if($resValue->repeat_type == 'week' && $resValue->repeat_start && $resValue->repeat_end) {
                        //$reservationRepeatWeekDateStart = Carbon::parse($resValue->reservation_date_start);
                        $reservationRepeatWeekDateStart = Carbon::parse($resValue->repeat_start);
                        $reservationRepeatWeekDateEnd = Carbon::parse($resValue->repeat_end);
                        /*$reservationRepeatWeekDateStart->add('1 week');
                        $reservationRepeatWeekDateEnd->add('1 week');*/

                        $reservationRepeatDiffInWeeks = $reservationRepeatWeekDateStart->diffInWeeks($reservationRepeatWeekDateEnd);

                        $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);

                        for ($t = 0; $t <= $reservationRepeatDiffInWeeks; $t++) {

                            if($t == 0) {
                                $defaultTSDateStartMultiple = strtotime('+0 week', strtotime($resValue->reservation_date_start));
                                $defaultTSDateEndMultiple = strtotime('+0 week', strtotime($resValue->reservation_date_end));
                            } else {
                                if(date("Y-m-d", $defaultTSDateStartMultiple) <= $resValue->repeat_end) {
                                    if ($resValue->repeat_role) {
                                        /*$defaultTSDateStartMultiple = strtotime('+' . $resValue->repeat_role*$resValue->repeat_role . ' week', strtotime($resValue->reservation_date_start));
                                        $defaultTSDateEndMultiple = strtotime('+' . $resValue->repeat_role*$resValue->repeat_role . ' week', strtotime($resValue->reservation_date_end));*/
                                        $defaultTSDateStartMultiple = strtotime('+' . $resValue->repeat_role . ' week', $defaultTSDateStartMultiple);
                                        $defaultTSDateEndMultiple = strtotime('+' . $resValue->repeat_role . ' week', $defaultTSDateEndMultiple);
                                    } else {
                                        $defaultTSDateStartMultiple = strtotime('+' . $t . ' week', $defaultTSDateStartMultiple);
                                        $defaultTSDateEndMultiple = strtotime('+' . $t . ' week', $defaultTSDateEndMultiple);
                                    }
                                }
                            }

                            if($t == 0) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    'start' => date("Y-m-d H:i:s", $defaultTSDateStartMultiple),
                                    'end' => date("Y-m-d H:i:s", $defaultTSDateEndMultiple),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white',
                                ];
                            } else {
                                if(date("Y-m-d", $defaultTSDateStartMultiple) <= $resValue->repeat_end) {
                                    if($repeatDatas !== null && in_array(date("Y-m-d", $defaultTSDateStartMultiple), array_unique($repeatDatas))) {
                                        $isRepeatData = true;
                                    } else {
                                        $isRepeatData = false;
                                    }
                                    if(!$isRepeatData) {
                                        $reservationCalendar[] = [
                                            'id' => $resValue->id_reservation,
                                            'title' => $resValue->reservation_subject,
                                            'start' => date("Y-m-d H:i:s", $defaultTSDateStartMultiple),
                                            'end' => date("Y-m-d H:i:s", $defaultTSDateEndMultiple),
                                            'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                            'textColor' => 'white',
                                            'resourceEditable' => false
                                        ];
                                    } else {
                                        $reservationCalendar[] = [
                                            'eventDisplay' => 'none'
                                        ];
                                    }
                                }
                            }
                        }
                } else if ($resValue->repeat_type == 'day' && $resValue->repeat_role_end_count_input) {
                    $reservationDateStart =  Carbon::parse($resValue->reservation_date_start);

                    if($resValue->repeat_end) {
                        //nem lehetseges eset!
                        $reservationRepeatDateEnd = Carbon::parse($resValue->repeat_end);
                    } else {
                        $reservationDateEnd = Carbon::parse($resValue->reservation_date_end);
                    }

                    $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);

                    for ($t = 0; $t <= $resValue->repeat_role_end_count_input; $t++) {

                        if($resValue->repeat_role) {
                            //szerepelnek ismetlodesi szabalyok: hany naponta, hetente, vagy havonta
                            $repeatRole = $resValue->repeat_role;
                        }

                        if($t == 0) {
                            $reservationDateStart->add('0 day');
                            $reservationDateEnd->add('0 day');
                        } else {
                            if($repeatRole && $repeatRole !== 'role_weekday') {
                                $reservationDateStart->add($repeatRole.' day');
                                $reservationDateEnd->add($repeatRole.' day');
                            } else {
                                $reservationDateStart->add('1 day');
                                $reservationDateEnd->add('1 day');
                            }
                        }

                        if($t == 0) {
                            $reservationCalendar[] = [
                                'id' => $resValue->id_reservation,
                                'title' => $resValue->reservation_subject,
                                /*'start' => $resValue->reservation_date_start,
                                'end' => $reservationDateStart->add($resValue->repeat_role_end_count_input . ' day'),*/
                                'start' => $reservationDateStart->format('Y-m-d H:i:s'),
                                'end' => $reservationDateEnd->format('Y-m-d H:i:s'),
                                'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                'textColor' => 'white'
                            ];
                        } else {
                            if($repeatDatas !== null && in_array($reservationDateStart->format('Y-m-d'), array_unique($repeatDatas))) {
                                $isRepeatData = true;
                            } else {
                                $isRepeatData = false;
                            }

                            if(!$isRepeatData) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    /*'start' => $resValue->reservation_date_start,
                                    'end' => $reservationDateStart->add($resValue->repeat_role_end_count_input . ' day'),*/
                                    'start' => $reservationDateStart->format('Y-m-d H:i:s'),
                                    'end' => $reservationDateEnd->format('Y-m-d H:i:s'),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white',
                                    'resourceEditable' => false
                                ];
                            } else {
                                $reservationCalendar[] = [
                                    'eventDisplay' => 'none'
                                ];
                            }
                        }
                    }
                } else if ($resValue->repeat_type == 'week' && $resValue->repeat_role_end_count_input) {
                    $reservationDateStart = Carbon::parse($resValue->reservation_date_start);

                    $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);

                    for ($t = 0; $t <= $resValue->repeat_role_end_count_input; $t++) {
                        $defaultTSDateStartMultiple = strtotime('+' . $t . ' week', strtotime($resValue->reservation_date_start));
                        $defaultTSDateEndMultiple = strtotime('+' . $t . ' week', strtotime($resValue->reservation_date_end));

                        if($t == 0) {
                            $reservationCalendar[] = [
                                'id' => $resValue->id_reservation,
                                'title' => $resValue->reservation_subject,
                                'start' => date("Y-m-d H:i:s", $defaultTSDateStartMultiple),
                                'end' => date("Y-m-d H:i:s", $defaultTSDateEndMultiple),
                                'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                'textColor' => 'white'
                            ];
                        } else {
                            if($repeatDatas !== null && in_array(date("Y-m-d", $defaultTSDateStartMultiple), array_unique($repeatDatas))) {
                                $isRepeatData = true;
                            } else {
                                $isRepeatData = false;
                            }

                            if(!$isRepeatData) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    'start' => date("Y-m-d H:i:s", $defaultTSDateStartMultiple),
                                    'end' => date("Y-m-d H:i:s", $defaultTSDateEndMultiple),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white',
                                    'resourceEditable' => false
                                ];
                            } else {
                                $reservationCalendar[] = [
                                    'eventDisplay' => 'none'
                                ];
                            }
                        }
                    }
                } else if($resValue->repeat_type == 'month' && $resValue->repeat_start && $resValue->repeat_end && !$resValue->repeat_role) {
                        $reservationRepeatMonthDateStart = Carbon::parse($resValue->repeat_start);
                        $reservationRepeatMonthDateEnd = Carbon::parse($resValue->repeat_end);

                        $reservationRepeatDiffInMonth = $reservationRepeatMonthDateStart->diffInMonths($reservationRepeatMonthDateEnd);

                        $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);

                        for ($t = 0; $t <= $reservationRepeatDiffInMonth; $t++) {
                            $defaultTSDateStartMultiple = strtotime('+' . $t . ' months', strtotime($resValue->reservation_date_start));
                            $defaultTSDateEndMultiple = strtotime('+' . $t . ' months', strtotime($resValue->reservation_date_end));

                            if($t == 0) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    'start' => date("Y-m-d H:i:s", $defaultTSDateStartMultiple),
                                    'end' => date("Y-m-d H:i:s", $defaultTSDateEndMultiple),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white'
                                ];
                            } else {
                                if($repeatDatas !== null && in_array(date("Y-m-d", $defaultTSDateStartMultiple), array_unique($repeatDatas))) {
                                    $isRepeatData = true;
                                } else {
                                    $isRepeatData = false;
                                }

                                if(!$isRepeatData) {
                                    $reservationCalendar[] = [
                                        'id' => $resValue->id_reservation,
                                        'title' => $resValue->reservation_subject,
                                        'start' => date("Y-m-d H:i:s", $defaultTSDateStartMultiple),
                                        'end' => date("Y-m-d H:i:s", $defaultTSDateEndMultiple),
                                        'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                        'textColor' => 'white',
                                        'resourceEditable' => false
                                    ];
                                } else {
                                    $reservationCalendar[] = [
                                        'eventDisplay' => 'none'
                                    ];
                                }
                            }
                        }
                } else if ($resValue->repeat_type == 'month' && $resValue->repeat_role_end_count_input) {
                    $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);
                    for ($t = 0; $t <= $resValue->repeat_role_end_count_input; $t++) {

                        if($resValue->repeat_role) {
                            if($t == 0) {
                                $defaultTSDateStartMultiple = strtotime('+' . $t . ' months', strtotime($resValue->reservation_date_start));
                                $defaultTSDateEndMultiple = strtotime('+' . $t . ' months', strtotime($resValue->reservation_date_end));
                            } else {
                                $defaultTSDateStartMultiple = strtotime('+' . $resValue->repeat_role . ' months', strtotime($resValue->reservation_date_start));
                                $defaultTSDateEndMultiple = strtotime('+' . $resValue->repeat_role . ' months', strtotime($resValue->reservation_date_end));
                            }
                        } else {
                            $defaultTSDateStartMultiple = strtotime('+' . $t . ' months', strtotime($resValue->reservation_date_start));
                            $defaultTSDateEndMultiple = strtotime('+' . $t . ' months', strtotime($resValue->reservation_date_end));
                        }

                        if($t == 0) {
                            $reservationCalendar[] = [
                                'id' => $resValue->id_reservation,
                                'title' => $resValue->reservation_subject,
                                'start' => date("Y-m-d H:i:s", $defaultTSDateStartMultiple),
                                'end' => date("Y-m-d H:i:s", $defaultTSDateEndMultiple),
                                'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                'textColor' => 'white'
                            ];
                        } else {
                            if($repeatDatas !== null && in_array(date("Y-m-d", $defaultTSDateStartMultiple), array_unique($repeatDatas))) {
                                $isRepeatData = true;
                            } else {
                                $isRepeatData = false;
                            }

                            if(!$isRepeatData) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    'start' => date("Y-m-d H:i:s", $defaultTSDateStartMultiple),
                                    'end' => date("Y-m-d H:i:s", $defaultTSDateEndMultiple),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white',
                                    'resourceEditable' => false
                                ];
                            } else {
                                $reservationCalendar[] = [
                                    'eventDisplay' => 'none'
                                ];
                            }
                        }

                    }
                } else if ($resValue->repeat_type == 'day' && preg_match("/[0-9]{1,}/", $resValue->repeat_role) && $resValue->repeat_end) {
                    $reservationRepeatDayDateStart = Carbon::parse($resValue->reservation_date_start);
                    $reservationRepeatDayDateEnd = Carbon::parse($resValue->repeat_end);
                    $reservationRepeatDiffInDay = $reservationRepeatDayDateStart->diffInDays($reservationRepeatDayDateEnd);

                    $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);

                    for ($t = 0; $t <= $reservationRepeatDiffInDay; $t++) {
                        $defaultTSDateStartDayMultiple = strtotime('+' . ($t*$resValue->repeat_role) . ' day', strtotime($resValue->reservation_date_start));
                        $defaultTSDateEndDayMultiple = strtotime('+' . ($t*$resValue->repeat_role) . ' day', strtotime($resValue->reservation_date_end));

                        if(date("Y-m-d", $defaultTSDateStartDayMultiple) <= $resValue->repeat_end) {

                            if($t == 0) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    'start' => date("Y-m-d H:i:s", $defaultTSDateStartDayMultiple),
                                    'end' => date("Y-m-d H:i:s", $defaultTSDateEndDayMultiple),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white'
                                ];
                            } else {

                                if($repeatDatas !== null && in_array(date("Y-m-d", $defaultTSDateStartDayMultiple), array_unique($repeatDatas))) {
                                    $isRepeatData = true;
                                } else {
                                    $isRepeatData = false;
                                }

                                if(!$isRepeatData) {
                                    $reservationCalendar[] = [
                                        'id' => $resValue->id_reservation,
                                        'title' => $resValue->reservation_subject,
                                        'start' => date("Y-m-d H:i:s", $defaultTSDateStartDayMultiple),
                                        'end' => date("Y-m-d H:i:s", $defaultTSDateEndDayMultiple),
                                        'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                        'textColor' => 'white',
                                        'resourceEditable' => false
                                    ];
                                } else {
                                    $reservationCalendar[] = [
                                        'eventDisplay' => 'none'
                                    ];
                                }
                            }
                        }
                    }
                } else if ($resValue->repeat_type == 'day' && $resValue->repeat_role == 'role_weekday' && $resValue->repeat_start && $resValue->repeat_end) {
                    // weekday 0302
                    $reservationDefaultRepeatDayDateStart = Carbon::parse($resValue->reservation_date_start);
                    $reservationDefaultRepeatDayDateEnd = Carbon::parse($resValue->reservation_date_end);

                    $reservationRepeatDayDateEnd = Carbon::parse($resValue->repeat_end);

                    $reservationRepeatDiffInDay = $reservationDefaultRepeatDayDateStart->diffInDays($reservationRepeatDayDateEnd);

                    $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);

                    for ($t = 0; $t <= $reservationRepeatDiffInDay+1; $t++) {

                        if ($t == 0) {
                            $reservationDefaultRepeatDayDateStart->add('0 day');
                            $reservationDefaultRepeatDayDateEnd->add('0 day');
                        } else {
                            $reservationDefaultRepeatDayDateStart->add('1 day');
                            $reservationDefaultRepeatDayDateEnd->add('1 day');
                        }

                        //if($reservationDefaultRepeatDayDateStart->isWeekday() && $resValue->reservation_date_start <= $resValue->repeat_end) {
                        if($reservationDefaultRepeatDayDateStart->isWeekday() && $reservationDefaultRepeatDayDateStart->format('Y-m-d') <= $resValue->repeat_end) {

                            if ($t == 0) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    'start' => $reservationDefaultRepeatDayDateStart->format('Y-m-d H:i:s'),
                                    'end' => $reservationDefaultRepeatDayDateEnd->format('Y-m-d H:i:s'),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white'
                                ];
                            } else {
                                if($repeatDatas !== null && in_array($reservationDefaultRepeatDayDateStart->format('Y-m-d'), array_unique($repeatDatas))) {
                                    $isRepeatData = true;
                                } else {
                                    $isRepeatData = false;
                                }

                                if(!$isRepeatData) {
                                    $reservationCalendar[] = [
                                        'id' => $resValue->id_reservation,
                                        'title' => $resValue->reservation_subject,
                                        'start' => $reservationDefaultRepeatDayDateStart->format('Y-m-d H:i:s'),
                                        'end' => $reservationDefaultRepeatDayDateEnd->format('Y-m-d H:i:s'),
                                        'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                        'textColor' => 'white',
                                        'resourceEditable' => false
                                    ];
                                } else {
                                    $reservationCalendar[] = [
                                        'eventDisplay' => 'none'
                                    ];
                                }
                            }
                        }
                    }
                } else if ($resValue->repeat_type == 'week' && $resValue->repeat_role && $resValue->repeat_end) {
                    $reservationRepeatWeekDateStart = Carbon::parse($resValue->reservation_date_start);
                    $reservationRepeatWeekDateEnd = Carbon::parse($resValue->repeat_end);
                    $reservationRepeatDiffInWeek = $reservationRepeatWeekDateStart->diffInWeeks($reservationRepeatWeekDateEnd);

                    $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);

                    for ($t = 0; $t <= $reservationRepeatDiffInWeek; $t++) {
                        $defaultTSDateStartWeekMultiple = strtotime('+' . ($t*$resValue->repeat_role) . ' week', strtotime($resValue->reservation_date_start));
                        $defaultTSDateEndWeekMultiple = strtotime('+' . ($t*$resValue->repeat_role) . ' week', strtotime($resValue->reservation_date_end));

                        if(date("Y-m-d", $defaultTSDateStartWeekMultiple) <= $resValue->repeat_end) {
                            if($t == 0) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    'start' => date("Y-m-d H:i:s", $defaultTSDateStartWeekMultiple),
                                    'end' => date("Y-m-d H:i:s", $defaultTSDateEndWeekMultiple),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white'
                                ];
                            } else {
                                if($repeatDatas !== null && in_array(date("Y-m-d", $defaultTSDateStartWeekMultiple), array_unique($repeatDatas))) {
                                    $isRepeatData = true;
                                } else {
                                    $isRepeatData = false;
                                }

                                if(!$isRepeatData) {
                                    $reservationCalendar[] = [
                                        'id' => $resValue->id_reservation,
                                        'title' => $resValue->reservation_subject,
                                        'start' => date("Y-m-d H:i:s", $defaultTSDateStartWeekMultiple),
                                        'end' => date("Y-m-d H:i:s", $defaultTSDateEndWeekMultiple),
                                        'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                        'textColor' => 'white',
                                        'resourceEditable' => false
                                    ];
                                } else {
                                    $reservationCalendar[] = [
                                        'eventDisplay' => 'none'
                                    ];
                                }
                            }
                        }
                    }
                } else if ($resValue->repeat_type == 'month' && $resValue->repeat_role && $resValue->repeat_end) {
                    $reservationRepeatMonthDateStart = Carbon::parse($resValue->reservation_date_start);
                    $reservationRepeatMonthDateEnd = Carbon::parse($resValue->repeat_end);
                    $reservationRepeatDiffInMonth = $reservationRepeatMonthDateStart->diffInMonths($reservationRepeatMonthDateEnd);

                    $repeatDatas = $this->decodeUnserializeData($resValue->repeat_desc);

                    for ($t = 0; $t <= $reservationRepeatDiffInMonth; $t++) {
                        $defaultTSDateStartMonthMultiple = strtotime('+' . ($t*$resValue->repeat_role) . ' months', strtotime($resValue->reservation_date_start));
                        $defaultTSDateEndMonthMultiple = strtotime('+' . ($t*$resValue->repeat_role) . ' months', strtotime($resValue->reservation_date_end));

                        if(date("Y-m-d", $defaultTSDateStartMonthMultiple) <= $resValue->repeat_end) {

                            if($t == 0) {
                                $reservationCalendar[] = [
                                    'id' => $resValue->id_reservation,
                                    'title' => $resValue->reservation_subject,
                                    'start' => date("Y-m-d H:i:s", $defaultTSDateStartMonthMultiple),
                                    'end' => date("Y-m-d H:i:s", $defaultTSDateEndMonthMultiple),
                                    'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                    'textColor' => 'white'
                                ];
                            } else {
                                if($repeatDatas !== null && in_array(date("Y-m-d", $defaultTSDateStartMonthMultiple), array_unique($repeatDatas))) {
                                    $isRepeatData = true;
                                } else {
                                    $isRepeatData = false;
                                }

                                if(!$isRepeatData) {
                                    $reservationCalendar[] = [
                                        'id' => $resValue->id_reservation,
                                        'title' => $resValue->reservation_subject,
                                        'start' => date("Y-m-d H:i:s", $defaultTSDateStartMonthMultiple),
                                        'end' => date("Y-m-d H:i:s", $defaultTSDateEndMonthMultiple),
                                        'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                                        'textColor' => 'white',
                                        'resourceEditable' => false
                                    ];
                                } else {
                                    $reservationCalendar[] = [
                                        'eventDisplay' => 'none'
                                    ];
                                }
                            }
                        }
                    }
                } else {
                    $reservationCalendar[] = [
                        'id' => $resValue->id_reservation,
                        'title' => $resValue->reservation_subject,
                        'start' => $resValue->reservation_date_start,
                        'end' => $resValue->reservation_date_end,
                        'color' => Resources::select('color_code')->ColorCode($resValue->resource)->first()->color_code,
                        'textColor' => 'white'
                    ];

                }
            }
        }

        if(isset($reservationCalendar)) {
            foreach ($reservationCalendar as $resI => $resV) {
                foreach ($resV as $reservI => $reservV) {
                    if($reservI == 'title') {
                        //title hossz atalakitas
                        if(mb_strlen($reservV) >= 30 ) {
                            $reservationCalendar[$resI]['title'] = mb_substr($reservationCalendar[$resI]['title'], 0, 30) . '...';
                        }
                    }
                }
            }

            return $reservationCalendar;
        } else {
            return [];
        }
    }

    /**
     * Repeat data decoded method
     *
     * @param   string  $codedString coded string
     *
     * @return   array   decoded array data
     */
    public function decodeUnserializeData($codedString) {
        if(isset($codedString) && mb_strlen($codedString) > 0) {
            $decodedData = base64_decode($codedString);
            $decodedArrayData = unserialize($decodedData);

            if($decodedArrayData) {
                return $decodedArrayData;
            } else {
                return false;
            }
        }
    }

    /**
     * Get calendar events by Id
     *
     * @param Request $request
     * @return AjaxResponse
     */
    public function getFullCalendarEventsById(Request $request) {

        DB::enableQueryLog();

        if(isset($request->id_reservation)) {
            $idReserv = $request->id_reservation;
        }

        $reservation = collect();

        if(isset($idReserv) && mb_strlen($idReserv) > 0) {
            $reservation = DB::table('reservation')
                ->leftJoin('reservation_repeat', 'reservation.reservation_repeat_id', '=', 'reservation_repeat.reservation_repeat_id')
                ->where('reservation.id_reservation', '=', $idReserv)
                ->get();
                //->toArray();
        }

        $checkFields = [
            'repeat_type' => 'repeat_',
            'reservation_all_day' => '',
            'reservation_repeat' => ''
        ];

        return AjaxResponse::make(['reservation_data' => $reservation, 'check_fields' => $checkFields], []);

    }

    /**
     * get reservation by end date
     *
     * @param Request $request
     * @return AjaxResponse
     */
    public function postReservationByDate(Request $request) {

        DB::enableQueryLog();

        if(isset($request->resource_type)) {
            $reservationResourceType = $request->resource_type;
        }
        if(isset($request->resource)) {
            $reservationResource = $request->resource;
        }
        if(isset($request->date_start)) {
            $reservationStartDate = $request->date_start.":00";
        }
        if(isset($request->date_end)) {
            $reservationEndDate = $request->date_end.":00";
        }

        $reservationResponse = collect();

        switch ($request->event) {
            case "S": //only start_date
                if( isset($reservationStartDate) && mb_strlen($reservationStartDate) > 0 ) {
                    $reservationResponse = DB::table('reservation')
                        ->leftJoin('reservation_repeat', 'reservation.reservation_repeat_id', '=', 'reservation_repeat.reservation_repeat_id')
                        ->where('reservation.resource', '=', $reservationResource)
                        //->where('reservation.reservation_date_start', '<=', $reservationStartDate)
                        ->where('reservation.reservation_date_start', '=', $reservationStartDate)
                        ->get();
                }
                break;
            case "E": //only end_date
                if( isset($reservationEndDate) && mb_strlen($reservationEndDate) > 0 ) {
                    $reservationResponse = DB::table('reservation')
                        ->leftJoin('reservation_repeat', 'reservation.reservation_repeat_id', '=', 'reservation_repeat.reservation_repeat_id')
                        ->where('reservation.resource', '=', $reservationResource)
                        ->where('reservation.reservation_date_start', '<=', $reservationStartDate)
                        ->where('reservation.reservation_date_end', '>=', $reservationEndDate)
                        ->get();
                }
                break;
            default:
                break;
        }

        return AjaxResponse::make(['reservation_data' => $reservationResponse], []);

    }

    /**
     * Override Save reservation resource
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws Exception
     */
    public function postCheckCalendarReservation()
    {
        if ( request()->input('reservation_date_start') !== null && request()->input('reservation_date_end') !== null ) {
            //adott idosavban ne lehessen ugyanarra az eszkozre es eszkoztipusra foglalni
            if (request()->input('resource_type') !== null) {
                $resourceTypeCheck = trim(request()->input('resource_type'));
            }
            if (request()->input('resource') !== null) {
                $resourceCheck = trim(request()->input('resource'));
            }

            if( request('reservation_repeat') !== null ) {
                //korabbi megvalositasbol - jelenleg nem hasznalt!!
                $checkReturn = $this->checkReservation(trim(request()->input('reservation_date_start')),
                    trim(request()->input('reservation_date_end')),
                    //trim(request()->input('repeat_end')),
                    $resourceTypeCheck,
                    $resourceCheck,
                    $is_repeat = true);
            } else {
                $checkReturn = $this->checkReservation(trim(request()->input('reservation_date_start')),
                    trim(request()->input('reservation_date_end')),
                    $resourceTypeCheck,
                    $resourceCheck);
            }

            if( $checkReturn['success'] == true && $checkReturn['message'] == "E"
                && $checkReturn['data']['reservation_date_start'] == request()->input('reservation_date_start').":00"
                && $checkReturn['data']['reservation_date_end'] == request()->input('reservation_date_end').":00" )
            {
                sleep(1);
                return AjaxResponse::make(['reservation_check_text' => __('Létező foglalás!')], []);
            } else if ($checkReturn['success'] == true && $checkReturn['message'] == "R") {
                sleep(1);
                return AjaxResponse::make(['reservation_check_text' => __('Létező foglalás!')], []);
            } else if ($checkReturn['success'] == true && $checkReturn['message'] == "T")
            {
                $inputDateStart = trim(request()->input('reservation_date_start')).":00";
                $inputDateEnd = trim(request()->input('reservation_date_end')).":00";

                sleep(1);
                return AjaxResponse::make(['reservation_check_text' => __('Létező idősáv!')], []);
            } else {
                return AjaxResponse::make(['success' => true], []);
            }

        }

    }

    /**
     * @return string
     */
    protected function getFailedRedirectUrl()
    {
        return route('reservationCalendarView');
    }

}
