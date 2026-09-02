<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_applications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'position',
        'status',
        'applied_date',
        'source',
        'job_url',
        'location',
        'notes',
        'salary_range_min',
        'salary_range_max',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'applied_date' => 'date:Y-m-d',
            'salary_range_min' => 'float',
            'salary_range_max' => 'float',
        ];
    }

    /**
     * User relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        if (!empty($status) && $status !== 'all') {
            return $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Scope a query to search across company name, position, location, or source.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $term = '%' . strtolower($search) . '%';
            $q->whereRaw('LOWER(company_name) LIKE ?', [$term])
              ->orWhereRaw('LOWER(position) LIKE ?', [$term])
              ->orWhereRaw('LOWER(location) LIKE ?', [$term])
              ->orWhereRaw('LOWER(source) LIKE ?', [$term]);
        });
    }

    /**
     * Scope a query to sort dynamically.
     */
    public function scopeSortBy(Builder $query, ?string $sortBy = 'applied_date', ?string $direction = 'desc'): Builder
    {
        $allowed = ['company_name', 'position', 'status', 'applied_date', 'created_at'];
        $column = in_array($sortBy, $allowed, true) ? $sortBy : 'applied_date';
        $dir = strtolower($direction ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($column, $dir);
    }
}
