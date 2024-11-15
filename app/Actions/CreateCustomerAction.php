<?php

namespace App\Actions;

use App\Http\Traits\ResponserTrait;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class CreateCustomerAction
{
    use ResponserTrait;
    public function handle($request)
    {
        try {
            // Determine if adding a new customer or updating an existing one
            if (empty($request->id)) {
                $customer = new Customer();
            } else {
                $customer = Customer::findOrFail($request->id);
            }
            // Assign customer attributes
            $customer->name = $request->name;
            $customer->email = $request->email;
            $customer->address = $request->address;
            $customer->contact_number = $request->contact_number;
            $customer->save();

            return $this->successResponse('Customer saved successfully', $customer);
        } catch (\Exception $e) {
            Log::error('Error saving customer: ' . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse('Server Error', 500);
        }
    }
}
