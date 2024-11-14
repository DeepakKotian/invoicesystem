<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Retrieve all customers.
     */
    public function index()
    {
        return response()->json(Customer::all());
    }

    /**
     * Add or update a customer.
     */
    public function save(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'id' => 'nullable|exists:customers,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:customers,email,' . $request->id,
                'address' => 'required|string|max:500',
                'contact_number' => 'required|string|regex:/^[0-9]{10,15}$/',
            ]);

            // Determine if adding a new customer or updating an existing one
            if (empty($request->id)) {
                $customer = new Customer();
            } else {
                $customer = Customer::findOrFail($request->id);
            }

            // Assign customer attributes
            $customer->name = $validatedData['name'];
            $customer->email = $validatedData['email'];
            $customer->address = $validatedData['address'];
            $customer->contact_number = $validatedData['contact_number'];
            $customer->save();

            return response()->json(['message' => 'Customer saved successfully', 'customer' => $customer], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error saving customer: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    /**
     * Retrieve a single customer by ID.
     */
    public function show($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            return response()->json($customer);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Customer Not Found',
                'message' => 'The requested customer does not exist.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving customer: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    /**
     * Delete a customer by ID.
     */
    public function destroy(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|exists:customers,id',
            ]);

            $customer = Customer::findOrFail($validatedData['id']);
            $customer->delete();

            return response()->json(['message' => 'Customer deleted successfully'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }
}
