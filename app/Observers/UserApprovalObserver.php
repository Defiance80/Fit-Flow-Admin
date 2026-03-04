<?php

namespace App\Observers;

use App\Notifications\AccountApprovedNotification;
use App\Models\User;

class UserApprovalObserver
{
    public function updating(User $user): void
    {
        // If is_active is changing from 0 to 1, send approval email
        if ($user->isDirty('is_active') && $user->is_active == 1 && $user->getOriginal('is_active') == 0) {
            $user->notify(new AccountApprovedNotification());
        }
    }
}
