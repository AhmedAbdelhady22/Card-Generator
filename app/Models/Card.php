<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
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
        return url("/card/{$this->slug}");
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

    public function scopeInactive($query)
    {
    return $query->where('is_active', false);
    }

    /**
     * Scope for cards by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ✅ MOVE: QR code generation to model
    public function generateQrCode(): void
    {
        try {
            $cardUrl = $this->public_url;
            Log::info("🔄 Generating QR code for card {$this->id} with URL: {$cardUrl}");
            
            $qrCodeContent = QrCode::format('svg')
                ->size(300)
                ->margin(2)
                ->generate($cardUrl);
            
            $filename = "qr-codes/card-{$this->id}.svg";
            
            Storage::disk('public')->makeDirectory('qr-codes');
            
            $saved = Storage::disk('public')->put($filename, $qrCodeContent);
            
            if ($saved) {
                $this->update(['qr_code' => $filename]);
                Log::info("✅ QR code saved successfully: {$filename}");
            }
            
        } catch (\Exception $e) {
            Log::error("❌ QR code generation failed for card {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    // ✅ MOVE: File upload logic to model
    public function uploadLogo($logoFile): ?string
    {
        if (!$logoFile || !$logoFile->isValid()) {
            return null;
        }

        // Delete old logo if exists
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            Storage::disk('public')->delete($this->logo);
        }

        Log::info('Logo upload attempt:', [
            'original_name' => $logoFile->getClientOriginalName(),
            'mime_type' => $logoFile->getClientMimeType(),
            'size' => $logoFile->getSize(),
        ]);

        $logoPath = $logoFile->store('logos', 'public');
        
        $this->update(['logo' => $logoPath]);
        
        Log::info('Logo stored successfully:', ['path' => $logoPath]);
        
        return $logoPath;
    }

    // ✅ MOVE: Generate PDF data to model
    public function getPdfData(): array
    {
        return [
            'card' => $this,
            'qr_code_path' => $this->qr_code ? storage_path('app/public/' . $this->qr_code) : null,
            'logo_path' => $this->logo ? storage_path('app/public/' . $this->logo) : null,
        ];
    }

    // ✅ MOVE: Delete related files to model
    public function deleteFiles(): void
    {
        if ($this->logo) {
            Storage::disk('public')->delete($this->logo);
        }
        if ($this->qr_code) {
            Storage::disk('public')->delete($this->qr_code);
        }
    }

    // ✅ MOVE: Card creation logic to model
    public static function createWithQrCode(array $data): self
    {
        $card = self::create($data);
        $card->generateQrCode();
        return $card;
    }

    // ADMIN MANAGEMENT METHODS
    public function deleteByAdmin(): bool
    {
        try {
            // Delete files first
            $this->deleteFiles();
            
            // Delete the card
            return $this->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting card by admin: ' . $e->getMessage());
            return false;
        }
    }

    public static function getAdminCardsList()
    {
        return self::with('user')->latest()->paginate(15);
    }

    // QUERY SCOPES (Add to existing)
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month);
    }

    public function scopeWithUser($query)
    {
        return $query->with('user:id,name,email');
    }

    public function generatePdf()
    {
        $pdfData = $this->getPdfData();
        $html = view('cards.pdf', $pdfData)->render();

        $pdf = \Spatie\Browsershot\Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(60)
            ->setOption('--disable-web-security', true)
            ->pdf();

        return $pdf;
    }

    public function getPdfFilename(): string
    {
        return \Illuminate\Support\Str::slug($this->name . '-' . $this->company) . '-business-card.pdf';
    }
}
