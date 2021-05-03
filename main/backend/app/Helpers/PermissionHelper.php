<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Redis;

class PermissionHelper
{

    /**
     * Check if the user has permission to a project.
     *
     * @param \App\User $user
     *   The respective user.
     * @param string $project_id
     *   The id of the project.
     *
     * @return Boolean
     *   true if user is member.
     */
    public static function hasProjectAcces($user, $project): bool
    {
        // Check if $user actually is a user object.
        if (!$user instanceof User) {
            return false;
        }

        // Check if $project actually is a project object.
        if (!$project instanceof Project) {
            return false;
        }

        // Check if user is team member.
        return $project->user_id === $user->id;
    }

    /**
     * Check if the user  has permission to access a given process.
     *
     * @param \App\Models\User $user
     *   The respective user.
     * @param string $project_id
     *   The id of the team.
     *
     * @return Boolean | \App\Models\Project
     *   The project or false.
     */
    public static function checkAccessAndLoadProject($user, $project_id)
    {
        /*
         * No access was stored in cache. Thus, check if manually.
         */
        if (!$user instanceof User) {
            return false;
        }

        // Check if we have cached access.
//        $access_cached = Redis::get('project_access_' . $user->id . '_' . $project_id);
//
//        // Positive access stored in cache.
//        if((int)$access_cached === 1){
//            return $user->projects()->where('id', $project_id)->get()->values()[0];
//        }
//
//        // Access forbidden from cache.
//        if((int) $access_cached === 0) {
//            return false;
//        }

        // Check if user has permission to access the process.
        $projects = $user->projects()->where('id', $project_id)->get()->values()->all();

        $project = $projects[0];

        // Cache the result.
        Redis::set('project_access_' . $user->id . '_' . $project_id, $project instanceOf Project ? 1 : 0);

        // Return the access.
        if (!$project instanceof Project) {
            return false;
        }

        return $project;
    }
}
