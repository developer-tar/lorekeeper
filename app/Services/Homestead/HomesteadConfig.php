<?php

namespace App\Services\Homestead;

use Config;
use App\Models\Homestead\RoomSave;

class HomesteadConfig
{
    /**
     * UI and routing labels for a homestead space type.
     *
     * @param  string  $roomType
     * @return array
     */
    public static function spaceLabels($roomType)
    {
        return Config::get('lorekeeper.homestead.spaces.' . $roomType, []);
    }

    /**
     * URL segment for a homestead space type (rooms or houses).
     *
     * @param  string  $roomType
     * @return string
     */
    public static function listSegment($roomType)
    {
        return static::spaceLabels($roomType)['segment']
            ?? static::editorSettings($roomType)['list_segment']
            ?? ($roomType === RoomSave::TYPE_OUTDOOR ? 'houses' : 'rooms');
    }

    /**
     * Editor sidebar inventory groups for a space type.
     *
     * @param  string  $roomType
     * @return array
     */
    public static function inventoryGroups($roomType)
    {
        $groups = Config::get('lorekeeper.homestead.editor_inventory_groups', []);

        if (isset($groups[$roomType]) && is_array($groups[$roomType])) {
            return $groups[$roomType];
        }

        if (isset($groups['furniture'])) {
            return $groups;
        }

        return [];
    }

    /**
     * Flattened placement types allowed in the editor for a space type.
     *
     * @param  string  $roomType
     * @return array
     */
    public static function placementTypes($roomType)
    {
        return collect(static::inventoryGroups($roomType))
            ->flatten()
            ->unique()
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Editor UI settings for a space type.
     *
     * @param  string  $roomType
     * @return array
     */
    public static function editorSettings($roomType)
    {
        return Config::get('lorekeeper.homestead.editor.' . $roomType, []);
    }

    /**
     * Canvas dimensions for the editor.
     *
     * @param  string  $roomType
     * @return array{width: int, height: int}
     */
    public static function canvasSize($roomType)
    {
        $canvas = Config::get('lorekeeper.homestead.canvas.' . $roomType);

        if (is_array($canvas)) {
            return [
                'width' => (int) ($canvas['width'] ?? Config::get('lorekeeper.homestead.canvas_width', 700)),
                'height' => (int) ($canvas['height'] ?? Config::get('lorekeeper.homestead.canvas_height', 500)),
            ];
        }

        return [
            'width' => (int) Config::get('lorekeeper.homestead.canvas_width', 700),
            'height' => (int) Config::get('lorekeeper.homestead.canvas_height', 500),
        ];
    }

    /**
     * Surface layout field map for a space type.
     *
     * @param  string  $roomType
     * @return array
     */
    public static function surfaceLayoutFields($roomType)
    {
        return Config::get('lorekeeper.homestead.surface_layout_fields.' . $roomType, []);
    }

    /**
     * Inventory tag used to unlock extra slots.
     *
     * @param  string  $roomType
     * @return string
     */
    public static function slotTag($roomType)
    {
        $tags = Config::get('lorekeeper.homestead.slot_tags', []);

        return $roomType === RoomSave::TYPE_INDOOR
            ? ($tags['indoor'] ?? 'room_slot')
            : ($tags['outdoor'] ?? 'house_slot');
    }

    /**
     * Base slot count before activated slot items.
     *
     * @param  string  $roomType
     * @return int
     */
    public static function baseSlots($roomType)
    {
        return $roomType === RoomSave::TYPE_INDOOR
            ? (int) Config::get('lorekeeper.homestead.base_indoor_slots', 1)
            : (int) Config::get('lorekeeper.homestead.base_outdoor_slots', 1);
    }
}
