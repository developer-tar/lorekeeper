<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Homestead Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the homestead / room decoration module.
    |
    */

    'base_indoor_slots' => 1,
    'base_outdoor_slots' => 1,

    'slot_tags' => [
        'indoor' => 'room_slot',
        'outdoor' => 'house_slot',
    ],

    /*
    |--------------------------------------------------------------------------
    | Staff Slot Bypass
    |--------------------------------------------------------------------------
    |
    | When true, staff members (admin rank or users with staff powers) are not
    | limited by homestead room/house slot counts.
    |
    */
    'bypass_slot_limits_for_staff' => true,

    /*
    |--------------------------------------------------------------------------
    | Editor Inventory
    |--------------------------------------------------------------------------
    |
    | Placement types shown in the room editor sidebar, grouped by tab.
    | Items must also have is_homestead_item = 1 and match the room type.
    |
    */
    'editor_inventory_groups' => [
        'furniture' => ['floor', 'decoration', 'exterior'],
        'surfaces' => ['wall', 'ceiling', 'flooring', 'floor'],
    ],

    'excluded_editor_item_tags' => ['room_slot', 'house_slot', 'sprite_slot'],

    'canvas_width' => 700,
    'canvas_height' => 500,

];
