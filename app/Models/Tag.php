<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Auto-generate slug from name
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
        
        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // Polymorphic relationship for users (DJs)
    public function users(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(User::class, 'taggable');
    }

    // Generic method to get all taggable models
    public function taggables(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Model::class, 'taggable');
    }

    // Scope to find tags by name or slug
    public function scopeByNameOrSlug($query, string $identifier)
    {
        return $query->where('name', $identifier)->orWhere('slug', $identifier);
    }

    // Get count of tagged items
    public function getTaggedCountAttribute(): int
    {
        return $this->morphToMany(User::class, 'taggable')->count();
    }
}
