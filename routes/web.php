<?php

use App\Http\Controllers\Setting\PersonnelTypeController;
use App\Http\Controllers\Student\StudentTypeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentListController;
use App\Http\Controllers\Personnel\PersonnelController;
use App\Http\Controllers\Setting\PrefixController;
use App\Http\Controllers\Academic\SubjectController;
use App\Http\Controllers\Academic\CurriculumController;
use App\Http\Controllers\Academic\ClassSectionController;
use App\Http\Controllers\Academic\TimetableController;
use App\Http\Controllers\Academic\ScoreController;
use App\Http\Controllers\Academic\GradeController;
use App\Http\Controllers\Academic\PromotionController;
use App\Http\Controllers\Student\StudentAlumniController;
use App\Http\Controllers\Setting\PositionController;
use App\Http\Controllers\Student\StudentCardController;
use App\Http\Controllers\Academic\AcademicYearController;

// --- 1. หน้าทั่วไป ---
Route::view('/', 'welcome');
Route::view('/rp_overview', 'pege.rp_overview');

// --- 2. ส่วนของคนทั่วไป (Guest) ---
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [LoginController::class, 'login']);

// --- 3. ส่วนของคนที่ Login แล้ว (Auth) ---
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard.dashboard', ['user' => Auth::user()]);
    })->name('dashboard');

    // === หน้ารายการนักเรียน (ตาราง/ค้นหา/ลบ) ===
    Route::controller(StudentListController::class)->group(function () {
        Route::get('/students', 'index')->name('students.index');
        Route::get('/students/{id}/show', 'show')->name('students.show');
        Route::delete('/students/{id}', 'destroy')->name('students.destroy');
    });

    // === ฟอร์มกรอกข้อมูลนักเรียน ===
    Route::controller(StudentController::class)->group(function () {
        Route::get('/students/create', 'create')->name('students.create');
        Route::post('/students', 'store')->name('students.store');
        Route::get('/students/{id}/edit', 'edit')->name('students.edit');
        Route::put('/students/{id}', 'update')->name('students.update');
        Route::post('/students/education', 'storeEducation')->name('students.storeEducation');
        Route::post('/students/family', 'storeFamily')->name('students.storeFamily');
        Route::post('/students/health', 'storeHealth')->name('students.storeHealth');
    });

    // === บุคลากร ===
    Route::controller(PersonnelController::class)->prefix('personnels')->name('personnels.')->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::put('/{id}/credentials', 'updateCredentials')->name('updateCredentials');  // ← เพิ่มบรรทัดนี้
        // เพิ่มใน group personnels ที่มีอยู่แล้ว


        // ข้อมูลการศึกษา
        Route::post('/education', 'storeEducation')->name('education.store');
        Route::put('/education/{id}', 'updateEducation')->name('education.update');
        Route::delete('/education/{id}', 'destroyEducation')->name('education.destroy');

        // เกียรติคุณ
        Route::post('/honor', 'storeHonor')->name('honor.store');
        Route::put('/honor/{id}', 'updateHonor')->name('honor.update');
        Route::delete('/honor/{id}', 'destroyHonor')->name('honor.destroy');

        // อบรม/ศึกษา/ดูงาน
        Route::post('/training', 'storeTraining')->name('training.store');
        Route::put('/training/{id}', 'updateTraining')->name('training.update');
        Route::delete('/training/{id}', 'destroyTraining')->name('training.destroy');

        // TOEIC
        Route::post('/toeic', 'storeToeic')->name('toeic.store');
        Route::put('/toeic/{id}', 'updateToeic')->name('toeic.update');
        Route::delete('/toeic/{id}', 'destroyToeic')->name('toeic.destroy');

        // ตำแหน่งงาน (Tab 3)
        Route::post('/position', 'storePosition')->name('position.store');

        // ใบอนุญาต (Tab 4)
        Route::post('/license', 'storeLicense')->name('license.store');
        Route::put('/license/{id}', 'updateLicense')->name('license.update');
        Route::delete('/license/{id}', 'destroyLicense')->name('license.destroy');

        // เครื่องราชฯ
        Route::post('/decoration', 'storeDecoration')->name('decoration.store');
        Route::put('/decoration/{id}', 'updateDecoration')->name('decoration.update');
        Route::delete('/decoration/{id}', 'destroyDecoration')->name('decoration.destroy');
    });


    Route::controller(StudentTypeController::class)->prefix('student-types')->name('student-types.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::put('/{id}/toggle', 'toggle')->name('toggle');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::controller(PersonnelTypeController::class)->prefix('personnel-types')->name('personnel-types.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::put('/{id}/toggle', 'toggle')->name('toggle');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/permissions', 'permissions')->name('permissions');
        Route::post('/{id}/permissions', 'savePermissions')->name('savePermissions');
    });


    Route::controller(PrefixController::class)->prefix('prefixes')->name('prefixes.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::put('/{id}/toggle', 'toggle')->name('toggle');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });


    // === 1. จัดการรายวิชา ===
    Route::controller(SubjectController::class)->prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::put('/{id}/toggle', 'toggle')->name('toggle');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // === 2. จัดการหลักสูตร ===
    Route::controller(CurriculumController::class)->prefix('curriculums')->name('curriculums.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/subjects', 'addSubject')->name('addSubject');
        Route::put('/{id}/subjects/{csId}', 'updateSubject')->name('updateSubject');
        Route::delete('/{id}/subjects/{csId}', 'removeSubject')->name('removeSubject');
        Route::get('/year/{year}', 'byYear')->name('byYear');
        Route::post('/{id}/copy', 'copy')->name('copy');

    });

    // === 3. ห้องเรียน + จัดนักเรียนเข้าห้อง ===
    Route::controller(ClassSectionController::class)->prefix('class-sections')->name('class-sections.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/students', 'manageStudents')->name('students');
        Route::post('/{id}/students', 'assignStudents')->name('assignStudents');
        Route::delete('/{id}/students/{ssId}', 'removeStudent')->name('removeStudent');
    });

    // === 4. ตารางสอน ===
    Route::controller(TimetableController::class)->prefix('timetable')->name('timetable.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/assign', 'storeAssign')->name('storeAssign');
        Route::delete('/assign/{id}', 'destroyAssign')->name('destroyAssign');
        Route::post('/slot', 'storeSlot')->name('storeSlot');
        Route::put('/slot/{id}', 'updateSlot')->name('updateSlot');
        Route::delete('/slot/{id}', 'destroySlot')->name('destroySlot');
        Route::get('/view', 'viewTimetable')->name('view');
        Route::get('/section/{id}', 'sectionView')->name('section');
        Route::delete('/section/{id}/clear', 'clearSection')->name('clearSection');
        Route::post('/section/{id}/import-curriculum', 'importCurriculum')->name('importCurriculum');
    });

    // === 5. บันทึกคะแนน ===
    Route::controller(ScoreController::class)->prefix('scores')->name('scores.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/section/{sectionId}', 'sectionSubjects')->name('section');
        Route::get('/{assignId}', 'manage')->name('manage');
        Route::post('/category', 'storeCategory')->name('storeCategory');
        Route::put('/category/{id}', 'updateCategory')->name('updateCategory');
        Route::delete('/category/{id}', 'destroyCategory')->name('destroyCategory');
        Route::post('/{assignId}/save', 'saveScores')->name('save');
        Route::post('/{assignId}/calculate', 'calculateGrades')->name('calculate');
        Route::post('/{assignId}/setup', 'setupCategories')->name('setup');
        Route::get('/{assignId}/print', 'printScoreSheet')->name('print'); // เพิ่มบรรทัดนี้
    });

    // === 6. ผลการเรียน / เกรด ===
    Route::controller(GradeController::class)->prefix('grades')->name('grades.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/student/{studentId}', 'studentTranscript')->name('transcript');
        Route::get('/student/{studentId}/print', 'printTranscript')->name('transcript.print');
        Route::get('/student/{studentId}/edit', 'editStudentGrades')->name('student.edit');
        Route::put('/{gradeId}', 'updateGrade')->name('update');
        Route::delete('/{gradeId}', 'destroyGrade')->name('destroy');
        Route::get('/section/{sectionId}', 'sectionReport')->name('section');
        Route::get('/gpa-report', 'gpaReport')->name('gpa');
        Route::get('/print/{assignId}', 'printScoreSheet')->name('print');
        Route::get('/excel/{assignId}', 'exportScoreExcel')->name('excel');
    });

    // === 7. เลื่อนชั้น / ย้ายห้อง / บันทึกจบ ===
    Route::controller(PromotionController::class)->prefix('promotions')->name('promotions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/transfer', 'transfer')->name('transfer');
        Route::post('/promote', 'promote')->name('promote');
        Route::post('/graduate', 'graduate')->name('graduate');
        Route::get('/history', 'history')->name('history');
    });


    Route::get('/student-alumni', [StudentAlumniController::class, 'index'])->name('student-alumni.index');

    Route::controller(PositionController::class)->prefix('positions')->name('positions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::put('/{id}/toggle', 'toggle')->name('toggle');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::controller(StudentCardController::class)->prefix('student-cards')->name('student-cards.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/print/{id}', 'printOne')->name('print-one');
        Route::get('/print-all', 'printAll')->name('print-all');
        Route::get('/print-selected', 'printSelected')->name('print-selected');
    });

    // Logout
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    Route::controller(AcademicYearController::class)->prefix('academic-years')->name('academic-years.')->group(function () {
        Route::post('/', 'storeYear')->name('storeYear');
        Route::put('/{id}/current', 'setYearCurrent')->name('setYearCurrent');
        Route::delete('/{id}', 'destroyYear')->name('destroyYear');

        Route::post('/semester', 'storeSemester')->name('storeSemester');
        Route::put('/semester/{id}/current', 'setSemesterCurrent')->name('setSemesterCurrent');
        Route::delete('/semester/{id}', 'destroySemester')->name('destroySemester');
    });

});
