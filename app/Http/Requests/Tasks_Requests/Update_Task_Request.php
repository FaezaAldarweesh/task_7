<?php

namespace App\Http\Requests\Tasks_Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class Update_Task_Request extends FormRequest
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
        $id = $this->task->id;
        return [            
            'title' => 'sometimes|nullable|string|min:4|max:50|unique:tasks,title,'.$id,
            'description' => 'sometimes|nullable|string|min:20|max:255',
            'type' => 'sometimes|nullable|string|in:Bug,Feature,Improvment',
            'priority' => 'sometimes|nullable|string|in:Low,Medium,High',
            'due_date' => 'sometimes|nullable|date|after_or_equal:today',
            'assigned_to' => 'sometimes|nullable|integer|exists:users,id',
            'depends_on' => 'sometimes|nullable|array',
            'depends_on.*.id' => 'sometimes|nullable|exists:tasks,id',
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
            'title' => 'عنوان المهمة',
            'description' => 'وصف المهمة',
            'type' => 'نوع المهمة',
            'status' => 'حالة المهمة',
            'priority' => 'درجة أهمية المهمة',
            'due_date' => 'تاريخ التسليم',
            'assigned_to' => 'اسم الموظف',
        ];
    }
    //===========================================================================================================================

    public function messages(): array
    {
        return [
            'string' => 'يحب أن يكون الحقل :attribute يحوي محارف',
            'unique' => ':attribute  موجود سابقاً , يجب أن يكون :attribute غير مكرر',
            'title.min' => 'الحد الأدنى لطول :attribute على الأقل هو 4 حرف',
            'title.max' => 'الحد الأقصى لطول  :attribute هو 50 حرف',
            'description.min' => 'الحد الأدنى لطول :attribute على الأقل هو 20 حرف',
            'description.max' => 'الحد الأقصى لطول  :attribute هو 255 حرف',
            'type.in' => 'يجب أن يكون  :attribute إحدى الأنواع التالية : Bug أو Feature أو Improvment ',
            'priority.in' => 'يجب أن تكون قيمة الحقل :attribute إحدى القيم التالية : High,Medium,Low',
            'date' => 'يجب أن يكون الحقل :attribute تاريخاً',
            'after_or_equal' => 'يجب أن بكون :attribute بتاريخ اليوم و ما بعد',
            'integer' => 'يجب أن يكون الحقل :attribute من نمط int',
            'exists' => 'يجب أن يكون :attribute موجودا مسبقا',
        ];
    }
}
