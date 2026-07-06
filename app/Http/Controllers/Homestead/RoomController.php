<?php

namespace App\Http\Controllers\Homestead;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Homestead\RoomManager;
use App\Models\Homestead\RoomSave;

class RoomController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Room Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing and managing the user's indoor rooms.
    |
    */

    /**
     * Shows the user's rooms page.
     *
     * @param  \App\Services\Homestead\RoomManager  $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(RoomManager $service)
    {
        $user = Auth::user();

        return view('homestead.rooms', [
            'rooms' => RoomSave::ownedBy($user->id)->indoor()->orderBy('name')->get(),
            'slots' => $service->getSlotSummary($user, RoomSave::TYPE_INDOOR),
        ]);
    }

    /**
     * Gets the room creation modal.
     *
     * @param  \App\Services\Homestead\RoomManager  $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreate(RoomManager $service)
    {
        $slots = $service->getSlotSummary(Auth::user(), RoomSave::TYPE_INDOOR);
        if (!$slots['can_create']) {
            abort(403);
        }

        return view('homestead._create_edit_room', [
            'room' => new RoomSave(['room_type' => RoomSave::TYPE_INDOOR]),
        ]);
    }

    /**
     * Creates a room.
     *
     * @param  \Illuminate\Http\Request             $request
     * @param  \App\Services\Homestead\RoomManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreate(Request $request, RoomManager $service)
    {
        $request->validate(RoomSave::$createRules);

        if ($room = $service->createRoom($request->only(['name']), Auth::user(), RoomSave::TYPE_INDOOR)) {
            flash('Room created successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the room editing modal.
     *
     * @param  int                                    $id
     * @param  \App\Services\Homestead\RoomManager    $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEdit($id, RoomManager $service)
    {
        $room = $service->getUserRoom($id, Auth::user());
        if (!$room || !$room->is_room) {
            abort(404);
        }

        return view('homestead._create_edit_room', [
            'room' => $room,
        ]);
    }

    /**
     * Updates a room.
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
            flash('Room updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the room deletion modal.
     *
     * @param  int                                    $id
     * @param  \App\Services\Homestead\RoomManager    $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDelete($id, RoomManager $service)
    {
        $room = $service->getUserRoom($id, Auth::user());
        if (!$room || !$room->is_room) {
            abort(404);
        }

        return view('homestead._delete_room', [
            'room' => $room,
        ]);
    }

    /**
     * Deletes a room.
     *
     * @param  \Illuminate\Http\Request             $request
     * @param  \App\Services\Homestead\RoomManager  $service
     * @param  int                                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDelete(Request $request, RoomManager $service, $id)
    {
        if ($service->deleteRoom(['room_id' => $id], Auth::user())) {
            flash('Room deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
