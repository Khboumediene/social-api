<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = ['profile_id', 'post_id'];

     // A like belongs to a post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

     // A like belongs to a profile
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
