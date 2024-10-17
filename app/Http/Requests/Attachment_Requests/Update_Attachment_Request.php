<?php

namespace App\Http\Requests\Attachment_Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Update_Attachment_Request extends FormRequest
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
            'attachment' => 'sometimes|nullable|file|mimes:doc,docx,zip,pdf,txt|max:512000',
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
            'attachment' => 'ملف المرفقات',
        ];
    }
    //===========================================================================================================================

    public function messages(): array
    {
        return [
            'file' => 'يجب أن يكون :attribute ملفاَ ',
            'mimes' => 'يجب أن يكون  :attribute من نمط word , pdf , zip , txt',
            'max' => 'الحد الأقصى لحجم  :attribute هو نصف جيغا ',
        ];
    }
}
