<?php

use App\Http\Controllers\BucketController;
use App\Http\Controllers\FeedbackPostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * |--------------------------------------------------------------------------
 * | Feedback related.
 * |--------------------------------------------------------------------------
 */
// Store new feedback.
Route::post('/feedback', [FeedbackPostController::class, 'store']);

// Get feedback for one project.
Route::get('/feedback/{project_id}', [FeedbackPostController::class, 'index']);

// Get single feedback.
Route::get('/feedback/{project_id}/{feedback_id}', [FeedbackPostController::class, 'showSingle']);

// Add feedback to a bucket.
Route::post('/feedback/addbucket', [FeedbackPostController::class, 'addFeedbackToBucket']);

// Toggle archiviation status of a feedback.
Route::patch('/feedback/archive', [FeedbackPostController::class, 'toggleArchivation']);

// Get all feedback for a specific bucket.
Route::get('/feedback/bucket/{project_id}/{bucket_id}', [FeedbackPostController::class, 'loadByBucket']);

// Download feedback as CSV file.
Route::get('/download/feedback/{project_id}', [FeedbackPostController::class, 'downloadAsCSV']);

// Delete feedback.
Route::delete('/feedback/{project_id}/{feedback_id}', [FeedbackPostController::class, 'destroy']);

/**
 * |--------------------------------------------------------------------------
 * | Projects related.
 * |--------------------------------------------------------------------------
 */
// Get all projects of logged in user.
Route::get('/projects', [ProjectController::class, 'index']);

// Create new project.
Route::post('/projects', [ProjectController::class, 'store']);

// Update an existing project.
Route::patch('/projects', [ProjectController::class, 'update']);

// Delete a project.
Route::delete('/projects/{project_id}', [ProjectController::class, 'destroy']);

/**
 * |--------------------------------------------------------------------------
 * | Buckets related.
 * |--------------------------------------------------------------------------
 */
// Get all buckets for a project.
Route::get('/buckets/{project_id}', [BucketController::class, 'index']);

// Get a single bucket.
Route::get('/buckets/{project_id}/{bucket_id}', [BucketController::class, 'loadSingleBucket']);

// Create new bucket.
Route::post('/buckets', [BucketController::class, 'store']);

// Delete a bucket.
Route::delete('/buckets/{project_id}/{bucket_id}', [BucketController::class, 'destroy']);

// Update a bucket.
Route::patch('/buckets', [BucketController::class, 'update']);

/**
 * |--------------------------------------------------------------------------
 * | Profile related.
 * |--------------------------------------------------------------------------
 */
Route::patch('/profile', [ProfileController::class, 'update']);
