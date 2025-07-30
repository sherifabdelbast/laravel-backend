<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'identify_number',
        'photo',
        'job_title',
        'skills',
        'phone',
        'location',
        'player_ids',
        'password',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'skills' => 'array',
        'player_ids' => 'array',
    ];

    protected $appends = ['url_photo'];

    protected function urlPhoto(): Attribute
    {
        if ($this->photo != null) {
            return Attribute::make(get: fn() => asset('storage/upload/personal_photo/' . $this->photo));
        }
        return Attribute::make(get: fn() => null);
    }

    public function setPlayerIdsAttribute($value)
    {
        if ($this->attributes['player_ids'] == null) {
            $currentPlayerIds = [];
        } else {
            $currentPlayerIds = json_decode($this->attributes['player_ids'], true);
        }

        $newPlayerIds = is_array($value) ? $value : [$value];
        $mergedPlayerIds = array_unique(array_merge($currentPlayerIds, $newPlayerIds));

        $this->attributes['player_ids'] = json_encode($mergedPlayerIds);
    }

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function sprints()
    {
        return $this->hasMany(Sprint::class);
    }

    public function teamMembers()
    {
        return $this->hasMany(Team::class);
    }

    public function Invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function clipboards()
    {
        return $this->hasMany(Clipboard::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }


    public function issueHistories()
    {
        return $this->hasMany(IssueHistory::class);
    }

    public function projectHistories()
    {
        return $this->hasMany(ProjectHistory::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function notifications()
    {
        return $this->hasMany(Status::class);
    }

    public function recipients()
    {
        return $this->hasMany(Recipient::class);
    }


// ************* Scope ***********

    public function ScopeEmail(Builder $builder, $email)
    {
        $builder->where('email', '=', $email);
    }

    public function ScopeIdentifyNumber(Builder $builder, $IdentifyNumber)
    {
        $builder->where('identify_number', '=', $IdentifyNumber);
    }

    public function ScopeId(Builder $builder, $userId)
    {
        $builder->where('id', '=', $userId);
    }

}
