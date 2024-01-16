<?php

namespace LevelUp\Experience\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Experience extends Model
{
    // use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config(key: 'experience-up.user.model'));
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(related: Level::class, foreignKey: 'experience_id');
    }

    use QueryCacheable;

    /**
     * Specify the amount of time to cache queries.
     * Do not specify or set it to null to disable caching.
     *
     * @var int|\DateTime
     */
    protected $cacheFor = 604800; // 1 week

    /**
     * The tags for the query cache. Can be useful
     * if flushing cache for specific tags only.
     *
     * @var null|array
     */
    public $cacheTags = ['experiences'];

    /**
     * A cache prefix string that will be prefixed
     * on each cache key generation.
     *
     * @var string
     */
    public $cachePrefix = 'experiences_';

    /**
     * The cache driver to be used.
     *
     * @var string
     */
    //public $cacheDriver = 'dynamodb';

    /**
     * Set the base cache tags that will be present
     * on all queries.
     */
    protected function getCacheBaseTags(): array
    {
        return [
            'custom_experience_tag',
        ];
    }

    /**
     * When invalidating automatically on update, you can specify
     * which tags to invalidate.
     *
     * @param  string|null  $relation
     * @param  \Illuminate\Database\Eloquent\Collection|null  $pivotedModels
     */
    public function getCacheTagsToInvalidateOnUpdate($relation = null, $pivotedModels = null): array
    {
        return [
            "experience:{$this->id}",
            'experiences',
        ];
    }

    /**
     * Specify the amount of time to cache queries.
     * Set it to null to disable caching.
     *
     * @return int|\DateTime
     */
    protected function cacheForValue()
    {
        //is local
        /*if (app()->environment('local')) {
            return null;
        }*/

        return $this->cacheFor;
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Experience $agent) {
            // ...
            $agent::flushQueryCache(['test']);
        });
        //Update
        static::saved(function (Experience $agent) {
            // ...
            $agent::flushQueryCache(['test']);
        });

        //Delete
        static::deleted(function (Experience $agent) {
            // ...
            $agent::flushQueryCache(['test']);
        });
    }

    //flushQueryCacheItem
    public function flushQueryCacheItem()
    {
        $cacheKeyAgent = 'experience_cache_for';
        //Delete cache
        cache()->forget($cacheKeyAgent);

        return true;
    }

    protected function getCacheForKey()
    {
        return 'experience_cache_for';
    }

    /**
     * The tags for the query cache. Can be useful
     * if flushing cache for specific tags only.
     *
     * @return null|array
     */
    protected function cacheTagsValue()
    {
        return ['experiences'];
    }
}
