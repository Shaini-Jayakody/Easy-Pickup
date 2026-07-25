<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'name' => ['required', 'string', 'max:255'],
            'id_num' => ['required', 'string', 'max:20', 'unique:users,id_num'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'address' => ['required', 'string', 'max:500'],
            'role' => ['nullable', 'in:user,admin,manager'],
        ];
    }

     /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.max' => 'Name cannot exceed 255 characters.',
            
            'id_num.required' => 'ID number is required.',
            'id_num.unique' => 'This ID number is already registered.',
            'id_num.max' => 'ID number cannot exceed 20 characters.',
            
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'age.required' => 'Age is required.',
            'age.min' => 'You must be at least 18 years old to register.',
            'age.max' => 'Age cannot exceed 100 years.',
            'age.integer' => 'Age must be a valid number.',
            
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be Male, Female, or Other.',
            
            'address.required' => 'Address is required.',
            'address.max' => 'Address cannot exceed 500 characters.',
            
            'role.in' => 'Invalid role selected.',
        ];
    }
}
