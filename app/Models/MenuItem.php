<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MenuItem extends Model
{
    protected $fillable = [
        'coffee_title',
        'single_price',
        'double_price',
        'available',
        'portion_available',
        'image_path',
        'category_id',
    ];

    protected $casts = [
        'available' => 'boolean',
        'single_price' => 'integer',
        'double_price' => 'integer',
        'portion_available' => 'integer',
    ];

    /**
     * Public URL to the image, for clients (e.g., Android app).
     */
    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        // Returns full URL via the public disk (requires storage:link)
        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Get the category that owns the menu item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
