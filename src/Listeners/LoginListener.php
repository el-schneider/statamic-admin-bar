<?php

namespace ElSchneider\StatamicAdminBar\Listeners;

use ElSchneider\StatamicAdminBar\Http\Controllers\AccessController;
use Illuminate\Auth\Events\Login;

class LoginListener
{
    protected AccessController $accessController;

    public function __construct(AccessController $accessController)
    {
        $this->accessController = $accessController;
    }

    public function handle(Login $event): void
    {
        if ($this->accessController->canViewAdminBar()) {
            cookie()->queue('has_had_admin_bar_access', true, 60 * 24 * 7); // 7 days
        }
    }
}
