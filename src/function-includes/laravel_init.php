<?php
include_once __DIR__ . '/../function-includes/init.php';

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$basePath = __DIR__ . '/../mvc-app';

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$viewBlade = $app->make('view');
$userNetLogin = !empty(authUser()) ? authUser() : null;
if ($userNetLogin != null) {
    $userModel = User::with('userRoles')->where('UD_NetLogin', $userNetLogin)->first();
    if ($userModel != null) {
        $userModel->getUserRoleDetail();
        Auth::login($userModel);
    }
}
