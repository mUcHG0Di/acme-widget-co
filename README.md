# Acme Widget Co - Basket

A small PHP library that builds a shopping basket, applies offers, and adds
delivery. Written as a proof of concept against the Acme Widget Co spec.

## Requirements

- PHP 8.5+
- Composer

## Setup

```bash
composer install
composer check   # runs style, static analysis, and tests
```

Individual scripts: `composer test`, `composer analyse`, `composer lint`,
`composer lint:fix`.

## Example

```php
use Acme\Widgets\Basket;
use Acme\Widgets\Catalogue\ProductCatalogue;
use Acme\Widgets\Delivery\DeliveryTier;
use Acme\Widgets\Delivery\TieredDeliveryCalculator;
use Acme\Widgets\Money;
use Acme\Widgets\Offer\BuyOneGetSecondHalfPrice;
use Acme\Widgets\Product;

$basket = new Basket(
    catalogue: new ProductCatalogue(
        new Product('R01', 'Red Widget',   Money::fromCents(3295)),
        new Product('G01', 'Green Widget', Money::fromCents(2495)),
        new Product('B01', 'Blue Widget',  Money::fromCents(795)),
    ),
    deliveryCalculator: new TieredDeliveryCalculator(
        new DeliveryTier(Money::zero(),          Money::fromCents(495)),
        new DeliveryTier(Money::fromCents(5000), Money::fromCents(295)),
        new DeliveryTier(Money::fromCents(9000), Money::zero()),
    ),
    offers: [new BuyOneGetSecondHalfPrice('R01')],
);

$basket->add('R01');
$basket->add('R01');

echo $basket->total()->format(); // "$54.37"
```

## How the total is calculated

1. Add up the prices of everything in the basket.
2. Subtract any offer discounts.
3. Look at the discounted amount and pick the right delivery charge.
4. Total = discounted amount + delivery.

Empty basket = $0. No items, no delivery.

## A few things worth knowing

**Money is stored in cents, not dollars.** Floats and money don't mix,
`0.1 + 0.2` famously isn't `0.3`. Everything's an integer until it's
formatted for display.

**Half-up rounding.** Half of $32.95 is $16.475. Round it up to $16.48,
not down to $16.47. The spec's expected total of $54.37 only works this
way, so I followed the spec.

**Delivery is calculated *after* discounts.** Two R01s cost $65.90 before
the offer ($2.95 delivery tier) but $49.42 after ($4.95 tier). The cheaper
delivery tier wins because the customer paid less. This isn't spelled out
in the spec, it's the only way the example numbers add up.

**Offers and delivery are interfaces.** Adding "buy 3 get 1 free" or a new
delivery tier doesn't mean rewriting anything, just write a new class
that fits the existing shape and pass it in.

## Assumptions I made

The spec doesn't cover every edge case, so:

| Question                  | Decision                                                    |
| ------------------------- | ----------------------------------------------------------- |
| Subtotal exactly $50?     | Charged the $2.95 tier ("under $50" means $50 isn't under). |
| Subtotal exactly $90?     | Free delivery ("$90 or more" includes $90).                 |
| Empty basket?             | $0 total. Charging delivery on nothing felt wrong.          |
| Three R01s in the basket? | One pair gets the discount, the third pays full price.      |
| Unknown product code?     | Throws an exception. Fail loudly, not silently.             |

## Project layout

```
src/
  Money.php
  Product.php
  Basket.php
  Catalogue/   ProductCatalogue + UnknownProductException
  Delivery/    DeliveryCalculator interface, tier value object, tiered impl
  Offer/       Offer interface, BuyOneGetSecondHalfPrice
tests/
  (mirrors src/, plus an AcceptanceTest covering the spec examples end-to-end)
```

The `Basket` is the only thing that holds state. Everything else is
immutable, once you construct it, it doesn't change.

## What I'd do differently for production

This is a proof of concept, so a few corners are deliberately cut:

- I'd swap my hand-rolled `Money` for `moneyphp/money`. Mine is fine for
  one currency and a small ruleset; the library handles currency
  conversion, allocation, and the dozen edge cases I haven't thought of.
- The catalogue is in-memory. In real life it'd come from a database
  behind a repository interface.
- Multiple offers that overlap (e.g. "BOGO half price" *and* "10% off
  reds") aren't handled cleanly, each offer gets the original item list,
  so an item could in theory be discounted twice. Fine for one offer,
  needs more thought for several.
- No CI workflow. Would add a GitHub Actions file running `composer check`
  on push.

## Tests

Three layers:

- Unit tests per class.
- An `AcceptanceTest` that runs the four spec baskets end-to-end and a
  handful of boundary cases (exact tier thresholds, odd-count R01 pairing,
  mixed baskets).
- PHPStan at max level catches the rest.

Followed TDD throughout - the commit history shows tests landing before
implementations.

## License

MIT.
