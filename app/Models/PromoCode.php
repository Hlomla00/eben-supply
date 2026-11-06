<?php
// PRT362S — Eben Supply | Group KN3

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    /** Shown to customers wherever promo codes can be entered. */
    public const ONE_USE_RULE = 'Each promo code can be used once per customer (per account and email address), and only one code per order.';

    /** Session key holding the code the shopper applied (one per order). */
    public const SESSION_KEY = 'promo_code';

    public const TYPES = [
        'percentage' => 'Percentage off',
        'fixed'      => 'Fixed amount off',
    ];

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_spend',
        'starts_at',
        'ends_at',
        'usage_limit',
        'is_active',
    ];

    protected $casts = [
        'value'              => 'decimal:2',
        'min_spend'          => 'decimal:2',
        'starts_at'          => 'datetime',
        'ends_at'            => 'datetime',
        'usage_limit'        => 'integer',
        'times_used'         => 'integer',
        'is_active'          => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /** Codes are case-insensitive: always stored and looked up upper-case. */
    public static function normalise(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }

    public function setCodeAttribute(?string $value): void
    {
        $this->attributes['code'] = self::normalise($value);
    }

    /**
     * The code in the session, rechecked against this subtotal (in rands).
     * Returns [PromoCode|null, string|null error]. A code that no longer
     * applies stays in the session so the shopper sees why, but gives no
     * discount.
     */
    public static function applied(float $subtotal, ?User $user, ?string $email = null): array
    {
        $code = session(self::SESSION_KEY);
        if (!$code) {
            return [null, null];
        }

        $promo = self::findByCode($code);
        if (!$promo) {
            return [null, "The code {$code} isn't valid."];
        }

        $reason = $promo->rejectionReason($subtotal, $user, null, $email);

        return $reason ? [null, $reason] : [$promo, null];
    }

    public static function findByCode(?string $code): ?self
    {
        $code = self::normalise($code);

        return $code === '' ? null : self::where('code', $code)->first();
    }

    /**
     * Why this code can't be used on an order with this subtotal (in rands),
     * or null when it can. The same check runs in the cart and when the
     * order is placed (where the checkout email is passed too).
     */
    public function rejectionReason(float $subtotal, ?User $user, ?CarbonInterface $at = null, ?string $email = null): ?string
    {
        $at ??= now();

        if (!$this->is_active) {
            return "The code {$this->code} is no longer active.";
        }
        if ($this->starts_at && $at->lt($this->starts_at)) {
            return "The code {$this->code} can't be used until " . $this->starts_at->format('d M Y, H:i') . '.';
        }
        if ($this->ends_at && $at->gte($this->ends_at)) {
            return "The code {$this->code} expired on " . $this->ends_at->format('d M Y, H:i') . '.';
        }
        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            return "The code {$this->code} has been fully used.";
        }
        if ($this->usedByCustomer($user, $email)) {
            return "You've already used the code {$this->code}. " . self::ONE_USE_RULE;
        }
        if ($this->min_spend !== null && $subtotal < (float) $this->min_spend) {
            return "Spend R" . number_format((float) $this->min_spend, 2) . " to use {$this->code} — add R"
                . number_format((float) $this->min_spend - $subtotal, 2) . ' more.';
        }

        return null;
    }

    /**
     * Each code can be used once per customer, matched by account or by email
     * address (any case), so a second account with the same email can't reuse it.
     */
    public function usedByCustomer(?User $user, ?string $email = null): bool
    {
        $emails = collect([$user?->email, $email])->filter()->map(fn ($e) => mb_strtolower(trim($e)))->unique()->values();

        if (!$user && $emails->isEmpty()) {
            return false;
        }

        return $this->orders()->where(function ($q) use ($user, $emails) {
            if ($user) {
                $q->orWhere('user_id', $user->id);
            }
            foreach ($emails as $e) {
                $q->orWhereRaw('LOWER(contact_email) = ?', [$e]);
            }
        })->exists();
    }

    /**
     * Discount in cents on a subtotal in cents. Percentages round to the cent;
     * a fixed amount is capped at the subtotal so a total never goes below 0.
     */
    public function discountCents(int $subtotalCents): int
    {
        $discount = $this->type === 'percentage'
            ? (int) round($subtotalCents * ((float) $this->value) / 100)
            : (int) round(((float) $this->value) * 100);

        return max(0, min($discount, $subtotalCents));
    }

    public function getLabelAttribute(): string
    {
        return $this->type === 'percentage'
            ? rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.') . '% off'
            : 'R' . number_format((float) $this->value, 2) . ' off';
    }

    /** Admin status: active, inactive, scheduled, expired or used up. */
    public function getStatusAttribute(): string
    {
        return match (true) {
            !$this->is_active                                                       => 'inactive',
            $this->starts_at && now()->lt($this->starts_at)                         => 'scheduled',
            $this->ends_at && now()->gte($this->ends_at)                            => 'expired',
            $this->usage_limit !== null && $this->times_used >= $this->usage_limit  => 'used up',
            default                                                                 => 'active',
        };
    }
}
