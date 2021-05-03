<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, string $project_id)
 * @method static find(mixed $feedback_id)
 */
class FeedbackPost extends Model
{
    use HasFactory;

    // Determine which fields can be filled by the user.
    protected $fillable = ['project_id', 'type', 'email', 'text', 'screenshot', 'browser_name', 'browser_version', 'os_name', 'is_archived', 'bucket_id', 'path'];
}
