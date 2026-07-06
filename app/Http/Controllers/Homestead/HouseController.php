<?php

namespace App\Http\Controllers\Homestead;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Homestead\RoomManager;
use App\Models\Homestead\RoomSave;

class HouseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | House Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing and managing the user's outdoor houses.
    |
    */

    /**
     * Shows the user's houses page.
     *
     * @param  \App\Services\Homestead\RoomManager  $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(RoomManager $service)
    {
        $user = Auth::user();

        return view('homestead.houses', [
            'houses' => RoomSave::ownedBy($user->id)->outdoor()->orderBy('name')->get(),
            'slots' => $service->getSlotSummary($user, RoomSave::TYPE_OUTDOOR),
        ]);
    }

    /**
     * Gets the house creation modal.
     *
     * @param  \App\Services\Homestead\RoomManager  $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreate(RoomManager $service)
    {
        $slots = $service->getSlotSummary(Auth::user(), RoomSave::TYPE_OUTDOOR);
        if (!$slots['can_create']) {
            abort(403);
        }

        return view('homestead._create_edit_house', [
            'house' => new RoomSave(['room_type' => RoomSave::TYPE_OUTDOOR]),
        ]);
    }

    /**
     * Creates a house.
     *
     * @param  \Illuminate\Http\Request             $request
     * @param  \App\Services\Homestead\RoomManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreate(Request $request, RoomManager $service)
    {
        $request->validate(RoomSave::$createRules);

        if ($service->createRoom($request->only(['name']), Auth::user(), RoomSave::TYPE_OUTDOOR)) {
            flash('House created successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the house editing modal.
     *
     * @param  int                                    $id
     * @param  \App\Services\Homestead\RoomManager    $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEdit($id, RoomManager $service)
    {
        $house = $service->getUserRoom($id, Auth::user());
        if (!$house || !$house->is_house) {
            abort(404);
        }

        return view('homestead._create_edit_house', [
            'house' => $house,
        ]);
    }

    /**
     * Updates a house.
     *
     * @param  \Illuminate\Http\Request             $request
     * @param  \App\Services\Homestead\RoomManager  $service
     * @param  int                                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(Request $request, RoomManager $service, $id)
    {
        $request->validate(RoomSave::$updateRules);

        if ($service->updateRoom($request->only(['name']) + ['room_id' => $id], Auth::user())) {
            flash('House updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the house deletion modal.
     *
     * @param  int                                    $id
     * @param  \App\Services\Homestead\RoomManager    $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDelete($id, RoomManager $service)
    {
        $house = $service->getUserRoom($id, Auth::user());
        if (!$house || !$house->is_house) {
            abort(404);
        }

        return view('homestead._delete_house', [
            'house' => $house,
        ]);
    }

    /**
     * Deletes a house.
     *
     * @param  \Illuminate\Http\Request             $request
     * @param  \App\Services\Homestead\RoomManager  $service
     * @param  int                                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDelete(Request $request, RoomManager $service, $id)
    {
        if ($service->deleteRoom(['room_id' => $id], Auth::user())) {
            flash('House deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
