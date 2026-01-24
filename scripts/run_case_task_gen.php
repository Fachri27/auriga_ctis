<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$catId = DB::table('categories')->where('slug', 'corruption')->value('id');
if (! $catId) {
    echo "Corruption category not found\n";
    exit(1);
}

$caseId = DB::table('cases')->insertGetId([
    'case_number' => 'CASE-TEST-'.time(),
    'category_id' => $catId,
    'status_id' => DB::table('statuses')->where('key', 'investigation')->value('id'),
    'created_by' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Created case: {$caseId}\n";

$n = App\Services\CaseTaskGenerator::generate($caseId, $catId);

echo "Generated tasks: {$n}\n";

$count = DB::table('case_tasks')->where('case_id', $caseId)->count();
$reqCount = DB::table('case_task_requirements')->whereIn('case_task_id', function ($q) use ($caseId) {
    $q->select('id')->from('case_tasks')->where('case_id', $caseId);
})->count();

echo "case_tasks count: {$count}\n";
echo "case_task_requirements count: {$reqCount}\n";

$tasks = DB::table('case_tasks')->where('case_id', $caseId)->get();
foreach ($tasks as $t) {
    echo "- task_id: {$t->task_id}, process_id: {$t->process_id}, due_date: {$t->due_date}, status: {$t->status}\n";
}
