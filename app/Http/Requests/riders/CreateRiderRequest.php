<?php

namespace App\Http\Requests\riders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRiderRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'user_id'=>['required','numeric',Rule::exists('users','id')],
            'shift_from_time'=>['nullable','string'],
            'shift_to_time'=>['nullable','string'],
            'vehicle_type'=>['nullable','string'],
            'status'=>['required','boolean']
        ];
    }

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }
}
