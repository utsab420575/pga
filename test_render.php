<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;

$admin = User::where('user_type', 'admin')->first();
auth()->login($admin);

$controller = new HomeController();
try {
    $res = $controller->approve_eligibility(new Request());
    $html = $res->render();
    echo "SUCCESS: Rendered HTML length: " . strlen($html) . PHP_EOL;
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
