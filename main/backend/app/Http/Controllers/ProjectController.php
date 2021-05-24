<?php

namespace App\Http\Controllers;

use App\Helpers\ExceptionHelper;
use App\Helpers\PermissionHelper;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            // Get all projects.
            $projects = Project::where('user_id', $user_id)->get()->values()->all();

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

            // URL is not required.
            $url = $request->url ?? "";

            if (!$name) {
                return ExceptionHelper::customSingleError('No name provided', 400);
            }

            // Check for user.
            $user = Auth::user();
            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            /*
             * Only users that have a premium subscription
             * can have more than one project.
             */
            if (!$user->is_premium) {
                $projects = $user->projects()->count();

                if ($projects >= 1) {
                    return ExceptionHelper::customSingleError('You are not allowed to create a project. Consider upgrading to a premium subscription in order to create unlimited projects.', 419);
                }
            }


            // Instantiate new project.
            $new_project = new Project([
                'user_id' => $user->id,
                'name' => $name,
                'url' => $url,
                // Premium project if user is premium.
                'is_premium' => $user->is_premium
            ]);

            $new_project->save();


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
            $whitelist = ['name', 'url'];
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

            return $project;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Delete a project.
     *
     * @param string $project_id
     *   The id of the project that will be deleted.
     *
     * @return JsonResponse
     */
    public function destroy(string $project_id)
    {
        try {
            if (!$project_id) {
                return ExceptionHelper::customSingleError('No project id provided.', 400);
            }

            $user = Auth::user();
            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('Access denied.', 401);
            }

            // Check if user has access to the project and load it if so.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Delete all feedback that belongs to this project.
            $project->feedback()->delete();

            // Delete the project.
            $project->delete();

        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }

    }

}
