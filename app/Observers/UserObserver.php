<?php

namespace App\Observers;

use App\Actions\Finance\CreateDefaultCategoriesForUserAction;
use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class UserObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private CreateDefaultCategoriesForUserAction $createDefaultCategories,
    ) {
    }

    public function created(User $user): void
    {
        $this->createDefaultCategories->execute($user);
    }
}
