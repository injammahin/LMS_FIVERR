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
use App\Http\Controllers\Student\AssignmentSubmissionController as StudentAssignmentSubmissionController;
use App\Http\Controllers\Student\NotificationController as StudentNotificationController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\AiTrainingController;
use App\Http\Controllers\Admin\AiTrainingFileController;
use App\Http\Controllers\Student\DictionaryLookupController;


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
Route::middleware(['throttle:20,1'])
    ->post('/ai/public/stream', [AiChatController::class, 'publicStream'])
    ->name('ai.public.stream');
    
Route::get('/dashboard', function () {

    $user = auth()->user();

    if (!$user) {
        return redirect()->route('login');
    }

    return match ($user->role) {
        'admin'   => redirect()->route('admin.dashboard'),
        'teacher' => redirect()->route('teacher.dashboard'),
        'student' => redirect()->route('student.dashboard'),
        'staff'   => redirect()->route('staff.dashboard'),
        default   => redirect('/'),
    };

})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/chat-users',[ChatController::class,'users'])->name('chat.users');

Route::get('/chat/{id}',[ChatController::class,'chat'])->name('chat.view');
Route::post('/chat/seen', [ChatController::class,'markSeen'])->name('chat.seen');
Route::post('/chat/send',[ChatController::class,'send'])->name('chat.send');
    Route::post('/ai/chat/stream', [AiChatController::class, 'stream'])
          ->name('ai.chat.stream');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth', 'admin','active'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
           ->name('dashboard');
        Route::get('/students/reports', [\App\Http\Controllers\Admin\StudentController::class, 'reports'])
              ->name('admin.students.reports');
        Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
        Route::get('teachers/reports', [\App\Http\Controllers\Admin\TeacherController::class, 'reports'])
              ->name('teachers.reports');
        Route::get('assignments/graded', [AssignmentController::class, 'graded'])
        ->name('assignments.graded');
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
        Route::post('/quill/upload', [\App\Http\Controllers\Admin\QuillUploadController::class, 'store'])
              ->name('quill.upload');
        Route::patch('students/{student}/toggle-status', 
                [\App\Http\Controllers\Admin\StudentController::class, 'toggleStatus']
               )->name('students.toggle-status');
        Route::patch('teachers/{teacher}/toggle-status', [TeacherController::class, 'toggleStatus'])
              ->name('teachers.toggle-status');
        Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])
              ->name('analytics.index');
        Route::get('staffs/reports', [\App\Http\Controllers\Admin\StaffController::class, 'reports'])->name('staffs.reports');
        Route::resource('staffs', \App\Http\Controllers\Admin\StaffController::class);

        Route::get('staffs/{staff}/courses', [\App\Http\Controllers\Admin\StaffController::class, 'editCourses'])
            ->name('staffs.courses.edit');

        Route::post('staffs/{staff}/courses', [\App\Http\Controllers\Admin\StaffController::class, 'updateCourses'])
            ->name('staffs.courses.update');

        // suspend/activate
        Route::patch('staffs/{staff}/toggle-status', [\App\Http\Controllers\Admin\StaffController::class, 'toggleStatus'])
            ->name('staffs.toggle-status');

        //---------------------------AI-------------------------
        Route::prefix('ai-assistant')->name('ai.')->group(function () {

            // KB
            Route::get('knowledge', [AiTrainingController::class, 'index'])->name('kb.index');
            Route::get('knowledge/create', [AiTrainingController::class, 'create'])->name('kb.create');
            Route::post('knowledge', [AiTrainingController::class, 'store'])->name('kb.store');
            Route::get('knowledge/{kb}/edit', [AiTrainingController::class, 'edit'])->name('kb.edit');
            Route::put('knowledge/{kb}', [AiTrainingController::class, 'update'])->name('kb.update');
            Route::delete('knowledge/{kb}', [AiTrainingController::class, 'destroy'])->name('kb.destroy');

            // Files
            Route::get('files', [AiTrainingFileController::class, 'index'])->name('files.index');
            Route::get('files/create', [AiTrainingFileController::class, 'create'])->name('files.create');
            Route::post('files', [AiTrainingFileController::class, 'store'])->name('files.store');

        });
            
});





Route::middleware(['auth', 'role:student','active'])
    ->prefix('student')
    ->name('student.')
      ->scopeBindings()
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/dictionary/lookup', [DictionaryLookupController::class, 'lookup'])
        ->name('dictionary.lookup');

        // This is where a student enters their division content
        Route::get('/division/{division}', [DashboardController::class, 'division'])
            ->name('division.show');
        Route::get('/division/{division}/subject/{subject}', [SubjectBrowseController::class, 'show'])
            ->name('subjects.show');
                   // ✅ Lesson
        Route::get('/courses/{course}/lessons/{lesson}', [LessonViewController::class, 'show'])
            ->name('lessons.show');
        Route::post('/courses/{course}/lessons/{lesson}/done', [\App\Http\Controllers\Student\LessonViewController::class, 'markDone'])
        ->name('lessons.done');

        // ✅ Quiz
        Route::get('/courses/{course}/quizzes/{quiz}', [QuizViewController::class, 'show'])
            ->name('quizzes.show');
        Route::get('/grades', [\App\Http\Controllers\Student\GradesController::class, 'index'])
            ->name('grades.index');
            

        // ✅ Assignment
        Route::get('/courses/{course}/assignments/{assignment}', [AssignmentViewController::class, 'show'])
            ->name('assignments.show');
        Route::get('/quizzes/{quiz}/start', [QuizAttemptController::class, 'start'])->name('quiz.start');
        Route::get('/attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('quiz.attempt.show');
        Route::post('/attempts/{attempt}/submit', [QuizAttemptController::class, 'submit'])->name('quiz.attempt.submit');
        Route::get('/attempts/{attempt}/result', [QuizAttemptController::class, 'result'])->name('quiz.attempt.result');
        Route::post('/courses/{course}/assignments/{assignment}/submit', [StudentAssignmentSubmissionController::class, 'store'])
               ->name('assignments.submit');
        Route::get('/notifications', [StudentNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [StudentNotificationController::class, 'readAll'])->name('notifications.readAll');

        Route::post('/notifications/{id}/read', [StudentNotificationController::class, 'read'])->name('notifications.read');
        Route::post('/notifications/{id}/unread', [StudentNotificationController::class, 'unread'])->name('notifications.unread');
        Route::get('/chat', [ChatController::class,'users'])
            ->name('chat.users');
        Route::get('/chat/{id}', [ChatController::class,'chat'])
            ->name('chat.view');
    });

use App\Http\Controllers\Teacher\SubmissionController;

Route::middleware(['auth', 'role:teacher','active'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {

        Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/courses', [\App\Http\Controllers\Teacher\CourseController::class, 'index'])
            ->name('courses.index');

        Route::get('/courses/{course}', [\App\Http\Controllers\Teacher\CourseController::class, 'show'])
            ->name('courses.show');

        Route::get('/submissions', [SubmissionController::class, 'index'])
            ->name('submissions.index');

        // ✅ Assignment submission review
        Route::get('/assignments/{assignment}/submissions/{submission}', [SubmissionController::class, 'showAssignment'])
            ->name('assignments.submissions.show');

        Route::post('/assignments/{assignment}/submissions/{submission}/grade', [SubmissionController::class, 'gradeAssignment'])
            ->name('assignments.submissions.grade');

        // ✅ Quiz attempt review
        Route::get('/quiz-attempts/{attempt}', [SubmissionController::class, 'showAttempt'])
            ->name('quiz.attempts.show');

        Route::post('/quiz-attempts/{attempt}/grade', [SubmissionController::class, 'gradeAttempt'])
            ->name('quiz.attempts.grade');

        // ✅ Notifications
        Route::get('/notifications', [\App\Http\Controllers\Teacher\NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Teacher\NotificationController::class, 'markRead'])
            ->name('notifications.read');

        Route::post('/notifications/read-all', [\App\Http\Controllers\Teacher\NotificationController::class, 'markAllRead'])
            ->name('notifications.read_all');
        Route::get('/courses/{course}/students/{student}', [\App\Http\Controllers\Teacher\CourseController::class, 'studentProgress'])
              ->name('courses.students.show');
         Route::get('/chat', [ChatController::class,'users'])
            ->name('chat.users');
        Route::get('/chat/{id}', [ChatController::class,'chat'])
            ->name('chat.view');

            

    });

Route::middleware(['auth', 'role:staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {

        Route::get('/dashboard', [\App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/courses', [\App\Http\Controllers\Staff\CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}', [\App\Http\Controllers\Staff\CourseController::class, 'show'])->name('courses.show');

        Route::get('/submissions', [\App\Http\Controllers\Staff\SubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/assignments/{assignment}/submissions/{submission}', [\App\Http\Controllers\Staff\SubmissionController::class, 'showAssignment'])->name('assignments.submissions.show');
        Route::get('/quiz-attempts/{attempt}', [\App\Http\Controllers\Staff\SubmissionController::class, 'showAttempt'])->name('quiz.attempts.show');

        Route::get('/courses', [\App\Http\Controllers\Staff\CourseController::class, 'index'])
            ->name('courses.index');

        Route::get('/courses/{course}', [\App\Http\Controllers\Staff\CourseController::class, 'show'])
            ->name('courses.show');

        // view submissions (NO grade routes here)
        Route::get('/submissions', [\App\Http\Controllers\Staff\SubmissionController::class, 'index'])
            ->name('submissions.index');

        Route::get('/assignments/{assignment}/submissions/{submission}', [\App\Http\Controllers\Staff\SubmissionController::class, 'showAssignment'])
            ->name('assignments.submissions.show');

        Route::get('/quiz-attempts/{attempt}', [\App\Http\Controllers\Staff\SubmissionController::class, 'showAttempt'])
            ->name('quiz.attempts.show');
        Route::get('/courses/{course}/students/{student}', [\App\Http\Controllers\Staff\CourseController::class, 'studentProgress'])
            ->name('courses.students.show');
        Route::get('/courses/{course}/activity', [\App\Http\Controllers\Staff\CourseController::class, 'activity'])
            ->name('courses.activity');
        Route::get('/quiz-attempts/{attempt}/pdf', [\App\Http\Controllers\Staff\SubmissionController::class, 'downloadAttemptPdf'])
           ->name('quiz.attempts.pdf');
        Route::get('/assignments/{assignment}/submissions/{submission}/pdf', [\App\Http\Controllers\Staff\SubmissionController::class, 'downloadAssignmentPdf'])
           ->name('assignments.submissions.pdf');
            });



require __DIR__.'/auth.php';
