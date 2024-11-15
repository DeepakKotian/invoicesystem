<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id' => 'nullable|exists:customers,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . request()->id,
            'address' => 'required|string|max:500',
            'contact_number' => 'required|string|regex:/^[0-9]{10,15}$/',
        ];
    }
}
