<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

//Route::get('projects/export', [ReportController::class, 'exportProjectReport'])->name('projects.report-export');

Route::middleware(['auth:api', 'checkPermission'])->group(function () {
    Route::get('logout', [UserController::class, 'logout'])->name('user.logout');


    Route::prefix('role')->group(function () {
        Route::post('create', [RoleController::class, 'create'])->name('role.create');
        Route::get('users', [RoleController::class, 'userRoles'])->name('role.userRoles');
        Route::get('users/search/{term}', [RoleController::class, 'userRoleSearch'])->name('role.userRoleSearch');
        Route::get('users/name/{term}', [RoleController::class, 'userSearch'])->name('role.userSearch');
        Route::get('users/get/{id}', [RoleController::class, 'userDetails'])->name('role.userDetails');
        Route::post('assign-role', [RoleController::class, 'assignRoleToUser'])->name('role.assignRoleToUser');

        Route::post('remove-role', [RoleController::class, 'removeRoleFromUser'])->name('role.removeRoleFromUser');
        Route::get('admin/all', [RoleController::class, 'getAll'])->name('role.get');
        Route::get('get/{id}', [RoleController::class, 'details'])->name('role.details');
        Route::get('search/{term}', [RoleController::class, 'search'])->name('role.search');

        Route::post('assign-super-admin-role', [RoleController::class, 'assignSuperAdminToUser'])->name('role.assignSuperAdminToUser');
        Route::post('give-permission-to-role', [RoleController::class, 'givePermissionToRole'])->name('role.givePermissionToRole');
        Route::post('revoke-permission-from-role', [RoleController::class, 'revokePermissionFromRole'])->name('role.revokePermissionFromRole');
        Route::delete('delete/{roleId}', [RoleController::class, 'deleteRole'])->name('role.delete');

    });

    // Permission Route
    Route::prefix('permission')->group(function () {
        Route::get('sync-route-to-permission', [PermissionController::class, 'addRouteAsPermission'])->name('permission.addRouteAsPermission'); //super admin
        Route::get('all', [PermissionController::class, 'getAllPermission'])->name('permission.getAll');
        Route::get('search/{term}', [PermissionController::class, 'searchPermission'])->name('permission.search');
    });

    //Project
    Route::prefix('projects')->group(function () {
        Route::get('list', [ProjectController::class, 'list'])->name('projects.list');
        Route::post('create', [ProjectController::class, 'create'])->name('projects.create');
        Route::get('get/{id}', [ProjectController::class, 'get'])->name('projects.get');
        Route::patch('update/{id}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('delete/{id}', [ProjectController::class, 'delete'])->name('projects.delete');
    });

    //Task
    Route::prefix('tasks')->group(function () {
        Route::get('list', [TaskController::class, 'list'])->name('tasks.list');
        Route::post('create', [TaskController::class, 'create'])->name('tasks.create');
        Route::get('get/{id}', [TaskController::class, 'get'])->name('tasks.get');
        Route::patch('update/{id}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('delete/{id}', [TaskController::class, 'delete'])->name('tasks.delete');
        Route::get('sub-task-list/{taskId}',[TaskController::class, 'subTaskList'])->name('task.sub-task-list');
        Route::post('task-status-update/{subtaskId}',[TaskController::class, 'taskStatusUpdate'])->name('task.task-status-update'); //for team member
        Route::get('project-wise-task-list/{projectId}',[TaskController::class, 'projectWiseTaskList'])->name('task.project-wise-task-list'); //for team member
        Route::get('user/{userId}/tasks', [TaskController::class, 'getUserTasks'])->name('user.tasks');
    });

    //report
    Route::prefix('reports')->group(function () {
         Route::post('projects', [ReportController::class, 'generateProjectReport'])->name('projects.report');
         Route::post('projects/export', [ReportController::class, 'exportProjectReport'])->name('projects.report-export');
    });
});
