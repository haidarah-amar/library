<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
        // مطلوب دائماً
            'book_id' => ['required', 'exists:books,id'],
            'due_date' => ['required', 'date', 'after:today'],

            // مطلوب فقط عند إنشاء عملية جديدة (كتاب غير محجوز)
            'bill_id' => ['required_without:transaction_id', 'exists:bills,id'],
            'price' => ['required_without:transaction_id', 'numeric', 'min:0'],
            'mortgage' => ['required_without:transaction_id', 'numeric', 'min:0'],

            // موجود فقط عند تسليم حجز سابق
            'transaction_id' => ['nullable', 'exists:transactions,id'],

            // حماية من التلاعب
            'status' => ['prohibited'],
            'delivered_at' => ['prohibited'],
            'extra_price' => ['prohibited'],
            'returned_at' => ['prohibited'],
            'customer_return_amount' => ['prohibited'],
    ];
}
}
