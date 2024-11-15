<?php

namespace App\Http\Controllers;

use App\Actions\CreateCustomerAction;
use App\Http\Requests\CustomerFormRequest;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/customers",
     *     tags={"Customers"},
     *     summary="Retrieve a paginated list of customers",
     *     description="This endpoint returns a paginated list of all customers in the system.",
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of customers retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer", example=1, description="Current page number"),
     *             @OA\Property(property="data", type="array", description="List of customers",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1, description="Customer ID"),
     *                     @OA\Property(property="name", type="string", example="John Doe", description="Customer's name"),
     *                     @OA\Property(property="email", type="string", example="johndoe@example.com", description="Customer's email"),
     *                     @OA\Property(property="address", type="string", example="1234 Main St, City, Country", description="Customer's address"),
     *                     @OA\Property(property="contact_number", type="string", example="1234567890", description="Customer's contact number")
     *                 )
     *             ),
     *             @OA\Property(property="first_page_url", type="string", example="http://example.com/api/customers?page=1", description="URL of the first page"),
     *             @OA\Property(property="from", type="integer", example=1, description="Starting item index of the current page"),
     *             @OA\Property(property="last_page", type="integer", example=5, description="Last page number"),
     *             @OA\Property(property="last_page_url", type="string", example="http://example.com/api/customers?page=5", description="URL of the last page"),
     *             @OA\Property(property="next_page_url", type="string", nullable=true, example="http://example.com/api/customers?page=2", description="URL of the next page"),
     *             @OA\Property(property="path", type="string", example="http://example.com/api/customers", description="Base URL for pagination"),
     *             @OA\Property(property="per_page", type="integer", example=15, description="Number of items per page"),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true, example=null, description="URL of the previous page"),
     *             @OA\Property(property="to", type="integer", example=15, description="Ending item index of the current page"),
     *             @OA\Property(property="total", type="integer", example=75, description="Total number of items in the collection")
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

    public function index()
    {
        return CustomerResource::collection(Customer::paginate());
    }

    /**
     * @OA\Post(
     *     path="/api/customer/save",
     *     tags={"Customers"},
     *     summary="Add or Update a Customer",
     *     description="This endpoint creates a new customer or updates an existing one based on the provided customer ID.",
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         description="Customer details for creation or update",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", nullable=true, example=null, description="Customer ID (if updating an existing customer)"),
     *             @OA\Property(property="name", type="string", example="John Doe", description="Customer's name"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com", description="Customer's email address"),
     *             @OA\Property(property="address", type="string", example="1234 Main St, City, Country", description="Customer's address"),
     *             @OA\Property(property="contact_number", type="string", example="1234567890", description="Customer's contact number"),
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=201,
     *         description="Customer created or updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Customer saved successfully"),
     *             @OA\Property(property="data", type="object",
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


    public function save(CustomerFormRequest $request)
    {
        return (new CreateCustomerAction)->handle($request);
    }


    public function show($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            return new CustomerResource($customer);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Customer Not Found',
                'message' => 'The requested customer does not exist.'
            ], 404);
        } catch (\Exception $e) {
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

            return $this->successResponse('Customer deleted successfully');
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
