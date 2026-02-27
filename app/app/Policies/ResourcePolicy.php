<?php

namespace App\Policies;

use App\Models\Resource;
use App\Models\User;

class ResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function manage(User $user, Resource $resource): bool
    {
        return $user->can('manage', $resource->restaurant);
    }
}
