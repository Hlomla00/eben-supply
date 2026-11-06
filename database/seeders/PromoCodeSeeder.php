<?php
// PRT362S — Eben Supply | Group KN3

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

/**
 * Test promo codes covering each rule. firstOrCreate keeps real usage counts
 * when the deploy entrypoint re-seeds.
 */
class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            // Valid percentage code, no minimum.
            ['code' => 'WELCOME10', 'type' => 'percentage', 'value' => 10],
            // Valid fixed code with a minimum spend.
            ['code' => 'SAVE50', 'type' => 'fixed', 'value' => 50, 'min_spend' => 300],
            // Minimum-spend case: needs R1,000 of items.
            ['code' => 'BIG100', 'type' => 'fixed', 'value' => 100, 'min_spend' => 1000],
            // Valid for the next 60 days.
            ['code' => 'KIDS15', 'type' => 'percentage', 'value' => 15, 'ends_at' => now()->addDays(60)->startOfDay()],
            // Expired yesterday.
            ['code' => 'EXPIRED20', 'type' => 'percentage', 'value' => 20,
             'starts_at' => now()->subDays(60)->startOfDay(), 'ends_at' => now()->subDay()->startOfDay()],
            // Switched off by an admin.
            ['code' => 'INACTIVE25', 'type' => 'percentage', 'value' => 25, 'is_active' => false],
            // Total usage limit already reached.
            ['code' => 'USEDUP30', 'type' => 'percentage', 'value' => 30, 'usage_limit' => 3, 'times_used' => 3],
            // Fixed amount, no minimum (like every code, once per customer).
            ['code' => 'SAVE40', 'type' => 'fixed', 'value' => 40],
            // Not started yet.
            ['code' => 'FUTURE10', 'type' => 'percentage', 'value' => 10, 'starts_at' => now()->addDays(30)->startOfDay()],
        ];

        foreach ($codes as $data) {
            $timesUsed = $data['times_used'] ?? 0;
            unset($data['times_used']);

            $promo = PromoCode::firstOrCreate(['code' => $data['code']], $data + ['is_active' => true]);

            // times_used isn't mass assignable (only orders change it); set the seed value once.
            if ($promo->wasRecentlyCreated && $timesUsed) {
                $promo->forceFill(['times_used' => $timesUsed])->save();
            }
        }
    }
}
