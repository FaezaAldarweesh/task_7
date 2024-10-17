<?php

namespace App\Models;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that are not mass assignable.
     * @var array<int, string>
     */
    protected $guarded = [
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed'
    ];

     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Return a all users with filter on role.
     * @param   $role
     * @return array
     */
    public function scopeFilter(Builder $query, $role)
    {
        if ($role !== null) {
            $query->where('role', '=', $role);
        }
        return $query;
    }

    /**
     * Returns all tasks assigned to the current user, with potential filtering based on the user's role.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Tasks(){

        return $this->hasMany(Task::class,'assigned_to','id');
    }

    /**
     * Returns all tasks assigned to the current Comments.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Comments(){

        return $this->hasMany(Comment::class);
    }

    /**
     * Returns all tasks assigned to the current Attachments.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Attachments(){

        return $this->hasMany(Attachment::class);
    }
}