<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenerateInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_invoice_with_valid_data()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $cart =  new Cart();
        $cart->customer_id = $customer->id;
        $cart->product_id = $product->id;
        $cart->quantity = 2;
        $cart->save();

        $data = [
            'discount' => 10.00,
            'tax_rate' => 8.00,
            'customer_id' => $customer->id,
        ];

        $response = $this->json('POST', '/api/invoice/generate', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'invoice' => [
                    'customer_name',
                    'customer_id',
                    'customer_email',
                    'customer_address',
                    'subtotal',
                    'discount',
                    'tax_rate',
                    'tax_amount',
                    'total_amount',
                ],
            ]);

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'subtotal' => $product->price * 2,
            'discount' => 10.00,
            'tax_rate' => 8.00,
        ]);

        $invoice = Invoice::first();
        $this->assertEquals($customer->name, $invoice->customer_name);
        $this->assertEquals($customer->email, $invoice->customer_email);
        $this->assertEquals($customer->address, $invoice->customer_address);
    }

    public function test_returns_error_for_empty_cart()
    {
        $customer = Customer::factory()->create();

        $data = [
            'discount' => 10.00,
            'tax_rate' => 8.00,
            'customer_id' => $customer->id,
        ];

        $response = $this->json('POST', '/api/invoice/generate', $data);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'error',
                'message',
            ]);
    }

   
    public function test_validates_request_data()
    {
        $customer = Customer::factory()->create();

        $data = [
            'discount' => 'invalid_discount', // Invalid discount format
            'tax_rate' => -5.00, // Negative tax rate
            'customer_id' => $customer->id,
        ];

        $response = $this->json('POST', '/api/invoice/generate', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'discount',
                    'tax_rate',
                ],
            ]);
    }
}