<?php

namespace App\Policies;

use App\Models\{Category, User};

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminOrSupport();
    }

    public function view(User $user, Category $category): bool
    {
        return $category->user_id === null || $user->id === $category->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdminOrSupport();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isAdminOrSupport();
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->isAdminOrSupport();
    }

    public function restore(User $user, Category $category): bool
    {
        return false;
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return false;
    }
}
