<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserPurchase;

class UserPurchasePolicy
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
    public function view(User $user, UserPurchase $userPurchase)
    {
        return $user->isCustomer() && ($user->id === $userPurchase->user_id);
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
    public function update(User $user, UserPurchase $userPurchase)
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
    public function delete(User $user, UserPurchase $userPurchase)
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
    public function restore(User $user, UserPurchase $userPurchase)
    {
        return $this->update($user, $userPurchase);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, UserPurchase $userPurchase)
    {
        return false;
    }
}
