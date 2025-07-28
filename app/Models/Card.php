<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Card extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'company',
        'position',
        'email',
        'phone',
        'mobile',
        'address',
        'company_address',
        'logo',
        'qr_code',
        'slug',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the card
     */


    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($card) {
            if (empty($card->slug)) {
                $card->slug = static::generateUniqueSlug($card->name);
            }
        });
        
        static::updating(function ($card) {
            if ($card->isDirty('name') && empty($card->slug)) {
                $card->slug = static::generateUniqueSlug($card->name);
            }
        });
    }

    public static function generateUniqueSlug($name)
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug . '-' . time();
        $counter = 1;
        
        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . time() . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

        public function getRouteKeyName()
    {
        return 'slug';
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the QR code URL
     */
    public function getQrCodeUrlAttribute(): ?string
    {
        return $this->qr_code ? url("/storage/{$this->qr_code}") : null;
    }

    /**
     * Get the card URL
     */
    public function getPublicUrlAttribute(): string
    {
        return url("/api/public/card/{$this->slug}");
    }
    /**
     * Get the logo public URL
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? url("/storage/{$this->logo}") : null;
    }   
    protected $appends = ['public_url', 'qr_code_url', 'logo_url'];

    /**
     * Scope for active cards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for cards by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
