<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,medium,high,urgent',
            'required_by' => 'nullable|date|after:today',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Purchase order title is required.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'estimated_value.numeric' => 'Estimated value must be a valid number.',
            'estimated_value.min' => 'Estimated value cannot be negative.',
            'priority.required' => 'Priority is required.',
            'priority.in' => 'Priority must be one of: low, medium, high, urgent.',
            'required_by.date' => 'Required by must be a valid date.',
            'required_by.after' => 'Required by date must be in the future.',
        ];
    }
}