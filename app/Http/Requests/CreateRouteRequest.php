<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRouteRequest extends FormRequest
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
            'name'=>['required','min:1','max:255'],
            'start_location'=>['required','min:1','max:255'],
            'end_location'=>['required','min:1','max:255'],
            'supplier_id'=>['required','array'],
            'supplier_id.*'=>['required','numeric',Rule::exists('suppliers','id')],
            'status'=>['required','boolean']
        ];
    }

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }
}
