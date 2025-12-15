<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Welcome', [
            'canLogin'     => Route::has('login'),
            'canRegister'  => Route::has('register'),
            'events'       => Event::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->all());

        if($event) {
            return Redirect::to('/dashboard');
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        //
    }

    /**
     *  example: #parameters: array:2 [
     *      "user_id" => "1"
     *      "event_id" => 11
     *       ] - OK!!
     *
     * @param Request $request
     */
    public function classify(Request $request) {
        if(is_array($request->all())) {
            $userData = User::find($request->user_id);
            $userData->events()->sync($request->all(), false);
        }
    }
}
