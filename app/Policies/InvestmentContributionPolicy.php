<?php

namespace App\Policies;

use App\Models\{InvestmentContribution, User};

class InvestmentContributionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InvestmentContribution $investmentContribution): bool
    {
        return $user->id === $investmentContribution->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InvestmentContribution $investmentContribution): bool
    {
        return $user->id === $investmentContribution->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InvestmentContribution $investmentContribution): bool
    {
        return $user->id === $investmentContribution->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InvestmentContribution $investmentContribution): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InvestmentContribution $investmentContribution): bool
    {
        return false;
    }
}
