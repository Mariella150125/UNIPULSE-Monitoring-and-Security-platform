<?php

namespace App\Policies;

use App\Models\Connector;
use App\Models\User;

class ConnectorPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function create(User $user): bool { return true; }

    public function view(User $user, Connector $connector): bool
    {
        return $connector->created_by === $user->id;
    }

    public function update(User $user, Connector $connector): bool
    {
        return $connector->created_by === $user->id;
    }

    public function delete(User $user, Connector $connector): bool
    {
        return $connector->created_by === $user->id;
    }

    public function test(User $user, Connector $connector): bool
    {
        return $connector->created_by === $user->id;
    }
}