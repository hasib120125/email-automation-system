<?php

namespace App\Traits;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait Taggable
{
    public function tags(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    // Tag this model with given tags
    public function tag(array|string|Collection $tags): void
    {
        $tagIds = $this->getTagIds($tags);
        $this->tags()->syncWithoutDetaching($tagIds);
    }

    // Remove tags from this model
    public function untag(array|string|Collection $tags): void
    {
        $tagIds = $this->getTagIds($tags);
        $this->tags()->detach($tagIds);
    }

    // Sync tags (replace all existing tags)
    public function syncTags(array|string|Collection $tags): void
    {
        $tagIds = $this->getTagIds($tags);
        $this->tags()->sync($tagIds);
    }

    // Check if model has specific tag
    public function hasTag(string $tagSlug): bool
    {
        return $this->tags()->where('slug', $tagSlug)->exists();
    }

    // Check if model has any of the given tags
    public function hasAnyTag(array $tagSlugs): bool
    {
        return $this->tags()->whereIn('slug', $tagSlugs)->exists();
    }

    // Check if model has all given tags
    public function hasAllTags(array $tagSlugs): bool
    {
        return $this->tags()->whereIn('slug', $tagSlugs)->count() === count($tagSlugs);
    }

    // Get tag names as array
    public function getTagNamesAttribute(): array
    {
        return $this->tags->pluck('name')->toArray();
    }

    // Get tag slugs as array
    public function getTagSlugsAttribute(): array
    {
        return $this->tags->pluck('slug')->toArray();
    }

    // Scope to filter by tag
    public function scopeWithTag($query, string $tagSlug)
    {
        return $query->whereHas('tags', function ($q) use ($tagSlug) {
            $q->where('slug', $tagSlug);
        });
    }

    // Scope to filter by any of the given tags
    public function scopeWithAnyTag($query, array $tagSlugs)
    {
        return $query->whereHas('tags', function ($q) use ($tagSlugs) {
            $q->whereIn('slug', $tagSlugs);
        });
    }

    // Scope to filter by all given tags
    public function scopeWithAllTags($query, array $tagSlugs)
    {
        foreach ($tagSlugs as $tagSlug) {
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }
        return $query;
    }

    // Helper method to convert various tag formats to IDs
    private function getTagIds(array|string|Collection $tags): array
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }

        if ($tags instanceof Collection) {
            $tags = $tags->toArray();
        }

        $tagIds = [];
        foreach ($tags as $tag) {
            if (is_numeric($tag)) {
                $tagIds[] = $tag;
            } elseif (is_string($tag)) {
                $tagModel = Tag::where('slug', $tag)->orWhere('name', $tag)->first();
                if ($tagModel) {
                    $tagIds[] = $tagModel->id;
                }
            } elseif ($tag instanceof Tag) {
                $tagIds[] = $tag->id;
            }
        }

        return array_unique($tagIds);
    }
}