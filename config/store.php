<?php
// PRT362S — Eben Supply | Group KN3

// Store contact details shown on the Contact, Returns, Privacy and error
// pages. Email and phone only appear once they are set in .env.
return [
    'address' => env('STORE_ADDRESS', 'Woodstock, Cape Town, 7925'),
    'email'   => env('STORE_EMAIL'),
    'phone'   => env('STORE_PHONE'),
];
