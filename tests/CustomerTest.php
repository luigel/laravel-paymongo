<?php

use Illuminate\Support\Collection;
use Luigel\Paymongo\Models\Customer;
use Luigel\Paymongo\Facades\Paymongo;
use Luigel\Paymongo\Exceptions\NotFoundException;

it('can create a customer', function () {
    $customer = createCustomer();

    expect($customer)->toBeInstanceOf(Customer::class);
});

it('can not retrieve a customer with invalid id', function () {
    $this->expectException(NotFoundException::class);
    
    Paymongo::customer()
        ->find('test');
});

it('can retrieve a customer', function () {
    $customer = createCustomer();

    $retrieve = Paymongo::customer()
                    ->find($customer->id);

    expect($customer->id)->toBe($retrieve->id);
});

it('can update a customer', function () {
    $customer = createCustomer();

    expect($customer->last_name)->toBe('Felix');

    $updatedCustomer = $customer->update([
        'last_name' => 'Mongo'
    ]);

    expect($updatedCustomer->last_name)->toBe('Mongo');
});

it('can delete a customer', function () {
    $customer = createCustomer()->delete();

    expect($customer->deleted)->toBe(true);
});

it('can retrieve a customer\'s payment methods', function () {
    $customer = createCustomer()->paymentMethods();

    expect($customer)->toBeInstanceOf(Collection::class);
});