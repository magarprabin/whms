<?php

namespace App\Http\Requests\logisticCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLogisticCategoryRequest extends FormRequest
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
            'name'=>['required','string','max:30','unique:logistic_categories,name'],
            'category_id'=>['required','array'],
            'category_id.*'=>['required','numeric',Rule::exists('categories','id')],
            'status'=>['required','boolean']
        ];
    }

    public function messages()
    {
        return [
            'category_id.required'=>'Category field is required',
        ];
    }
}
