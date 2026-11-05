<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ListingFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Product::factory()->withSizes(['S' => 2, 'M' => 0])->create(['name' => 'ES Adult Tee', 'price' => 300]);
        Product::factory()->withSizes(['4-5Y' => 3])->create(['name' => 'ES Kids Tee', 'is_kids' => true, 'price' => 180]);
        Product::factory()->create(['name' => 'ES Kids Cap', 'is_kids' => true, 'price' => 150]);
        Product::factory()->create(['name' => 'ES Adult Cap', 'price' => 250, 'sale_price' => 120]);
        Product::factory()->create(['name' => 'ES Sold Out Tote', 'category' => 'tote_bag', 'price' => 200, 'stock_quantity' => 0]);
    }

    private function names(string $url): array
    {
        return $this->get($url)->assertOk()->viewData('products')->pluck('name')->all();
    }

    // ── Kids is its own category ───────────────────────────────────────────

    public function test_kids_items_do_not_appear_under_adult_categories(): void
    {
        $this->assertSame(['ES Adult Tee'], $this->names('/products?category=tshirt'));
        $this->assertSame(['ES Adult Cap'], $this->names('/products?category=cap'));
    }

    public function test_kids_page_and_kids_category_list_only_kids(): void
    {
        $this->assertEqualsCanonicalizing(['ES Kids Tee', 'ES Kids Cap'], $this->names('/kids'));
        $this->assertSame(['ES Kids Tee'], $this->names('/kids?category=tshirt'));
        $this->assertEqualsCanonicalizing(['ES Kids Tee', 'ES Kids Cap'], $this->names('/products?category=kids'));
    }

    public function test_shop_all_still_shows_everything_with_a_kids_pill(): void
    {
        $this->assertCount(5, $this->names('/products'));
        $this->get('/products')->assertSee('href="' . route('kids.index') . '"', false)->assertSee('>Kids</a>', false);
    }

    public function test_kids_products_are_labelled_kids_not_t_shirt(): void
    {
        $kidsTee = Product::firstWhere('name', 'ES Kids Tee');
        $this->assertSame('Kids T-Shirt', $kidsTee->category_label);
        $this->assertSame('badge-kids', $kidsTee->category_badge);

        $this->get("/products/{$kidsTee->id}")
            ->assertSee('Kids T-Shirt')
            ->assertSeeInOrder(['Home', 'Kids', 'T-Shirts', 'ES Kids Tee'])
            ->assertDontSee('transition-colors">Shop</a>', false);       // no Shop step in the breadcrumb
    }

    // ── Filters ────────────────────────────────────────────────────────────

    public function test_size_filter_only_shows_products_with_that_size_in_stock(): void
    {
        $this->assertSame(['ES Adult Tee'], $this->names('/products?size=S'));
        $this->assertSame([], $this->names('/products?size=M'));               // M is sold out
        $this->assertSame(['ES Kids Tee'], $this->names('/kids?size=4-5Y'));
    }

    public function test_price_filter_uses_the_current_sale_price(): void
    {
        // The adult cap is R250 but on sale at R120.
        $this->assertEqualsCanonicalizing(['ES Adult Cap', 'ES Kids Cap'], $this->names('/products?max_price=150'));
        $this->assertEqualsCanonicalizing(['ES Adult Tee', 'ES Sold Out Tote'], $this->names('/products?min_price=200'));
        $this->assertSame(['ES Kids Tee'], $this->names('/products?min_price=160&max_price=190'));
        // Swapped bounds are corrected rather than returning nothing.
        $this->assertSame(['ES Kids Tee'], $this->names('/products?min_price=190&max_price=160'));
    }

    public function test_price_filter_ignores_a_sale_that_has_ended(): void
    {
        Product::firstWhere('name', 'ES Adult Cap')->update(['sale_ends_at' => Carbon::parse('2026-12-01')]);
        $this->travelTo(Carbon::parse('2026-12-02'));

        $this->assertNotContains('ES Adult Cap', $this->names('/products?max_price=150'));
    }

    public function test_in_stock_and_on_sale_filters(): void
    {
        $this->assertNotContains('ES Sold Out Tote', $this->names('/products?in_stock=1'));
        $this->assertCount(4, $this->names('/products?in_stock=1'));
        $this->assertSame(['ES Adult Cap'], $this->names('/products?on_sale=1'));
    }

    public function test_sorting_by_current_price_and_name(): void
    {
        $this->assertSame(
            ['ES Adult Cap', 'ES Kids Cap', 'ES Kids Tee', 'ES Sold Out Tote', 'ES Adult Tee'],
            $this->names('/products?sort=price_asc')                           // 120, 150, 180, 200, 300
        );
        $this->assertSame('ES Adult Tee', $this->names('/products?sort=price_desc')[0]);
        $this->assertSame('ES Adult Cap', $this->names('/products?sort=name')[0]);
    }

    public function test_filters_combine_and_survive_pagination_links(): void
    {
        $this->assertSame(['ES Kids Cap'], $this->names('/kids?category=cap&max_price=160&in_stock=1&sort=price_asc'));
        $this->get('/products?size=S&sort=price_desc')
            ->assertSee('<option value="S" selected>', false)
            ->assertSee('<option value="price_desc" selected>', false)
            ->assertSee('1 active');
    }

    public function test_invalid_filter_values_are_ignored(): void
    {
        $this->assertCount(5, $this->names('/products?size=XXXL&min_price=abc&max_price=-5&sort=drop_table'));
        // Adult sizes aren't offered or applied on the Kids page.
        $this->assertCount(2, $this->names('/kids?size=M'));
        $this->get('/kids')->assertDontSee('<option value="M"', false)->assertSee('<option value="4-5Y"', false);
    }
}
