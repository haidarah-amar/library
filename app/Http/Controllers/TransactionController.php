<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Models\Book;
use App\Http\Requests\TransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ReturnTransactionRequest;
use App\Http\Controllers\Controller;


class TransactionController extends Controller
{
    
    public function index(Request $request)
{
    $validated = $request->validate([
        'status' => ['nullable', 'in:reserved,received,returned,expired,all']
    ]);

    $query = Transaction::with(['book', 'bill.customer']);

    if (!isset($validated['status'])) {
        $query->where('status', 'reserved');
    } elseif ($validated['status'] === 'all') {
        $query->whereIn('status', ['reserved', 'received']);
    } else {
        $query->where('status', $validated['status']);
    }

    $transactions = $query->orderByDesc('created_at')->paginate(10);

    return response()->json([
        'data' => $transactions
    ]);
}

    
public function store(TransactionRequest $request)
{
    DB::beginTransaction();

    try {

        $book = Book::where('id', $request->book_id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($book->total_copies < 1) {
            DB::rollBack();
            return response()->json([
                'message' => 'الكتاب غير متوفر حالياً'
            ], 400);
        }

        // 🔹 تأكيد حجز سابق
        if ($request->has('transaction_id')) {

            $transaction = Transaction::where('id', $request->transaction_id)
                ->where('status', 'reserved')
                ->where('book_id', $book->id)
                ->lockForUpdate()
                ->firstOrFail();

            $transaction->update([
                'status' => 'received',
                'delivered_at' => now(),
                'due_date' => $request->due_date,
            ]);

        } else {

            // 🔹 إنشاء عملية جديدة
            $transaction = Transaction::create([
                'bill_id' => $request->bill_id,
                'book_id' => $book->id,
                'price' => $request->price,
                'mortgage' => $request->mortgage,
                'extra_price' => 0,
                'delivered_at' => now(),
                'due_date' => $request->due_date,
                'status' => 'received',
            ]);
        }

        // 🔹 إنقاص نسخة واحدة بعد نجاح العملية
        $book->decrement('total_copies');

        DB::commit();

        return response()->json([
            'message' => 'تمت العملية بنجاح',
            'data' => $transaction
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => 'حدث خطأ أثناء تنفيذ العملية',
            'error' => $e->getMessage()
        ], 500);
    }
} 
    public function show($id)
{
    $transaction = Transaction::with([
            'book',
            'bill.customer' 
        ])
        ->findOrFail($id);

    return response()->json([
        'data' => $transaction
    ]);
}


public function returnBook(ReturnTransactionRequest $request, $id)
{
    DB::beginTransaction();

    try {

        // قفل العملية
        $transaction = Transaction::where('id', $id)
            ->where('status', 'received')
            ->lockForUpdate()
            ->firstOrFail();

        // قفل الكتاب المرتبط
        $book = Book::where('id', $transaction->book_id)
            ->lockForUpdate()
            ->firstOrFail();

        // تحقق منطقي: لا يمكن إعادة مبلغ أكبر من الرهن
        if ($request->customer_return_amount > $transaction->mortgage) {
            DB::rollBack();
            return response()->json([
                'message' => 'المبلغ المعاد لا يمكن أن يكون أكبر من مبلغ التأمين'
            ], 400);
        }

        // تحديث العملية
        $transaction->update([
            'status' => 'returned',
            'returned_at' => now(),
            'customer_return_amount' => $request->customer_return_amount,
        ]);

        // زيادة عدد النسخ
        $book->increment('total_copies');

        DB::commit();

        return response()->json([
            'message' => 'تمت إعادة الكتاب بنجاح',
            'data' => $transaction
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => 'حدث خطأ أثناء إعادة الكتاب',
            'error' => $e->getMessage()
        ], 500);
    }
}
 
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    public function destroy(Transaction $transaction)
    {
        //
    }
}
