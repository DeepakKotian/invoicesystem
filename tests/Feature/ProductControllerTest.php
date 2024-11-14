<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test saving a new product.
     *
     * @return void
     */
    public function test_store_new_product()
    {
        // Create a category to associate with the product
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);

        // Prepare data for the new product with valid category_id
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.0,
            'quantity' => 10,
            'category_id' => $category->id, // Use the ID of the created category
        ];

        // Send POST request to the save method
        $response = $this->postJson('/api/product/save', $productData);

        // Assert the response is successful and the product was created
        $response->assertStatus(201);
        $response->assertJson([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.0,
            'quantity' => 10,
            'category_id' => $category->id, // Ensure the category_id matches
        ]);

        // Verify that the product is saved in the database
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.0,
            'quantity' => 10,
            'category_id' => $category->id,
        ]);
    }

    /**
     * Test validation error when required fields are missing.
     *
     * @return void
     */
    public function test_product_validation_error()
    {
        // Send POST request with missing required fields (e.g. name and price)
        $response = $this->postJson('/api/product/save', [
            'description' => 'Test product without name and price',
            'quantity' => 5,
            'category_id' => 1, // Assuming category ID 1 exists
        ]);

        // Assert validation errors are returned for missing required fields
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'price']);
    }
}
