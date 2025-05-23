<?php

namespace LevelUp\Experience\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use LevelUp\Experience\Models\Experience;

class LeaderboardService
{
    private mixed $userModel;

    public function __construct()
    {
        $this->userModel = config(key: 'level-up.user.model');
    }

    public function generate(bool $paginate = false, int $limit = null): array|Collection|LengthAwarePaginator
    {
        $LoginUser = auth()->user();
        //User company
        $UserCompanyId = $LoginUser->company_id;

        return $this->userModel::query()
            //Where company
            //->where('company_id', $UserCompanyId)
            ->with(relations: ['experience', 'level'])
            ->orderByDesc(
                column: Experience::select('experience_points')
                    ->whereColumn(config('level-up.user.foreign_key'), 'users.id')
                    ->latest()
            )
            ->take($limit)
            ->when($paginate, fn (Builder $query) => $query->paginate(), fn (Builder $query) => $query->get());
    }

    //Generate leaderboard for week
    public function generateWeek(bool $paginate = false, int $limit = null): array|Collection|LengthAwarePaginator
    {
        $LoginUser = auth()->user();
        //User company
        $UserCompanyId = $LoginUser->company_id;

        return $this->userModel::query()
            //Where company
            //->where('company_id', $UserCompanyId)
            //->where('is_complete_edu_lms', true)
            ->with(relations: ['experience', 'level'])
            ->orderByDesc(
                column: Experience::select('week_experience_points')
                    ->whereColumn(config('level-up.user.foreign_key'), 'users.id')
                    ->latest()
            )
            ->take($limit)
            ->when($paginate, fn (Builder $query) => $query->paginate(), fn (Builder $query) => $query->get());
    }

    //Generate leaderboard for month
    public function generateMonth(bool $paginate = false, int $limit = null): array|Collection|LengthAwarePaginator
    {
        $LoginUser = auth()->user();
        //User company
        $UserCompanyId = $LoginUser->company_id;

        return $this->userModel::query()
            //Where company
            ->where('company_id', $UserCompanyId)
            ->with(relations: ['experience', 'level'])
            ->orderByDesc(
                column: Experience::select('month_experience_points')
                    ->whereColumn(config('level-up.user.foreign_key'), 'users.id')
                    ->latest()
            )
            ->take($limit)
            ->when($paginate, fn (Builder $query) => $query->paginate(), fn (Builder $query) => $query->get());
    }
}
