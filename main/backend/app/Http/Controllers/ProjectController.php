<?php

namespace App\Http\Controllers;

use App\Helpers\ExceptionHelper;
use App\Helpers\PermissionHelper;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Throwable;

class ProjectController extends Controller
{

    /**
     * The constructor method.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get all projects of the logged in user.
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            // Check for user.
            $user_id = Auth::id();
            if (!$user_id) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            // Try get cached values.
//            $cached_data = Redis::get('projects_by_user_' . $user_id);
//            if ($cached_data) {
//                return $cached_data;
//            }

            // Get all projects.
            $projects = Project::where('user_id', $user_id)->get()->values()->all();

            // Cache the results.
            Redis::set('projects_by_user_' . $user_id, json_encode($projects));

            return $projects;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Store a newly created project.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {

            // Check that the name is given in request.
            $name = $request->name;
            if (!$name) {
                return ExceptionHelper::customSingleError('No name provided', 400);
            }

            // Check for user.
            $user = Auth::user();
            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            // Instantiate new project.
            $new_project = new Project([
                'user_id' => $user->id,
                'name' => $name,
                // Premium project if user is premium.
                'is_premium' => $user->is_premium
            ]);

            $new_project->save();

            // Update the caches if we already have them stored.
            $cached_data = json_decode(Redis::get('projects_by_user_' . $user->id));

            if (is_array($cached_data)) {
                $cached_data[] = $new_project->attributesToArray();
                Redis::set('projects_by_user_' . $user->id, json_encode($cached_data));
            }

            return $new_project;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Update an existing project.
     *
     * @param Request $request
     *   The request object.
     *
     * @return Project|bool|JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $project_id = $request->project_id;
            $updates = $request->updates;

            // Check that updates are provided.
            if (!is_array($updates) || empty($updates)) {
                return ExceptionHelper::customSingleError('No updates given.', 400);
            }

            if (!$project_id) {
                return ExceptionHelper::customSingleError('No project given.', 400);
            }

            /*
             * Whitelist the fields that may be updated.
             * If the client tries to update something else
             * throw a corresponding error.
             */
            $whitelist = ['name'];
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

            // Get the user and check if he has access to the project.
            $user = Auth::user();
            if (!$user) {
                return ExceptionHelper::customSingleError('Access denied.', 401);
            }

            // Check if user has access to the project and load it if so.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            /*
             * Update the project.
             *
             * Since we checked the values of $updates in the beginning,
             * is save to write it this way.
             */
            $project->update($updates);

            /*
             * Check if the list of projects is already cached.
             * If so, update it.
             */
            $cached_data = json_decode(Redis::get('projects_by_user_' . $user->id));
            if ($cached_data) {
                /*
                 * We need to iterate over all existing cached
                 * data and check if the one we updated is in there.
                 * If so, we need to swap it with the updated value.
                 */
                $new_cache = [];
                foreach ($cached_data as $existing_project_in_cache) {
                    // If we arrive at the updated data.
                    if((int) $existing_project_in_cache->id === (int) $project_id) {
                        $new_cache[] = $project->attributesToArray();
                    }
                    // If it's not the updated data, keep the existing data in cache.
                    else if ((int) $existing_project_in_cache->id !== (int) $project_id) {
                        $new_cache[] = $existing_project_in_cache;
                    }
                }
                // Update the cached data.
                Redis::set('projects_by_user_' . $user->id, json_encode($new_cache));
            }

            return $project;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }

    }

}
