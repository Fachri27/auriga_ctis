<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AdminDashboardController, CaseGeometryController, HomeController, ProfileController, PublicCaseController, PublicDashboardController};
use App\Livewire\Artikels\{ArtikelForm, ArtikelTable};
use App\Livewire\Cases\{CaseDetail, CaseForm, CaseList};
use App\Livewire\Categories\{CategoriesForm, CategoriesTable};
use App\Livewire\Process\{ProcessForm, ProcessTable};
use App\Livewire\Reports\{ReportDetail, ReportForm, ReportTable};
use App\Livewire\Status\StatusList;
use App\Livewire\Tasks\{TaskForm, TaskList, TaskRequirementForm, TaskRequirementList};
use App\Livewire\{UserForm, UserList};
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
    // Public verification page (locale-aware): /{locale}/verify-case/{case_number}
    Route::get('/verify-case/{case_number}', [\App\Http\Controllers\Public\CaseVerificationController::class, 'show'])->name('verify-case');

    Route::get('/about', function () {
        return view('front.about');
    })->name('about-user');

    Route::get('/reports/create', ReportForm::class)->middleware('auth')->name('report.form');

    // Route::get('/verify-case/{case_number}',
    //     [PublicCaseController::class, 'show']
    // )->name('public.verify.case');

    Route::get('/detail-case/{caseNumber}', [PublicCaseController::class, 'show'])
        ->name('public.verify.case');

    // Public dashboard route
    Route::get('/dashboard', [PublicDashboardController::class, 'index'])
        ->name('public.dashboard');

});

// public GeoJSON for case geometries
Route::get('/case-geometries', [CaseGeometryController::class, 'index']);

Route::get('/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'internal.access'])
    ->name('dashboard');

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

    Route::get('cms/artikels/create/', ArtikelForm::class)->name('artikel.create');
    Route::get('cms/artikels/', ArtikelTable::class)->name('artikel.index');
    Route::get('cms/artikels/{artikelId}/edit/', ArtikelForm::class)->name('artikel.edit');

    Route::get('cms/users/', UserList::class)->name('user.index');
    Route::get('cms/users/create', UserForm::class)->name('user.create');
    Route::get('cms/users/{userId}/edit', UserForm::class)->name('user.edit');


    Route::get('cms/permissions/', ManagePermission::class)->name('permission');
    


});

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

require __DIR__.'/auth.php';
