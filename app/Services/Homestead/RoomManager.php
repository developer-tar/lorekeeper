<?php namespace App\Services\Homestead;

use DB;
use Config;

use App\Services\Service;
use App\Models\Homestead\RoomSave;
use App\Models\Homestead\RoomLayout;
use App\Models\Homestead\RoomPlacement;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Models\Item\Item;

class RoomManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Room Manager
    |--------------------------------------------------------------------------
    |
    | Handles creation, modification and deletion of homestead rooms and houses.
    |
    */

    /**
     * Get slot usage summary for a user and room type.
     *
     * @param  \App\Models\User\User  $user
     * @param  string                 $roomType
     * @return array
     */
    public function getSlotSummary($user, $roomType)
    {
        $used = $this->getUsedSlots($user, $roomType);

        if ($this->bypassesSlotLimits($user)) {
            return [
                'max' => null,
                'used' => $used,
                'remaining' => null,
                'can_create' => true,
                'unlimited' => true,
            ];
        }

        $max = $this->getMaxSlots($user, $roomType);

        return [
            'max' => $max,
            'used' => $used,
            'remaining' => max(0, $max - $used),
            'can_create' => $used < $max,
            'unlimited' => false,
        ];
    }

    /**
     * Get the maximum number of slots available for a room type.
     *
     * @param  \App\Models\User\User  $user
     * @param  string                 $roomType
     * @return int
     */
    public function getMaxSlots($user, $roomType)
    {
        $base = $roomType === RoomSave::TYPE_INDOOR
            ? Config::get('lorekeeper.homestead.base_indoor_slots', 1)
            : Config::get('lorekeeper.homestead.base_outdoor_slots', 1);

        return $base + $this->getActivatedSlotCount($user, $this->getSlotTag($roomType));
    }

    /**
     * Get the number of rooms or houses a user has created.
     *
     * @param  \App\Models\User\User  $user
     * @param  string                 $roomType
     * @return int
     */
    public function getUsedSlots($user, $roomType)
    {
        return RoomSave::where('user_id', $user->id)
            ->where('room_type', $roomType)
            ->count();
    }

    /**
     * Get the number of activated inventory slot items for a tag.
     *
     * @param  \App\Models\User\User  $user
     * @param  string                 $tag
     * @return int
     */
    public function getActivatedSlotCount($user, $tag)
    {
        return UserItem::where('user_id', $user->id)
            ->where('activated_quantity', '>', 0)
            ->whereHas('item.tags', function ($query) use ($tag) {
                $query->where('tag', $tag)->where('is_active', 1);
            })
            ->sum('activated_quantity');
    }

    /**
     * Create a room or house for a user.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @param  string                 $roomType
     * @return \App\Models\Homestead\RoomSave|bool
     */
    public function createRoom($data, $user, $roomType = RoomSave::TYPE_INDOOR)
    {
        DB::beginTransaction();

        try {
            if (!isset($data['name']) || !trim($data['name'])) {
                throw new \Exception('Please enter a name.');
            }

            if (!$this->getSlotSummary($user, $roomType)['can_create']) {
                $label = $roomType === RoomSave::TYPE_INDOOR ? 'room' : 'house';
                throw new \Exception('You have reached your ' . $label . ' slot limit.');
            }

            $room = RoomSave::create([
                'user_id' => $user->id,
                'name' => trim($data['name']),
                'room_type' => $roomType,
            ]);

            RoomLayout::create([
                'room_save_id' => $room->id,
            ]);

            return $this->commitReturn($room);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Update a room or house.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return \App\Models\Homestead\RoomSave|bool
     */
    public function updateRoom($data, $user)
    {
        DB::beginTransaction();

        try {
            if (!isset($data['room_id'])) {
                throw new \Exception('Invalid room selected.');
            }

            $room = $this->getUserRoom($data['room_id'], $user);
            if (!$room) {
                throw new \Exception('Invalid room selected.');
            }

            if (!isset($data['name']) || !trim($data['name'])) {
                throw new \Exception('Please enter a name.');
            }

            $room->update([
                'name' => trim($data['name']),
            ]);

            return $this->commitReturn($room);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Delete a room or house.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function deleteRoom($data, $user)
    {
        DB::beginTransaction();

        try {
            if (!isset($data['room_id'])) {
                throw new \Exception('Invalid room selected.');
            }

            $room = $this->getUserRoom($data['room_id'], $user);
            if (!$room) {
                throw new \Exception('Invalid room selected.');
            }

            RoomPlacement::where('room_save_id', $room->id)->delete();
            RoomLayout::where('room_save_id', $room->id)->delete();
            $room->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Get a room owned by the user.
     *
     * @param  int                    $id
     * @param  \App\Models\User\User  $user
     * @return \App\Models\Homestead\RoomSave|null
     */
    public function getUserRoom($id, $user)
    {
        return RoomSave::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Get grouped placeable inventory for the room editor sidebar.
     *
     * @param  \App\Models\User\User           $user
     * @param  string                          $roomType
     * @param  \App\Models\Homestead\RoomSave  $room
     * @return array
     */
    public function getEditorInventory($user, $roomType, $room)
    {
        $placeableItemIds = Item::placeableInHomestead($roomType)->pluck('id');
        $groups = Config::get('lorekeeper.homestead.editor_inventory_groups', []);

        if (!$placeableItemIds->count()) {
            return array_fill_keys(array_keys($groups), collect());
        }

        $stacks = UserItem::where('user_id', $user->id)
            ->where('count', '>', 0)
            ->whereIn('item_id', $placeableItemIds)
            ->with(['item' => function ($query) {
                $query->sortAlphabetical();
            }])
            ->get()
            ->groupBy('item_id');

        $placedCounts = RoomPlacement::where('room_save_id', $room->id)
            ->selectRaw('item_id, COUNT(*) as placed_count')
            ->groupBy('item_id')
            ->pluck('placed_count', 'item_id');

        $entries = $stacks->map(function ($itemStacks, $itemId) use ($placedCounts) {
            $item = $itemStacks->first()->item;
            $quantity = $itemStacks->sum('count');
            $placed = (int) ($placedCounts[$itemId] ?? 0);

            return (object) [
                'item' => $item,
                'stacks' => $itemStacks,
                'quantity' => $quantity,
                'placed' => $placed,
                'available' => max(0, $quantity - $placed),
            ];
        })->sortBy(function ($entry) {
            return $entry->item->name;
        })->values();

        $grouped = [];
        foreach ($groups as $groupKey => $placementTypes) {
            $grouped[$groupKey] = $entries->filter(function ($entry) use ($placementTypes) {
                return in_array($entry->item->placement_type, $placementTypes, true);
            })->values();
        }

        return $grouped;
    }

    /**
     * Build catalog data for the room editor client script.
     *
     * @param  array  $inventory
     * @return array
     */
    public function getEditorCatalog($inventory)
    {
        $catalog = [];

        foreach ($inventory['furniture'] as $entry) {
            $catalog[$entry->item->id] = [
                'id' => $entry->item->id,
                'name' => $entry->item->name,
                'imageUrl' => $entry->item->imageUrl,
                'hasImage' => (bool) $entry->item->has_image,
                'width' => (int) ($entry->item->default_width ?: 64),
                'height' => (int) ($entry->item->default_height ?: 64),
                'quantity' => (int) $entry->quantity,
            ];
        }

        return $catalog;
    }

    /**
     * Get saved placements for the room editor.
     *
     * @param  \App\Models\Homestead\RoomSave  $room
     * @return array
     */
    public function getPlacementsForEditor($room)
    {
        return RoomPlacement::where('room_save_id', $room->id)
            ->orderBy('z_index')
            ->get()
            ->map(function ($placement) {
                return [
                    'item_id' => $placement->item_id,
                    'x' => (float) $placement->x,
                    'y' => (float) $placement->y,
                    'width' => (float) $placement->width,
                    'height' => (float) $placement->height,
                    'z_index' => (int) $placement->z_index,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Build catalog data including items already placed in the room.
     *
     * @param  array                             $inventory
     * @param  \App\Models\Homestead\RoomSave    $room
     * @return array
     */
    public function getEditorCatalogForRoom($inventory, $room)
    {
        $catalog = $this->getEditorCatalog($inventory);

        $placedItemIds = RoomPlacement::where('room_save_id', $room->id)->pluck('item_id')->unique();
        if (!$placedItemIds->count()) {
            return $catalog;
        }

        $placedItems = Item::whereIn('id', $placedItemIds)->get();
        foreach ($placedItems as $item) {
            if (isset($catalog[$item->id])) {
                continue;
            }

            $catalog[$item->id] = [
                'id' => $item->id,
                'name' => $item->name,
                'imageUrl' => $item->imageUrl,
                'hasImage' => (bool) $item->has_image,
                'width' => (int) ($item->default_width ?: 64),
                'height' => (int) ($item->default_height ?: 64),
                'quantity' => 0,
            ];
        }

        return $catalog;
    }

    /**
     * Save room editor placements.
     *
     * @param  array                             $data
     * @param  \App\Models\User\User             $user
     * @param  \App\Models\Homestead\RoomSave    $room
     * @return bool
     */
    public function savePlacements($data, $user, $room)
    {
        DB::beginTransaction();

        try {
            $placements = $data['placements'] ?? [];
            if (!is_array($placements)) {
                throw new \Exception('Invalid placement data.');
            }

            $inventory = $this->getEditorInventory($user, $room->room_type, $room);
            $ownedQuantities = [];
            foreach ($inventory['furniture'] as $entry) {
                $ownedQuantities[$entry->item->id] = $entry->quantity;
            }

            $placeableItemIds = Item::placeableInHomestead($room->room_type)->pluck('id')->all();
            $canvasWidth = Config::get('lorekeeper.homestead.canvas_width', 700);
            $canvasHeight = Config::get('lorekeeper.homestead.canvas_height', 500);
            $itemCounts = [];

            foreach ($placements as $placement) {
                if (!isset($placement['item_id'], $placement['x'], $placement['y'], $placement['width'], $placement['height'], $placement['z_index'])) {
                    throw new \Exception('Invalid placement data.');
                }

                $itemId = (int) $placement['item_id'];
                if (!in_array($itemId, $placeableItemIds, true)) {
                    throw new \Exception('One or more placed items cannot be used in this room.');
                }

                $x = (float) $placement['x'];
                $y = (float) $placement['y'];
                $width = (float) $placement['width'];
                $height = (float) $placement['height'];

                if ($width <= 0 || $height <= 0) {
                    throw new \Exception('Invalid placement dimensions.');
                }

                if ($x < 0 || $y < 0 || ($x + $width) > $canvasWidth || ($y + $height) > $canvasHeight) {
                    throw new \Exception('One or more items are outside the room boundaries.');
                }

                $itemCounts[$itemId] = ($itemCounts[$itemId] ?? 0) + 1;
            }

            foreach ($itemCounts as $itemId => $count) {
                if ($count > ($ownedQuantities[$itemId] ?? 0)) {
                    throw new \Exception('You do not own enough of one or more placed items.');
                }
            }

            RoomPlacement::where('room_save_id', $room->id)->delete();

            foreach ($placements as $placement) {
                RoomPlacement::create([
                    'room_save_id' => $room->id,
                    'item_id' => (int) $placement['item_id'],
                    'x' => $placement['x'],
                    'y' => $placement['y'],
                    'width' => $placement['width'],
                    'height' => $placement['height'],
                    'z_index' => (int) $placement['z_index'],
                ]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Get the inventory tag associated with a room type.
     *
     * @param  string  $roomType
     * @return string
     */
    protected function getSlotTag($roomType)
    {
        $tags = Config::get('lorekeeper.homestead.slot_tags', []);

        return $roomType === RoomSave::TYPE_INDOOR
            ? ($tags['indoor'] ?? 'room_slot')
            : ($tags['outdoor'] ?? 'house_slot');
    }

    /**
     * Whether the user bypasses homestead slot limits.
     *
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    protected function bypassesSlotLimits($user)
    {
        return Config::get('lorekeeper.homestead.bypass_slot_limits_for_staff', true)
            && $user->isStaff;
    }
}
