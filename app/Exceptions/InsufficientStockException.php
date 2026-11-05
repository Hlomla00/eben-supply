<?php
// Hlomla Magopeni 218070349 — Eben Supply | Group KN3

namespace App\Exceptions;

use RuntimeException;

// Thrown during checkout when an item no longer has enough stock;
// the transaction rolls back and the customer is sent back to the cart.
class InsufficientStockException extends RuntimeException
{
}
