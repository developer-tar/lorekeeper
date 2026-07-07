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
    | Space Labels (rooms / houses UI)
    |--------------------------------------------------------------------------
    */
    'spaces' => [
        'indoor' => [
            'segment' => 'rooms',
            'singular' => 'room',
            'title' => 'Rooms',
            'description' => 'Manage your indoor rooms.',
            'slot_limit_message' => 'You have reached your room slot limit. Activate a room slot item from your inventory to unlock more rooms.',
            'empty_message' => 'You have no rooms yet.',
            'empty_hint' => 'Create a room to start placing furniture and surfaces.',
            'name_label' => 'Room Name',
            'name_placeholder' => 'My Room',
            'create_action' => 'Create Room',
            'edit_action' => 'Edit Room',
            'delete_action' => 'Delete Room',
            'created_message' => 'Room created successfully.',
            'updated_message' => 'Room updated successfully.',
            'deleted_message' => 'Room deleted successfully.',
            'delete_confirm' => 'You are about to delete the room <strong>:name</strong>. This will also remove its layout and furniture placements. Are you sure?',
        ],
        'outdoor' => [
            'segment' => 'houses',
            'singular' => 'house',
            'title' => 'Houses',
            'description' => 'Manage your outdoor houses.',
            'slot_limit_message' => 'You have reached your house slot limit. Activate a house slot item from your inventory to unlock more houses.',
            'empty_message' => 'You have no houses yet.',
            'empty_hint' => 'Create a house to start placing exterior decor and ground cover.',
            'name_label' => 'House Name',
            'name_placeholder' => 'My House',
            'create_action' => 'Create House',
            'edit_action' => 'Edit House',
            'delete_action' => 'Delete House',
            'created_message' => 'House created successfully.',
            'updated_message' => 'House updated successfully.',
            'deleted_message' => 'House deleted successfully.',
            'delete_confirm' => 'You are about to delete the house <strong>:name</strong>. This will also remove its layout and furniture placements. Are you sure?',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor Settings (per space type)
    |--------------------------------------------------------------------------
    |
    | Shared homestead editor UI differs by indoor room vs outdoor house.
    |
    */
    'editor' => [
        'indoor' => [
            'label' => 'Room Editor',
            'list_segment' => 'rooms',
            'canvas_bg_class' => 'homestead-editor-canvas-room-bg',
            'placeable_group' => 'furniture',
            'help_text' => 'Click or drag furniture onto the canvas. Drag placed items to move. Use Surfaces for wallpaper and flooring. Save to keep your layout.',
            'save_message' => 'Room layout saved successfully.',
            'invalid_item_message' => 'One or more placed items cannot be used in this room.',
            'boundary_message' => 'One or more items are outside the room boundaries.',
            'empty_inventory_hint' => 'You need homestead decoration items in your inventory. Items must be configured for room use before they appear here.',
        ],
        'outdoor' => [
            'label' => 'House Editor',
            'list_segment' => 'houses',
            'canvas_bg_class' => 'homestead-editor-canvas-house-bg',
            'placeable_group' => 'furniture',
            'help_text' => 'Click or drag furniture onto the canvas. Drag placed items to move. Use Surfaces for ground cover. Save to keep your layout.',
            'save_message' => 'House layout saved successfully.',
            'invalid_item_message' => 'One or more placed items cannot be used in this house.',
            'boundary_message' => 'One or more items are outside the house boundaries.',
            'empty_inventory_hint' => 'You need homestead decoration items in your inventory. Items must be configured for outdoor use before they appear here.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor Inventory
    |--------------------------------------------------------------------------
    |
    | Placement types shown in the editor sidebar, grouped by tab, per space type.
    | Items must also have is_homestead_item = 1 and match the room type.
    |
    */
    'editor_inventory_groups' => [
        'indoor' => [
            'furniture' => ['floor', 'decoration'],
            'surfaces' => ['wall', 'ceiling', 'flooring', 'floor'],
        ],
        'outdoor' => [
            'furniture' => ['floor', 'decoration', 'exterior'],
            'surfaces' => ['flooring', 'floor'],
        ],
    ],

    'excluded_editor_item_tags' => ['room_slot', 'house_slot', 'sprite_slot'],

    /*
    |--------------------------------------------------------------------------
    | Surface Layout Fields
    |--------------------------------------------------------------------------
    |
    | Maps item placement_type values to room_layouts columns when applied
    | from the Surfaces inventory tab (click-to-apply, not drag-and-drop).
    |
    */
    'surface_layout_fields' => [
        'indoor' => [
            'wall' => 'wallpaper_item_id',
            'ceiling' => 'wallpaper_item_id',
            'flooring' => 'flooring_item_id',
            'floor' => 'flooring_item_id',
        ],
        'outdoor' => [
            'flooring' => 'flooring_item_id',
            'floor' => 'flooring_item_id',
        ],
    ],

    'surface_canvas_layers' => [
        'wallpaper_item_id' => 'wall',
        'exterior_wall_item_id' => 'wall',
        'flooring_item_id' => 'floor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Placement Types
    |--------------------------------------------------------------------------
    |
    | Valid placement_type values for homestead decor items (admin item form).
    |
    */
    'placement_types' => [
        'floor' => 'Floor (furniture)',
        'decoration' => 'Decoration',
        'exterior' => 'Exterior',
        'wall' => 'Wallpaper',
        'ceiling' => 'Ceiling',
        'flooring' => 'Flooring',
    ],

    'homestead_room_types' => [
        '' => 'Any room type',
        'indoor' => 'Indoor rooms only',
        'outdoor' => 'Outdoor houses only',
        'both' => 'Indoor and outdoor',
    ],

    'canvas' => [
        'indoor' => ['width' => 700, 'height' => 500],
        'outdoor' => ['width' => 700, 'height' => 500],
    ],

    // Legacy defaults used when per-type canvas config is absent.
    'canvas_width' => 700,
    'canvas_height' => 500,

];
