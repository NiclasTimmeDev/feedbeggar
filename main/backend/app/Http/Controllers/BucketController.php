<?php

namespace App\Http\Controllers;

use App\Helpers\ExceptionHelper;
use App\Helpers\PermissionHelper;
use App\Models\Bucket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Throwable;

class BucketController extends Controller
{
    /**
     * The constructor method.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @param string $project_id
     *   The id of the project.
     *
     * @return JsonResponse
     */
    public function index(string $project_id)
    {
        try {
            // Check for user.
            $user = Auth::user();
            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            // Find the project.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Try to get the data from cache.
//            $cached_buckets = Redis::get('buckets_by_project_' . $project_id);
//            if ($cached_buckets) {
//                return $cached_buckets;
//            }

            // Send the buckets.
            $buckets = Bucket::where('project_id', $project_id)->orderBy('updated_at', 'DESC')->get()->values()->all();

            // Cache the result.
            Redis::set('buckets_by_project_' . $project_id, json_encode($buckets));

            return response()->json($buckets);
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Load a single bucket by project id and bucket id.
     *
     * @param string $project_id
     *   The id of the project the bucket belongs to.
     * @param string $bucket_id
     *   The id of the bucket that will be loaded.
     *
     * @return JsonResponse
     */
    public function loadSingleBucket(string $project_id, string $bucket_id)
    {
        try {
            // Check for user.
            $user = Auth::user();
            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            // Find the project.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Load the bucket by id.
            $bucket = Bucket::find($bucket_id);
            if (!$bucket) {
                return ExceptionHelper::customSingleError('Bucket not found.', 401);
            }

            // Check that the bucket belongs to the project.
            if ((int)$bucket->project_id !== (int)$project->id) {
                return ExceptionHelper::customSingleError('You do not have access to this resource.', 403);
            }

            // Return the bucket to the client.
            return $bucket;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Store a newly created bucket in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $project_id = $request->projectID;
            $name = $request->name;
            $description = $request->description;

            // Check for user.
            $user = Auth::user();
            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            // Find the project.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Create new bucket object.
            $new_bucket = new Bucket([
                'project_id' => $project_id,
                'name' => $name,
                'description' => $description
            ]);
            $new_bucket->save();

            return response()->json($new_bucket);
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     *   The rwquest object.
     *
     * @param Bucket $bucket
     *   The bucket.
     *
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $bucket_id = $request->bucket_id;
            $updates = $request->updates;

            // Check that the updates are given in form of array.
            if (!is_array($updates)) {
                return ExceptionHelper::customSingleError('Updates provided in incorrect format.', 400);
            }

            // Determine which values may be updated and check if the given updates contain invalid values.
            $whitelist = ['name', 'description'];

            $error = FALSE;
            foreach ($updates as $key => $value) {
                if (!in_array($key, $whitelist)) {
                    $error = TRUE;
                    break;
                }
            }

            if ($error) {
                return ExceptionHelper::customSingleError('Not all given values may be updated.', 400);
            }

            // Load the bucket of interest.
            $bucket = Bucket::find($bucket_id);
            if (!$bucket instanceof Bucket) {
                return ExceptionHelper::customSingleError('The bucket was not found.', 400);
            }

            $user = Auth::user();
            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            // Find the project.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $bucket->project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            /**
             * Update the bucket.
             *
             * Since we checked the values of $updates in the beginning,
             * is save to write it this way.
             */
            $bucket->update($updates);

            // Flush cache.
            //@TODO: Update cache instead of removing it.
            Redis::del('buckets_by_project_' . $project->id);

            return response()->json($bucket);
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Bucket $bucket
     *   The bucket.
     *
     * @return JsonResponse
     */
    public function destroy(string $project_id, string $bucket_id)
    {
        try {
            // Check for user.
            $user = Auth::user();
            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            // Find the project.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Load the bucket by id.
            $bucket = Bucket::find($bucket_id);
            if (!$bucket) {
                return ExceptionHelper::customSingleError('Bucket not found.', 401);
            }

            // Check that the bucket belongs to the project.
            if ((int)$bucket->project_id !== (int)$project->id) {
                return ExceptionHelper::customSingleError('You do not have access to this resource.', 403);
            }

            // Get all feedback related to the bucket.
            $feedbacks = $bucket->feedback;

            // Delete the bucket id from all related feedback.
            foreach ($feedbacks as $feedback) {
                $feedback->update([
                    'bucket_id' => NULL
                ]);
            }

            // Delete the bucket.
            $bucket->delete();

            // Flush cache.
            Redis::del('buckets_by_project_' . $project->id);

            return response()->json(true);
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }
}
