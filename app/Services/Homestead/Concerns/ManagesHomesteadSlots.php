<?php

namespace App\Services\Homestead\Concerns;

use Config;
use App\Services\Homestead\HomesteadConfig;
use App\Models\Homestead\RoomSave;
use App\Models\User\UserItem;

trait ManagesHomesteadSlots
{
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
        return HomesteadConfig::baseSlots($roomType)
            + $this->getActivatedSlotCount($user, HomesteadConfig::slotTag($roomType));
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
