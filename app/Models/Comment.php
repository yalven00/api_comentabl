<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content',
        'user_id',
        'parent_id',
        'commentable_id',
        'commentable_type',
    ];

    protected $casts = [
        'replies_count' => 'integer',
        'cursor_sort' => 'integer',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('replies');
    }

   
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

   
    public function scopeWithReplies(Builder $query, int $depth = 3): Builder
    {
        if ($depth <= 0) {
            return $query;
        }

        return $query->with(['replies' => function ($query) use ($depth) {
            $query->withReplies($depth - 1);
        }]);
    }

    public function incrementRepliesCount()
    {
        $this->increment('replies_count');
        
        if ($this->parent) {
            $this->parent->incrementRepliesCount();
        }
    }

    public function decrementRepliesCount()
    {
        $this->decrement('replies_count');
        
        if ($this->parent) {
            $this->parent->decrementRepliesCount();
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($comment) {
            // Устанавливаем cursor_sort для пагинации
            $maxSort = self::where('commentable_type', $comment->commentable_type)
                ->where('commentable_id', $comment->commentable_id)
                ->where('parent_id', $comment->parent_id)
                ->max('cursor_sort');
            
            $comment->cursor_sort = ($maxSort ?? 0) + 1;
        });

        static::created(function ($comment) {
            if ($comment->parent) {
                $comment->parent->incrementRepliesCount();
            }
        });

        static::deleting(function ($comment) {
            if ($comment->parent) {
                $comment->parent->decrementRepliesCount();
            }
        });
    }
}