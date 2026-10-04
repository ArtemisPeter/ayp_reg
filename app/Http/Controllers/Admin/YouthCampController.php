<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\YouthCamp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class YouthCampController extends Controller
{
    public function index()
    {
        return view('admin.youth-camps.index', ['youthCamps' => YouthCamp::orderByDesc('year')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        YouthCamp::create($data);

        return redirect('/admin/youth-camps');
    }

    public function update(Request $request, YouthCamp $youthCamp): RedirectResponse
    {
        $data = $request->validate($this->rules($youthCamp->id));

        $youthCamp->update($data);

        return redirect('/admin/youth-camps');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?int $ignoringId = null): array
    {
        return [
            'year' => ['required', 'integer', 'between:2000,2100', Rule::unique('youth_camps', 'year')->withoutTrashed()->ignore($ignoringId)],
            'theme' => ['required', 'string', 'max:255'],
            'venue' => ['nullable', 'string', 'max:255'],
            'verse_reference' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'pre_registration_fee' => ['nullable', 'numeric', 'min:0'],
            'on_site_fee' => ['nullable', 'numeric', 'min:0'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
        ];
    }

    public function destroy(YouthCamp $youthCamp): RedirectResponse
    {
        if ($youthCamp->is_current) {
            return redirect('/admin/youth-camps')->withErrors(['year' => 'Make another camp current before removing this one.']);
        }

        if (Attendee::where('youth_camp_id', $youthCamp->id)->exists()) {
            return redirect('/admin/youth-camps')->withErrors(['year' => 'This camp has Attendees registered and cannot be removed.']);
        }

        $youthCamp->delete();

        return redirect('/admin/youth-camps');
    }

    public function makeCurrent(YouthCamp $youthCamp): RedirectResponse
    {
        DB::transaction(function () use ($youthCamp) {
            YouthCamp::query()->update(['is_current' => false]);
            $youthCamp->forceFill(['is_current' => true])->save();
        });

        return redirect('/admin/youth-camps');
    }
}
