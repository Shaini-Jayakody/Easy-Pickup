<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait UserValidationTrait
{
    /**
     * Validate user registration data
     */
    public function validateUserRegistration(array $data)
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s.]+$/'],
            'id_num' => ['required', 'string', 'max:20', 'min:4', 'regex:/^[A-Za-z0-9]+$/', 'unique:users,id_num'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'address' => ['required', 'string', 'max:500', 'min:5'],
            'role' => ['nullable', 'string', 'in:user,admin,manager'],
        ], [
            'name.required' => 'Full name is required.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'name.regex' => 'Name should only contain letters, spaces, and periods.',
            
            'id_num.required' => 'ID Number is required.',
            'id_num.unique' => 'This ID Number is already registered.',
            'id_num.max' => 'ID Number cannot exceed 20 characters.',
            'id_num.min' => 'ID Number must be at least 4 characters.',
            'id_num.regex' => 'ID Number should contain only letters and numbers.',
            
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'age.required' => 'Age is required.',
            'age.min' => 'You must be at least 18 years old.',
            'age.max' => 'Age cannot exceed 100 years.',
            'age.integer' => 'Age must be a valid number.',
            
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be Male, Female, or Other.',
            
            'address.required' => 'Address is required.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'address.min' => 'Address must be at least 5 characters.',
            
            'role.in' => 'Invalid role selected.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Get frontend validation rules for JavaScript
     */
    public function getFrontendValidationRules()
    {
        return [
            'name' => [
                'required' => true,
                'min' => 2,
                'max' => 255,
                'pattern' => '/^[a-zA-Z\\s.]+$/',
                'messages' => [
                    'required' => 'Full name is required.',
                    'min' => 'Name must be at least 2 characters.',
                    'max' => 'Name cannot exceed 255 characters.',
                    'pattern' => 'Name should only contain letters, spaces, and periods.'
                ]
            ],
            'id_num' => [
                'required' => true,
                'min' => 4,
                'max' => 20,
                'pattern' => '/^[A-Za-z0-9]+$/',
                'messages' => [
                    'required' => 'ID Number is required.',
                    'min' => 'ID Number must be at least 4 characters.',
                    'max' => 'ID Number cannot exceed 20 characters.',
                    'pattern' => 'ID Number should contain only letters and numbers.'
                ]
            ],
            'email' => [
                'required' => true,
                'pattern' => '/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/',
                'messages' => [
                    'required' => 'Email address is required.',
                    'pattern' => 'Please enter a valid email address.'
                ]
            ],
            'password' => [
                'required' => true,
                'min' => 8,
                'messages' => [
                    'required' => 'Password is required.',
                    'min' => 'Password must be at least 8 characters.'
                ]
            ],
            'age' => [
                'required' => true,
                'min' => 18,
                'max' => 100,
                'messages' => [
                    'required' => 'Age is required.',
                    'min' => 'You must be at least 18 years old.',
                    'max' => 'Age cannot exceed 100 years.'
                ]
            ],
            'gender' => [
                'required' => true,
                'values' => ['Male', 'Female', 'Other'],
                'messages' => [
                    'required' => 'Please select your gender.',
                    'values' => 'Gender must be Male, Female, or Other.'
                ]
            ],
            'address' => [
                'required' => true,
                'min' => 5,
                'max' => 500,
                'messages' => [
                    'required' => 'Address is required.',
                    'min' => 'Address must be at least 5 characters.',
                    'max' => 'Address cannot exceed 500 characters.'
                ]
            ]
        ];
    }
}