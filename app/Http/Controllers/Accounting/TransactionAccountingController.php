<?php

namespace App\Http\Controllers\Accounting;

use App\Traits\ApiResponser;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionAccountingController extends Controller
{
    //
    use ApiResponser;

    public function index(Request $request)
    {
        $transactions = Transaction::with('products')
            ->where('created_at', '>', Carbon::now()->startOfDay())
            ->where('created_at', '<', Carbon::now()->endOfDay())
            ->get();

        $total = number_format((float)$transactions->sum('total'), 2, '.', '');
        $paymentDue = $transactions->sum('payment_due');
        $discount = $transactions->sum('discount_amount');
        $paid = $transactions->sum('paied');
        $total_product = $transactions->pluck('products')->collapse()->count();

        $chartData = [];

        $transactions->each(function($transaction) use (&$chartData){
            $data = [];
            $data['total'] = $transaction->total;
            $productName = '';
            if($transaction->products->count()){
                $transaction->products->each(function($product) use (&$productName){
                    $productName .= $product->name. ', ';
                });
            }
            $data['products'] = $productName . '('. $transaction->products->count(). ')';
            $data['color'] = $this->rand_color();
            $chartData[] = $data;
        });


        $data = [
            'total' => $total,
            'payment_due' => $paymentDue,
            'discount' => $discount,
            'total_product' => $total_product,
            'paid' => $paid,
            'transactions' => $transactions,
            'chart_data'    => $chartData
        ];

        return $this->successResponse($data, 200);
    }


}