<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'profile_picture',
    ];

    // A profile can have many posts
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // A profile can have many comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // A profile can like many posts
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // A profile can follow many profiles
    public function following()
    {
        return $this->belongsToMany(Profile::class, 'followers', 'follower_id', 'followed_id');
    }

    // A profile can be followed by many profiles
    public function followers()
    {
        return $this->belongsToMany(Profile::class, 'followers', 'followed_id', 'follower_id');
    }
    
}
