<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserDeposit;

class UserDepositPolicy
{
    /**
      * Determine whether the user can view any models.
      *
      * @param  User  $user
      * @return \Illuminate\Auth\Access\Response|bool
      */
    public function viewAny(User $user)
    {
        return $user->isCustomer();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, UserDeposit $userDeposit)
    {
        return $user->isCustomer() && ($user->id === $userDeposit->user_id);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->isCustomer();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, UserDeposit $userDeposit)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, UserDeposit $userDeposit)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, UserDeposit $userDeposit)
    {
        return $this->update($user, $userDeposit);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, UserDeposit $userDeposit)
    {
        return false;
    }

    /**
     * Determine whether the user can approve the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function approve(User $user, UserDeposit $userDeposit)
    {
        return $user->isAdmin() && $userDeposit->isPending();
    }

    /**
     * Determine whether the user can approve the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reject(User $user, UserDeposit $userDeposit)
    {
        return $user->isAdmin() && $userDeposit->isPending();
    }
}
