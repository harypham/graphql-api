<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use SoftDeletes;
    protected $table = 'comments';
    protected $dates = ['deleted_at'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
