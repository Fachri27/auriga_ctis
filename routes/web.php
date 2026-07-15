<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AdminDashboardController, ArtikelController, CaseGeometryController, HomeController, ProfileController, PublicCaseController, PublicDashboardController};
use App\Livewire\About\AboutForm;
use App\Livewire\Artikels\{ArtikelForm, ArtikelTable};
use App\Livewire\Cases\{CaseAction, CaseDetail, CaseForm, CaseList};
use App\Livewire\Categories\{CategoriesForm, CategoriesTable};
use App\Livewire\Process\{ProcessForm, ProcessTable};
use App\Livewire\Reports\{ReportDetail, ReportForm, ReportTable};
use App\Livewire\Status\StatusList;
use App\Livewire\Tasks\{TaskForm, TaskList, TaskRequirementForm, TaskRequirementList};
use App\Livewire\{SubscriptionList, UserForm, UserList};
use App\Livewire\Permission\ManagePermission;

Route::get('/', function () {
    return redirect('/id');
});

Route::group([
    'prefix' => '{locale}',
    'middleware' => ['setlocale'],
    'where' => ['locale' => 'en|id'],
], function () {

    // Route::get('/', function () {
    //     return view('front.dashboard-user');
    // })->name('dashboard-user');

    Route::get('/', [HomeController::class, 'index'])->name('dashboard-user');

    // Verified & Published Cases (public)
    Route::get('/verified-cases', [\App\Http\Controllers\Front\VerifiedCaseController::class, 'index'])->name('front.verified-cases');
    // Public verification page (locale-aware): /{locale}/verify-case/{case_number}
    Route::get('/verify-case/{case_number}', [\App\Http\Controllers\Public\CaseVerificationController::class, 'show'])->name('verify-case');

    Route::get('/about', [\App\Http\Controllers\HomeController::class, 'about'])->name('about-user');

    Route::get('/reports/create', ReportForm::class)->middleware('auth')->name('report.form');

    // Route::get('/verify-case/{case_number}',
    //     [PublicCaseController::class, 'show']
    // )->name('public.verify.case');

    Route::get('/detail-case/{caseNumber}', [PublicCaseController::class, 'show'])
        ->name('public.verify.case');

    // Public dashboard route
    Route::get('/dashboard', [PublicDashboardController::class, 'index'])
        ->name('public.dashboard');

    Route::get('/artikel/{slug}', [HomeController::class,'preview'])->name('public.artikel.detail');
    Route::get('/artikels', [ArtikelController::class,'showArtikel'])->name('public.artikel.list');

});

// public GeoJSON for case geometries
Route::get('/case-geometries', [CaseGeometryController::class, 'index']);

// Sitemap XML untuk SEO
Route::get('/sitemap.xml', function () {
    $items = [];

    // Homepage
    $items[] = ['url' => url('/id'), 'lastmod' => now(), 'priority' => '1.0', 'changefreq' => 'daily'];

    // Static pages
    $items[] = ['url' => url('/id/about'), 'lastmod' => now(), 'priority' => '0.8', 'changefreq' => 'monthly'];
    $items[] = ['url' => url('/id/verified-cases'), 'lastmod' => now(), 'priority' => '0.9', 'changefreq' => 'daily'];
    $items[] = ['url' => url('/id/dashboard'), 'lastmod' => now(), 'priority' => '0.9', 'changefreq' => 'daily'];
    $items[] = ['url' => url('/id/artikels'), 'lastmod' => now(), 'priority' => '0.7', 'changefreq' => 'weekly'];

    // Published cases
    $cases = DB::table('cases')
        ->leftJoin('case_translations', function ($q) {
            $q->on('case_translations.case_id', '=', 'cases.id')
              ->where('case_translations.locale', 'id');
        })
        ->where('cases.is_public', true)
        ->whereNotNull('cases.published_at')
        ->select('cases.id', 'cases.case_number', 'cases.updated_at', 'case_translations.title')
        ->orderBy('cases.updated_at', 'desc')
        ->limit(500)
        ->get();

    foreach ($cases as $case) {
        $items[] = [
            'url' => url('/id/detail-case/' . $case->case_number),
            'lastmod' => $case->updated_at ?? now(),
            'priority' => '0.8',
            'changefreq' => 'weekly',
        ];
    }

    // Published articles
    $artikels = DB::table('artikels')
        ->whereNotNull('published_at')
        ->select('slug', 'updated_at')
        ->orderBy('updated_at', 'desc')
        ->limit(200)
        ->get();

    foreach ($artikels as $art) {
        $items[] = [
            'url' => url('/id/artikel/' . $art->slug),
            'lastmod' => $art->updated_at ?? now(),
            'priority' => '0.6',
            'changefreq' => 'monthly',
        ];
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($items as $item) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($item['url']) . "</loc>\n";
        $xml .= "    <lastmod>" . ($item['lastmod'] instanceof \DateTime ? $item['lastmod']->format('Y-m-d') : date('Y-m-d')) . "</lastmod>\n";
        $xml .= "    <changefreq>" . $item['changefreq'] . "</changefreq>\n";
        $xml .= "    <priority>" . $item['priority'] . "</priority>\n";
        $xml .= "  </url>\n";
    }

    $xml .= '</urlset>';

    return response($xml)->header('Content-Type', 'application/xml');
})->name('sitemap.xml');

Route::get('/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'internal.access'])
    ->name('dashboard');

Route::get('/dashboard/export/csv', [AdminDashboardController::class, 'exportCsv'])
    ->middleware(['auth', 'verified', 'internal.access'])
    ->name('dashboard.export.csv');

Route::get('/dashboard/export/excel', [AdminDashboardController::class, 'exportExcel'])
    ->middleware(['auth', 'verified', 'internal.access'])
    ->name('dashboard.export.excel');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin|cso'])->group(function () {
    Route::get('/cms/categories/', CategoriesTable::class)->name('categoris.index');
    Route::get('/cms/categories/create', CategoriesForm::class)->name('categoris.create');
    Route::get('/cms/categories/{categoryId}/edit', CategoriesForm::class)->name('categoris.edit');

    Route::get('/cms/reports', ReportTable::class)->name('reports.index');

    Route::get('/cms/process', ProcessTable::class)->name('process.index');
    Route::get('/cms/process/create', ProcessForm::class)->name('process.create');
    Route::get('/cms/process/{processId}/edit', ProcessForm::class)->name('process.edit');

    Route::get('/cms/tasks', TaskList::class)->name('task.index');
    // Route::get('/cms/tasks/create', TaskForm::class)->name('task.create');
    Route::get('/cms/tasks/{id}/edit', TaskForm::class)->name('task.edit');

    Route::get('/cms/task-requirements/', TaskRequirementList::class)->name('taskreq.index');
    Route::get('/cms/task-requirements/{id}/edit', TaskRequirementForm::class)->name('taskreq.edit');

    Route::get('/cms/cases', CaseList::class)->name('case.index');
    Route::get('/cms/cases/create', CaseForm::class)->name('case.create');
    Route::get('/cms/cases/{id}/detail', CaseDetail::class)->name('case.detail');
    Route::get('/cms/cases/{caseId}/edit', CaseForm::class)->name('case.edit');

    Route::get('/cms/reports/detail/{id}', ReportDetail::class)->name('reports.detail');

    Route::get('/cms/status', StatusList::class)->name('statuse.index');

    // Verification pages
    Route::get('/verification/assigned-to-me', \App\Livewire\Verification\AssignedToMe::class)
        ->middleware('auth')
        ->name('verification.assigned');

    Route::get('/verification/pending-review', \App\Livewire\Verification\PendingReview::class)
        ->middleware('auth')
        ->name('verification.pending');

    Route::get('/verification/rejected', \App\Livewire\Verification\RejectedCases::class)
        ->middleware('auth')
        ->name('verification.rejected');

    Route::get('/cms/about', AboutForm::class)->name('about.edit');

    Route::get('cms/artikels/create/', ArtikelForm::class)->name('artikel.create');
    Route::get('cms/artikels/', ArtikelTable::class)->name('artikel.index');

    Route::get('/cms/subscriptions', SubscriptionList::class)->name('subscription.index');
    Route::get('cms/artikels/{artikelId}/edit/', ArtikelForm::class)->name('artikel.edit');

    Route::get('cms/users/', UserList::class)->name('user.index');
    Route::get('cms/users/create', UserForm::class)->name('user.create');
    Route::get('cms/users/{userId}/edit', UserForm::class)->name('user.edit');


    Route::get('cms/permissions/', ManagePermission::class)->name('permission');
    Route::get('cms/task/create/{case}', CaseDetail::class)->name('case.task.create');

    // Chart data management
    Route::get('/cms/charts/upload', \App\Livewire\Charts\CsvUpload::class)->name('charts.upload');
    Route::get('/cms/charts', \App\Livewire\Charts\ChartsDashboard::class)->name('charts.dashboard');
    Route::get('/cms/charts/sync', function () {
        \Illuminate\Support\Facades\Artisan::queue('chart:sync');
        return redirect()->route('charts.dashboard')->with('success', 'Sync dijadwalkan, data akan diperbarui di background.');
    })->name('charts.sync');


});

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

require __DIR__.'/auth.php';
