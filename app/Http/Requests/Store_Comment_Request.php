<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Store_Comment_Request extends FormRequest
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
            'created_by' => 'required|integer|exists:users,id',
            'comment' => 'required|string|min:20|max:255',
        ];
    }
    //===========================================================================================================================
    protected function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'status' => 'error 422',
            'message' => 'فشل التحقق يرجى التأكد من المدخلات',
            'errors' => $validator->errors(),
        ]));
    }
    //===========================================================================================================================
    protected function passedValidation()
    {
        //تسجيل وقت إضافي
        Log::info('تمت عملية التحقق بنجاح في ' . now());

    }
    //===========================================================================================================================
    public function attributes(): array
    {
        return [
            'created_by' => 'اسم المستخدم',
            'comment' => 'التعليق',
        ];
    }
    //===========================================================================================================================

    public function messages(): array
    {
        return [
            'required' => ' :attribute مطلوب',
            'string' => 'يحب أن يكون الحقل :attribute يحوي محارف',
            'min' => 'الحد الأدنى لطول :attribute على الأقل هو 4 حرف',
            'max' => 'الحد الأقصى لطول  :attribute هو 50 حرف',
            'integer' => 'يجب أن يكون الحقل :attribute من نمط int',
            'exists' => 'يجب أن يكون :attribute موجودا مسبقا',
        ];
    }
}
