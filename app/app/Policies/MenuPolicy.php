<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\User;

class MenuPolicy
{
    public function manage(User $user, MenuItem $item): bool
    {
        return $user->can('manage', $item->restaurant);
    }
}
