<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'image_url',
        'profile_id'
    ];

     // A post belongs to a profile
     public function profile()
     {
         return $this->belongsTo(Profile::class);
     }
 
     // A post can have many comments
     public function comments()
     {
         return $this->hasMany(Comment::class);
     }
 
     // A post can have many likes
     public function likes()
     {
         return $this->hasMany(Like::class);
     }
}
