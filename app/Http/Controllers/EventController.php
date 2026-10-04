<?php

namespace App\Http\Controllers;

use App\Models\Circuit;
use App\Models\Event;
use App\Models\EventScore;
use App\Models\YouthCamp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index()
    {
        return view('events.index', [
            'events' => Event::with('ranks')
                ->whereHas('youthCamp', fn ($camp) => $camp->where('is_current', true))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ranks' => ['required', 'array', 'min:1'],
            'ranks.*.label' => ['required', 'string', 'max:255'],
            'ranks.*.points' => ['required', 'integer', 'min:0'],
        ]);

        $camp = YouthCamp::where('is_current', true)->first();

        if ($camp === null) {
            return redirect('/events')->withErrors(['name' => 'No Youth Camp is open yet. Set the current camp first.']);
        }

        $event = Event::create(['youth_camp_id' => $camp->id, 'name' => $data['name']]);
        $event->ranks()->createMany($data['ranks']);

        return redirect('/events');
    }

    public function score(Event $event)
    {
        return view('events.score', [
            'event' => $event->load('ranks'),
            'circuits' => Circuit::orderBy('name')->get(),
            'scores' => $event->scores()->pluck('event_rank_id', 'circuit_id'),
        ]);
    }

    public function storeScore(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'circuit_id' => ['required', Rule::exists('circuits', 'id')->whereNull('deleted_at')],
            'event_rank_id' => ['required', Rule::exists('event_ranks', 'id')->where('event_id', $event->id)],
        ]);

        EventScore::updateOrCreate(
            ['event_id' => $event->id, 'circuit_id' => $data['circuit_id']],
            ['event_rank_id' => $data['event_rank_id']],
        );

        return redirect("/events/{$event->id}/score");
    }
}
