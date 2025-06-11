<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{

    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
//        'name',
        'username',
        'email',
        'password',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->support_id)) {
                $model->support_id = Str::uuid();
            }
        });
    }

    public function groups(): HasMany
    {
        // Automatically load the relationships
        return $this->hasMany(Group::class, 'user_id');
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->username)
            ->explode(' ')
            ->map(fn(string $username) => Str::of($username)->substr(0, 1)->upper())
            ->implode('');
    }

    public function knownIpAddresses(): HasMany
    {
        return $this->hasMany(KnownIpAddress::class, 'user_id');
    }

    public function knownPlaces(): HasMany
    {
        return $this->hasMany(KnownPlace::class, 'user_id');
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(BusinessStructureNode::class, 'user_id');
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'App.Models.User.'.$this->id;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

}
