<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserTransaction;

class UserTransactionPolicy
{
    /**
      * Determine whether the user can view any models.
      *
      * @param  User  $user
      * @return \Illuminate\Auth\Access\Response|bool
      */
    public function viewAny(User $user)
    {
        return $user->isCustomer() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, UserTransaction $userTransaction)
    {
        return $user->isCustomer() && ($user->id === $userTransaction->user_id);
    }

    public function viewCheck(User $user, UserTransaction $userTransaction)
    {
        return $user->isCustomer() && ($user->id === $userTransaction->user_id) || $user->isAdmin();
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
    public function update(User $user, UserTransaction $userTransaction)
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
    public function delete(User $user, UserTransaction $userTransaction)
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
    public function restore(User $user, UserTransaction $userTransaction)
    {
        return $this->update($user, $userTransaction);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, UserTransaction $userTransaction)
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
    public function approve(User $user, UserTransaction $userTransaction)
    {
        return $user->isAdmin() && $userTransaction->isPending();
    }

    /**
     * Determine whether the user can approve the model.
     *
     * @param  User  $user
     * @param  Usage  $usage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reject(User $user, UserTransaction $userTransaction)
    {
        return $user->isAdmin() && $userTransaction->isPending();
    }
}
