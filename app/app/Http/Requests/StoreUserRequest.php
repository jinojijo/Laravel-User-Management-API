<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $normalizedEmail = normalizeEmail($this->input('email'));
        $this->merge(['email' => $normalizedEmail]);

        return [
            'first_name'        => ['required','string','max:255','regex:/^[a-zA-Z\s\-\'\.]+$/',],
            'last_name'         => ['required','string','max:255','regex:/^[a-zA-Z\s\-\'\.]+$/',],
            'role'              => ['required','integer',Rule::in(User::getValidRoles()),],
            'email'             => ['required','string','email:rfc,dns','max:255','unique:users,email',],
            'password'          => ['required','string','min:8','regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',],
            'latitude'          => ['required','numeric','between:-90,90',],
            'longitude'         => ['required','numeric','between:-180,180',],
            'date_of_birth'     => ['required','date','before:today','after:1900-01-01',],
            'timezone'          => ['required','string','timezone',],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'first_name.regex'      => 'The first name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.regex'       => 'The last name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'role.in'               => 'The selected role is invalid. Must be 1 (Admin), 2 (Supervisor), or 3 (Agent).',
            'email.email'           => 'The email must be a valid email address.',
            'email.unique'          => 'The email has already been taken.',
            'password.regex'        => 'The password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.',
            'latitude.between'      => 'The latitude must be between -90 and 90 degrees.',
            'longitude.between'     => 'The longitude must be between -180 and 180 degrees.',
            'date_of_birth.before'  => 'The date of birth must be before today.',
            'date_of_birth.after'   => 'The date of birth must be after 1900-01-01.',
            'timezone.timezone'     => 'The timezone must be a valid timezone identifier.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'first_name'        => 'first name',
            'last_name'         => 'last name',
            'date_of_birth'     => 'date of birth',
        ];
    }
}