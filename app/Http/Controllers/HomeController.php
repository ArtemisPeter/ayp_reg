<?php

namespace App\Http\Controllers;

use App\Models\YouthCamp;
use App\Portal\ContestLeaderboard;
use App\Portal\YouthCountLeaderboard;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(YouthCountLeaderboard $youthLeaderboard, ContestLeaderboard $contestLeaderboard)
    {
        return view('welcome', [
            'camp' => YouthCamp::where('is_current', true)->first(),
            'leaderboard' => collect(Cache::remember('portal.youth-count', 30, fn () => $youthLeaderboard->rows()->all())),
            'contestLeaderboard' => collect(Cache::remember('portal.contest', 30, fn () => $contestLeaderboard->rows()->all())),
        ]);
    }
}
