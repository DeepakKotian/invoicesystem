<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{

    public function index()
    {
        return response()->json(Customer::all());
    }

    /**
     * @OA\Post(
     *     path="/api/customer/save",
     *     tags={"Customers"},
     *     summary="Add a new customer or update an existing customer",
     *     description="This endpoint is used to create a new customer or update an existing one based on the provided customer ID.",
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         description="Customer data to create or update",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "email", "address", "contact_number"},
     *             @OA\Property(property="id", type="integer", nullable=true, example=1, description="Customer ID (for update)"),
     *             @OA\Property(property="name", type="string", example="John Doe", description="Customer's name"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com", description="Customer's email address"),
     *             @OA\Property(property="address", type="string", example="1234 Main St, City, Country", description="Customer's address"),
     *             @OA\Property(property="contact_number", type="string", example="1234567890", description="Customer's contact number")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=201,
     *         description="Customer created or updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Customer saved successfully"),
     *             @OA\Property(property="customer", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="address", type="string", example="1234 Main St, City, Country"),
     *                 @OA\Property(property="contact_number", type="string", example="1234567890"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-13T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-13T12:00:00Z")
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Validation Error"),
     *             @OA\Property(property="messages", type="object",
     *                 @OA\Property(property="name", type="array", 
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 ),
     *                 @OA\Property(property="email", type="array", 
     *                     @OA\Items(type="string", example="The email must be a valid email address.")
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Server Error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
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
