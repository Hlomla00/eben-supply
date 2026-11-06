<?php
// PRT362S — Eben Supply | Group KN3

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Create, edit and (de)activate promo codes. Codes are never deleted so past
 * orders keep their link to the code they used.
 */
class PromoCodeController extends Controller
{
    public function index()
    {
        $promos = PromoCode::withCount('orders')->latest()->paginate(20);
        return view('admin.promos.index', compact('promos'));
    }

    public function create()
    {
        $promo = new PromoCode(['type' => 'percentage', 'is_active' => true]);
        return view('admin.promos.create', compact('promo'));
    }

    public function store(Request $request)
    {
        $promo = PromoCode::create($this->validated($request));

        return redirect()->route('admin.promos.index')->with('success', "Promo code {$promo->code} created.");
    }

    public function edit(PromoCode $promo)
    {
        return view('admin.promos.edit', compact('promo'));
    }

    public function update(Request $request, PromoCode $promo)
    {
        $promo->update($this->validated($request, $promo));

        return redirect()->route('admin.promos.index')->with('success', "Promo code {$promo->code} updated.");
    }

    public function toggle(PromoCode $promo)
    {
        $promo->update(['is_active' => !$promo->is_active]);

        return redirect()->back()->with('success', "Promo code {$promo->code} " . ($promo->is_active ? 'activated.' : 'deactivated.'));
    }

    private function validated(Request $request, ?PromoCode $promo = null): array
    {
        // Codes are case-insensitive, so uniqueness is checked on the upper-case form.
        $request->merge(['code' => PromoCode::normalise($request->input('code'))]);

        $data = $request->validate([
            'code'               => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_-]+$/',
                                     Rule::unique('promo_codes', 'code')->ignore($promo)],
            'type'               => ['required', Rule::in(array_keys(PromoCode::TYPES))],
            'value'              => ['required', 'numeric', 'gt:0', $request->input('type') === 'percentage' ? 'max:100' : 'max:99999'],
            'min_spend'          => 'nullable|numeric|min:0',
            'starts_at'          => 'nullable|date',
            'ends_at'            => array_filter(['nullable', 'date', $request->filled('starts_at') ? 'after:starts_at' : null]),
            'usage_limit'        => 'nullable|integer|min:1',
            'is_active'          => 'boolean',
        ], [
            'code.regex' => 'Use letters, numbers, dashes or underscores only.',
            'value.max'  => 'A percentage discount can be at most 100.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
