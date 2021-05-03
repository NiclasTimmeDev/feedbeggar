<?php

namespace App\Models;

use App\Models\FeedbackPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static find(string $bucket_id)
 */
class Bucket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id',
        'name',
        'description',
    ];

    /**
     * Get all feedback that belongs to the bucket.
     */

    public function feedback()
    {
        return $this->hasMany(FeedbackPost::class);
    }
}
