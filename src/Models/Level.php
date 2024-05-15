<?php

namespace LevelUp\Experience\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LevelUp\Experience\Exceptions\LevelExistsException;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Throwable;

class Level extends Model
{
    protected $guarded = [];

    /**
     * @throws \LevelUp\Experience\Exceptions\LevelExistsException
     */
    public static function add(...$levels): array
    {
        $newLevels = [];

        foreach ($levels as $level) {
            if (is_array($level)) {
                $levelNumber = $level['level'];
                $pointsToNextLevel = $level['next_level_experience'];
            } else {
                $levelNumber = $level;
                $pointsToNextLevel = $levels[1] ?? 0;
            }

            try {
                $newLevels[] = self::create([
                    'level' => $levelNumber,
                    'next_level_experience' => $pointsToNextLevel,
                ]);
            } catch (Throwable) {
                throw LevelExistsException::handle(levelNumber: $levelNumber);
            }

            if (! is_array($level)) {
                break;
            }
        }

        return $newLevels;
    }

    public function users(): HasMany
    {
        return $this->hasMany(related: config(key: 'level-up.user.model'));
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
    public $cacheTags = ['levels'];

    /**
     * A cache prefix string that will be prefixed
     * on each cache key generation.
     *
     * @var string
     */
    public $cachePrefix = 'levels_';

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
            'custom_level_tag',
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
            "level:{$this->id}",
            'levels',
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
        static::created(function (Level $agent) {
            // ...
            $agent::flushQueryCache(['levels']);
        });
        //Update
        static::saved(function (Level $agent) {
            // ...
            $agent::flushQueryCache(['levels']);
        });

        //Delete
        static::deleted(function (Level $agent) {
            // ...
            $agent::flushQueryCache(['levels']);
        });
    }

    //flushQueryCacheItem
    public function flushQueryCacheItem()
    {
        $cacheKeyAgent = 'level_cache_for';
        //Delete cache
        cache()->forget($cacheKeyAgent);

        return true;
    }

    protected function getCacheForKey()
    {
        return 'level_cache_for';
    }

    /**
     * The tags for the query cache. Can be useful
     * if flushing cache for specific tags only.
     *
     * @return null|array
     */
    protected function cacheTagsValue()
    {
        return ['levels'];
    }
}
