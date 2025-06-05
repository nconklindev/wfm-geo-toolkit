<?php

namespace App\Models;

use App\Observers\KnownIpAddressObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

#[ObservedBy(KnownIpAddressObserver::class)]
class KnownIpAddress extends Model
{
    use HasFactory;
    use Notifiable;
    use Searchable;

    protected $fillable = [
        'start',
        'end',
        'name',
        'description',
    ];

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start' => $this->start,
            'end' => $this->end,
            'user_id' => $this->user_id
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
