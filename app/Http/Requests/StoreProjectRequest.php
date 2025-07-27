<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'funding_goal' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'expected_end_date' => 'required|date|after:start_date',
            'category' => 'required|string|max:100',
            'risk_level' => 'required|in:low,medium,high',
        ];
    }
}
