<?php

namespace App\Http\Requests\User_Rqeuests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Update_User_Request extends FormRequest
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
        $user_id = $this->route('user');

        return [
            'name' => 'sometimes|nullable|regex:/^[\p{L}\s]+$/u|min:2|max:50',
            'email' => ['sometimes','nullable', 'email', Rule::unique('users', 'email')->ignore($user_id)],
            'password' => 'sometimes|nullable|string|min:8',
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
            'name' => 'اسم المستخدم',
            'email' => 'الإيميل',
            'password' => 'كلمة المرور',
        ];
    }
    //===========================================================================================================================

    public function messages(): array
    {
        return [
            'unique' => ':attribute  موجود سابقاً , يجب أن يكون :attribute غير مكرر',
            'regex' => 'يجب أن يحوي  :attribute على أحرف فقط',
            'email' => 'يجب أن يكون الحقل :attribute يحوي على لإيميل من نمط @',
            'max' => 'الحد الأقصى لطول  :attribute هو 50 حرف',
            'name.min' => 'الحد الأدنى لطول :attribute على الأقل هو 2 حرف',
            'password.min' => 'الحد الأدنى لطول :attribute على الأقل هو 8 محرف',
        ];
    }
}
