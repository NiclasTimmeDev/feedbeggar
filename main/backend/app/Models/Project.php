<?php

namespace App\Models;

use App\Models\FeedbackPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static find(string $project_id)
 */
class Project extends Model
{
    use HasFactory;

    // Determine which fields can be filled by the user.
    protected $fillable = ['user_id', 'name', 'url', 'is_premium'];

    /**
     * Get all feedback that belong to the project.
     */

    public function feedback()
    {
        return $this->hasMany(FeedbackPost::class);
    }
}
