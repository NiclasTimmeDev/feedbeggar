<?php

namespace App\Http\Controllers;

use App\Helpers\ExceptionHelper;
use App\Helpers\PermissionHelper;
use App\Models\Bucket;
use App\Models\FeedbackPost;
use App\Models\Project;
use App\Models\User;
use App\Notifications\FeedbackSubmitted;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FeedbackPostController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param string $project_id
     *   The project id.
     */
    public function index(string $project_id)
    {
        try {
            // Check current user.
            $user = Auth::user();

            if (!$user) {
                return ExceptionHelper::customSingleError('Not authenticated', 403);
            }

            $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Load and return feedback for the project.
            $feedback = FeedbackPost::where('project_id', $project_id)->orderBy('created_at', 'DESC')->get();

            return $feedback;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Return a single feedback by project id and feedback id.
     *
     * @param string $project_id
     *   The id of the project.
     * @param string $feedback_id
     *   The id of the feedback.
     */
    public function showSingle(string $project_id, string $feedback_id)
    {
        try {
            // Check current user.
            $user = Auth::user();
            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('Not authenticated', 403);
            }

            // Check if user has access to the project and load it if so.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

            if (!$project instanceof Project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Check if feedback exists and the feedback belongs to the given project.
            $feedback = FeedbackPost::find($feedback_id);
            $feedback_belongs_to_project = $feedback->project_id === $project->id;
            if (!$feedback instanceof FeedbackPost || !$feedback_belongs_to_project) {
                return ExceptionHelper::customSingleError('The feedback does not belong to the project.', 401);
            }

            return $feedback;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Get all feedback for a bucket.
     *
     * @param string $project_id
     *   The project the bucket belongs to.
     * @param string $bucket_id
     *   The bucket for which the feedbach will be loaded.
     */
    public function loadByBucket(string $project_id, string $bucket_id)
    {
        try {
            $user = Auth::user();

            // Check if user has access to the project and load it if so.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Check if the bucket exists and belongs to the project.
            $bucket = Bucket::find($bucket_id);
            if ((int)$bucket->project_id !== (int)$project->id) {
                return ExceptionHelper::customSingleError('You do not have access to this bucket.', 401);
            }


            // If no cache available, get data from database.
            $feedback = FeedbackPost::where('bucket_id', $bucket_id)->orderBy('updated_at', 'DESC')->get();

            return $feedback;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Add a bucket to a feedback.
     *
     * @param Request $request
     *   The request object.
     */
    public function addFeedbackToBucket(Request $request)
    {
        try {
            // Check that all data is provided.
            $feedback_id = $request->feedback_id;
            $bucket_id = $request->bucket_id;
            if (!$feedback_id || !$bucket_id) {
                return ExceptionHelper::customSingleError('Not all data provided.', 400);
            }

            // Load the feedback.
            $feedback = FeedbackPost::find($feedback_id);
            if (!$feedback) {
                return ExceptionHelper::customSingleError('Feedback not found.', 400);
            }

            /*
             * Check if the bucket already belongs to a bucket.
             * This will be necessary when updating the caches.
             */
            $current_bucket = $feedback->bucket_id ?? null;

            if ((int)$current_bucket === (int)$bucket_id) {
                return ExceptionHelper::customSingleError('Nothing to update.', 400);
            }

            // Get the user and check if he has access to the project.
            $user = Auth::user();
            if (!$user) {
                return ExceptionHelper::customSingleError('Access denied.', 401);
            }

            // Check if user has access to the project and load it if so.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $feedback->project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Check that bucket exists.
            $bucket = Bucket::find($bucket_id);
            if (!$bucket) {
                return ExceptionHelper::customSingleError('Bucket not found.', 401);
            }

            // The bucket must belong to the project.
            if ($bucket->project_id !== $project->id) {
                return ExceptionHelper::customSingleError('Bucket does not belong to the given project.', 400);
            }

            // Update the feedback.
            $feedback->update(['bucket_id' => $bucket_id]);

            return $feedback;

        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Store a new feedback in the Database.
     *
     * @param Request $request
     *   The request object.
     */
    public function store(Request $request)
    {
        try {
            // Get relevant data from request.
            $project_id = (int)$request->projectId;
            $type = $request->type;
            $email = $request->email ?? '';
            $text = $request->text;
            $screenshot = $request->screenshot ?? '';
            $browser_info = $request->browserInfo;
            $os_name = $request->osName;
            $path = $request->path;

            // Check all values for correct type.
            if (!is_int($project_id)) {
                return ExceptionHelper::customSingleError('Invalid project ID', 400);
            }

            // Check if feedback type is string.
            if (!is_string($type)) {
                return ExceptionHelper::customSingleError('Invalid type', 400);
            }
            // Check if type has a valid value.
            // if ($type === 'idea' || $type === 'bug' || $type ===  'other') {
            //     return ExceptionHelper::customSingleError('Invalid feedback type.', 400);
            // }

            // If email given, check that it meets standards of email.
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ExceptionHelper::customSingleError('Invalid email', 400);
            }

            // Check if text is string.
            if (!is_string($text)) {
                return ExceptionHelper::customSingleError('Invalid text', 400);
            }

            // Sanitize the string to prevent XSS.
            $text = filter_var($text, FILTER_SANITIZE_STRING);

            // If provided, screenshot must be base64 encoded string.
            if ($screenshot && !is_string($screenshot)) {
                return ExceptionHelper::customSingleError('Invalid screenshot', 400);
            }

            // Check validity of browser info.
            if (!is_array($browser_info) || !isset($browser_info['browserName']) || empty($browser_info['browserName']) || !isset($browser_info['majorVersion']) || empty($browser_info['majorVersion'])) {
                return ExceptionHelper::customSingleError('Invalid browser info', 400);
            }

            // Check if os name is string.
            if (!is_string($os_name)) {
                return ExceptionHelper::customSingleError('Invalid OS name', 400);
            }

            // Check if path is string.
            if (!is_string($path)) {
                return ExceptionHelper::customSingleError('Invalid path name', 400);
            }

            // Check if project exists.
            $project = Project::find($project_id);
            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Check if the project has a url given. If so, only allow requests from that url.
            $url = $project->url;
            if ($url && str_contains($request->server('HTTP_REFERER'), $url)) {
                return ExceptionHelper::customSingleError('Posting feedback is not allowed from this URL.', 401);
            }

            // If given, create S3 image from screenshot.
            $img_url = '';
            if ($screenshot) {
                $img_url = $this->storeBase64ImgInS3((string)$project_id, $screenshot);
            }

            // Store new feedback in DB.
            $new_feedback = new FeedbackPost([
                'project_id' => $project_id,
                'type' => $type,
                'email' => $email,
                'text' => $text,
                'screenshot' => $img_url,
                'browser_name' => $browser_info['browserName'],
                'browser_version' => $browser_info['majorVersion'],
                'os_name' => $os_name,
                'path' => $path
            ]);
            $new_feedback->save();

            // Notify owner of project about the new feedback.
            $this->notifyUser($project, $new_feedback);

            // Send positive response to client.
            return true;
        } catch (Exception $e) {
            return ExceptionHelper::customSingleError($e, 500);
        }
    }

    /**
     * Create an image from a base64 encoded string and store in in S3.
     *
     * @param string $project_id
     *   The project the screenshot belongs to.
     * @param string $data
     *   The base64 encoded image.
     *
     * @return string|false
     *   If successful. the URL of the newly stored img in S3.
     */
    private function storeBase64ImgInS3(string $project_id, string $data)
    {
        // Extract the base 64 from the string.
        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        // Create a name for the image.
        $img_name = 'feedback-screenshots/' . $project_id . '-' . rand(111111111, 999999999) . '.png';

        // Post the image to S3.
        $upload_img = Storage::disk('s3')->put($img_name, $data, 'public');

        // Return if upload failed.
        if (!$upload_img) {
            return false;
        }

        // Retrieve the url that S3 created for the newly created img.
        return Storage::disk('s3')->url($img_name);
    }

    /**
     * Notify the user when a new feedback was submitted.
     */
    private function notifyUser(Project $project, FeedbackPost $feedback)
    {
        $user = User::find($project->user_id);

        if (!$user instanceof User) {
            return false;
        }

        $slug = '/feedback/' . $project->id . '/' . $feedback->id;

        return $user->notify(new FeedbackSubmitted($slug));
    }

    /**
     * Toggle the archiviation status of a feedback.
     *
     * If it is thus far not archived, it will be archived and vice versa.
     *
     * @param Request $request
     */
    public function toggleArchivation(Request $request)
    {

        try {
            $project_id = $request->project_id;
            $feedback_id = $request->feedback_id;

            // Check that all data is provided.
            if (!$project_id || !$feedback_id) {
                return ExceptionHelper::customSingleError('Not all data provided.', 400);
            }

            $user = Auth::user();
            $project = Project::find($project_id);

            // Check that project actually exists.
            if (!$project instanceof Project) {
                return ExceptionHelper::customSingleError('Project not found.', 400);
            }

            // Check that the user actually owns the project.
            if (!PermissionHelper::hasProjectAcces($user, $project)) {
                return ExceptionHelper::customSingleError('Access to project denied.', 401);
            }

            // Load the feedback that shall be updated.
            $feedback = FeedbackPost::find($feedback_id);

            // Check that feedback exists.
            if (!$feedback instanceof FeedbackPost) {
                return ExceptionHelper::customSingleError('Feedback not found.', 400);
            }

            // Toggle (update) the archiviation status of the feedback.
            $feedback->update([
                'is_archived' => !$feedback->is_archived
            ]);

            return $feedback;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $project_id
     * @param string $feedback_id
     */
    public function destroy(string $project_id, string $feedback_id)
    {
        try {
            // Check for user.
            $user = Auth::user();
            if (!$user) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            // Check if user has access to the project and load it if so.
            $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

            if (!$project) {
                return ExceptionHelper::customSingleError('Project not found', 404);
            }

            // Load the feedback by id.
            $feedback = FeedbackPost::find($feedback_id);
            if (!$feedback instanceof FeedbackPost) {
                return ExceptionHelper::customSingleError('Bucket not found.', 401);
            }

            // Check that the feedback belongs to the project.
            if ((int)$feedback->project_id !== (int)$project->id) {
                return ExceptionHelper::customSingleError('You do not have access to this resource.', 403);
            }

            // Delete the bucket.
            $feedback->delete();

            return true;
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }

    public function downloadAsCSV(string $project_id)
    {
        // Check for user.
        $user = Auth::user();
        if (!$user) {
            return ExceptionHelper::customSingleError('You are not logged in.', 401);
        }

        // Check if user has access to the project and load it if so.
        $project = PermissionHelper::checkAccessAndLoadProject($user, $project_id);

        if (!$project) {
            return ExceptionHelper::customSingleError('Project not found', 404);
        }

        // Set the right headers.
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=feedback.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        // Load and return feedback for the project.
        $all_feedback = FeedbackPost::where('project_id', $project_id)->orderBy('created_at', 'DESC')->get();

        $callback = function () use ($all_feedback) {
            $FH = fopen('php://output', 'w');

            foreach ($all_feedback as $feedback) {
                $browser_info = $feedback['browser_name'] . ' ' . $feedback['browser_version'];
                $formatted_date = date("m/d/Y" . $feedback['created_at']);
                fputcsv($FH, [$feedback['type'], $feedback['email'], $feedback['text'], $feedback['path'], $browser_info, $feedback['os_name'], $formatted_date]);
            }
            fclose($FH);
        };

        return response()->stream($callback, 200, $headers);
    }

}
