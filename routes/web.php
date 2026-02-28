<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\QuizQuestionController;
use App\Http\Controllers\Admin\QuizOptionController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AssignmentSubmissionController;
use App\Http\Controllers\Admin\TeacherController;

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\SubjectBrowseController;
use App\Http\Controllers\Student\LessonViewController;
use App\Http\Controllers\Student\QuizViewController;
use App\Http\Controllers\Student\AssignmentViewController;
use App\Http\Controllers\Student\QuizAttemptController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::view('/courses', 'admin.courses.index')->name('courses.index');
        Route::view('/courses/create', 'admin.courses.create')->name('courses.create');

        Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
        Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class);
        Route::resource('divisions', DivisionController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('courses', CourseController::class);
        Route::resource('courses.lessons', LessonController::class);
        Route::resource('courses.quizzes', QuizController::class);
        Route::resource('quizzes.questions', QuizQuestionController::class);
        Route::resource('questions.options', QuizOptionController::class);
        Route::resource('courses.assignments', AssignmentController::class);
        Route::get('assignments/{assignment}/submissions', [AssignmentSubmissionController::class, 'index'])
            ->name('assignments.submissions.index');
        Route::get('assignments/{assignment}/submissions/{submission}', [AssignmentSubmissionController::class, 'show'])
            ->name('assignments.submissions.show');
        Route::post('assignments/{assignment}/submissions/{submission}/grade', [AssignmentSubmissionController::class, 'grade'])
            ->name('assignments.submissions.grade');
        Route::get('teachers/{teacher}/courses', [TeacherController::class, 'editCourses'])
            ->name('teachers.courses.edit');

        Route::post('teachers/{teacher}/courses', [TeacherController::class, 'updateCourses'])
            ->name('teachers.courses.update');
            
});





Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // This is where a student enters their division content
        Route::get('/division/{division}', [DashboardController::class, 'division'])
            ->name('division.show');
        Route::get('/division/{division}/subject/{subject}', [SubjectBrowseController::class, 'show'])
            ->name('subjects.show');
                   // ✅ Lesson
        Route::get('/courses/{course}/lessons/{lesson}', [LessonViewController::class, 'show'])
            ->name('lessons.show');

        // ✅ Quiz
        Route::get('/courses/{course}/quizzes/{quiz}', [QuizViewController::class, 'show'])
            ->name('quizzes.show');

        // ✅ Assignment
        Route::get('/courses/{course}/assignments/{assignment}', [AssignmentViewController::class, 'show'])
            ->name('assignments.show');
        Route::get('/quizzes/{quiz}/start', [QuizAttemptController::class, 'start'])->name('quiz.start');
        Route::get('/attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('quiz.attempt.show');
        Route::post('/attempts/{attempt}/submit', [QuizAttemptController::class, 'submit'])->name('quiz.attempt.submit');
        Route::get('/attempts/{attempt}/result', [QuizAttemptController::class, 'result'])->name('quiz.attempt.result');
        
    });

Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::view('/dashboard', 'teacher.dashboard')->name('dashboard');
    });

Route::middleware(['auth', 'role:staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::view('/dashboard', 'staff.dashboard')->name('dashboard');
    });
require __DIR__.'/auth.php';
