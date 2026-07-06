<?php

namespace App\Http\Controllers\Homestead;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Homestead\RoomManager;

class RoomEditorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Room Editor Controller
    |--------------------------------------------------------------------------
    |
    | Handles the room decoration editor interface.
    |
    */

    /**
     * Shows the room editor.
     *
     * @param  int                                    $id
     * @param  \App\Services\Homestead\RoomManager      $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditor($id, RoomManager $service)
    {
        $room = $service->getUserRoom($id, Auth::user());
        if (!$room || !$room->is_room) {
            abort(404);
        }

        $room->load('layout');

        $inventory = $service->getEditorInventory(Auth::user(), $room->room_type, $room);

        return view('homestead.room_editor', [
            'room' => $room,
            'exitUrl' => url('homestead/rooms'),
            'inventory' => $inventory,
            'editorCatalog' => $service->getEditorCatalogForRoom($inventory, $room),
            'initialPlacements' => $service->getPlacementsForEditor($room),
            'canvasWidth' => config('lorekeeper.homestead.canvas_width', 700),
            'canvasHeight' => config('lorekeeper.homestead.canvas_height', 500),
        ]);
    }

    /**
     * Saves the room editor state.
     *
     * @param  \Illuminate\Http\Request             $request
     * @param  \App\Services\Homestead\RoomManager  $service
     * @param  int                                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSave(Request $request, RoomManager $service, $id)
    {
        $room = $service->getUserRoom($id, Auth::user());
        if (!$room || !$room->is_room) {
            abort(404);
        }

        $placements = json_decode($request->input('placements', '[]'), true);
        if (!is_array($placements)) {
            flash('Invalid placement data.')->error();
            return redirect()->back();
        }

        if ($service->savePlacements(['placements' => $placements], Auth::user(), $room)) {
            flash('Room layout saved successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
