<?php
// PRT362S — Eben Supply | Group KN3

namespace App\Exceptions;

use RuntimeException;

// Stops an order being placed. The transaction rolls back and the shopper is
// sent to $redirectRoute with the message.
class CheckoutException extends RuntimeException
{
    public function __construct(string $message, public readonly string $redirectRoute = 'cart.index')
    {
        parent::__construct($message);
    }
}
