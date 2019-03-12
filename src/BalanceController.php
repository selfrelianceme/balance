<?php

namespace Selfreliance\Balance;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use App\Models\Payment_System;
use App\Models\Currency_Rate;
use DepositService;
use PaymentSystem;
use Withdraw;
use Referral;
use App\Libraries\DepositProfit\DepositProfit;
use Carbon\Carbon;
use Balance;
class BalanceController extends Controller
{
    /**
     * Index
     * @return view home with feedback messages
    */    
    public function index(Request $request)
    {
        // $total_deposits = DepositService::total_amount();
        $total_deposits = Balance::get_total_purchase();
        $total_withdraw = number(Withdraw::total_withdraw());
        // $total_referral = Referral::sum_total_refereals();
        $total_referral = '-';
        // $total_accurrals = $DepositProfit->sum_total_accurals();
        $total_accurrals = '-';

        $expenses = env('EXPENSES', 0);

        $total_profit_system = number($total_deposits-$total_withdraw-$expenses,2);
        

        $from_date = ($request->input('from'))?$request->input('from'):null;
        $to_date = ($request->input('to'))?$request->input('to'):null;

        if($from_date == null)
            $startOfMonth = Carbon::now()->startOfMonth();    
        else
            $startOfMonth = Carbon::parse($from_date);    

        if($from_date == null)
            $endOfMonth = Carbon::now()->endOfMonth();
        else
            $endOfMonth = Carbon::parse($to_date);    
        
        

        $purcharse = Balance::get_purcharse_for_chart($startOfMonth, $endOfMonth);
        
        $withdraw = Withdraw::get_withdraw_for_chart($startOfMonth, $endOfMonth);

        $data_for_chart = create_calendar_zeros_chart($startOfMonth, $endOfMonth);
        $max_value_chart = 0;
        foreach($data_for_chart as $key=>$row){
            if(array_key_exists($key, $purcharse)){
                $data_for_chart[$key]['purchase'] = $purcharse[$key];
                if($purcharse[$key] > $max_value_chart) $max_value_chart = $purcharse[$key];
            }

            if(array_key_exists($key, $withdraw)){
                $data_for_chart[$key]['withdraw'] = $withdraw[$key];
                if($withdraw[$key] > $max_value_chart) $max_value_chart = $withdraw[$key];
            }
        }
        // dd($data_for_chart);

        $payment_system = PaymentSystem::getAll('asc')->where('is_real_payment_system', 1)->values()->all();
        
        $today = get_group_by_payment_system(Carbon::today(), Carbon::today());
        $week = get_group_by_payment_system(Carbon::today()->subDays(7), Carbon::today());
        $month = get_group_by_payment_system(Carbon::today()->subDays(31), Carbon::today());
        $total = get_group_by_payment_system(false, false);
        
        $purcharse = [];
        foreach($payment_system as $row){
            $purcharse[] = [
                'title'    => $row->title,
                'currency' => $row->currency,
                'id'       => $row->id,
                'data' => [
                    'today' => (array_key_exists($row->id, $today))?$today[$row->id]:0,
                    'week' => (array_key_exists($row->id, $week))?$week[$row->id]:0,
                    'month' => (array_key_exists($row->id, $month))?$month[$row->id]:0,
                    'total' => (array_key_exists($row->id, $total))?$total[$row->id]:0
                ]
            ];
        }        

        $withdraw = [];
        foreach($payment_system as $row){
            $withdraw[] = [
                'title'    => $row->title,
                'currency' => $row->currency,
                'id'       => $row->id,
                'data' => [
                    'today' => Withdraw::get_by_payment_systems_ids(Carbon::today(), Carbon::today(), explode(',',$row->id)),
                    'week' => Withdraw::get_by_payment_systems_ids(Carbon::today()->subDays(7), Carbon::today(), explode(',',$row->id)),
                    'month' => Withdraw::get_by_payment_systems_ids(Carbon::today()->subDays(31), Carbon::today(), explode(',',$row->id)),
                    'total' => Withdraw::get_by_payment_systems_ids(false, false, explode(',',$row->id))
                ]
            ];
        }        

        // foreach($payment_system as $row){
        //     $currency = PaymentSystem::get_origin_currency($row->title);
        //     $withdraw[] = [
        //         'title'    => $row->title,
        //         'currency' => $currency,
        //         'data' => [
        //             'today' => Withdraw::get_by_payment_systems_ids(Carbon::today(), Carbon::today(), explode(',',$row->payment_systems_in)),
        //             'week' => Withdraw::get_by_payment_systems_ids(Carbon::today()->subDays(7), Carbon::today(), explode(',',$row->payment_systems_in)),
        //             'month' => Withdraw::get_by_payment_systems_ids(Carbon::today()->subDays(31), Carbon::today(), explode(',',$row->payment_systems_in)),
        //             'total' => Withdraw::get_by_payment_systems_ids(false, false, explode(',',$row->payment_systems_in))
        //         ]
        //     ];
        // }

        return view('balance::index')->with(compact(
        	'payment_system', 'total_deposits', 'total_withdraw', 'total_referral', 'total_accurrals', 'data_for_chart', 'max_value_chart', 'startOfMonth', 'endOfMonth', 'purcharse', 'withdraw', 'total_profit_system', 'expenses'
        ));
    }

    public function loadbalance($id){
        try{
            $payment = PaymentSystem::getAll()->where('id', $id)->first();

            $temp = $payment->class_name;
            if (!class_exists($temp)) {
                throw new \Exception('Empty payment module');
            }  

            $class = new $temp();
            if(!method_exists($class,'balance')){
                throw new \Exception('Not found payment module');
            }

            if(isset($class)){
                try {
                    $Currency_Rate = Currency_Rate::orderBy('id', 'desc')->first();
                    if($temp == '\Selfreliance\PayKassa\PayKassa'){
                        $res = $class->balance($payment->currency, $payment->title);    
                    }else{
                        $res = $class->balance($payment->currency);    
                    }
                    
                    $responce = $res." ".$payment->currency;
                    // if($payment->currency == "BTC"){
                    //     $responce .= " ~ ".$res*$Currency_Rate->btc_usd." USD";
                    // }
                    // if($payment->currency == "ETH"){
                    //     $responce .= " ~ ".$res*$Currency_Rate->eth_usd." USD";
                    // }
                    // if($payment->currency == "DASH"){
                    //     $responce .= " ~ ".$res*$Currency_Rate->dsh_usd." USD";
                    // }
                    // if($payment->currency == "LTC"){
                    //     $responce .= " ~ ".$res*$Currency_Rate->ltc_usd." USD";
                    // }
                    echo $responce;
                }catch(\Exception $e){
                    dd($e);
                }
            }            
        }catch(\Exception $e){
            echo $e->getMessage();
        }
        
    }


    public function save_expenses(Request $request){
        $this->validate($request, [
            'EXPENSES'     => 'required|numeric',
        ]);
        $path = $this->envPath();

        file_put_contents($path, str_replace(
            'EXPENSES='.env('EXPENSES'),
            'EXPENSES='.$request->input('EXPENSES'), file_get_contents($path)
        ));

        flash()->success('Новые условия сохранены');
        return redirect()->back();
    }


    /**
     * Get the .env file path.
     *
     * @return string
     */
    protected function envPath()
    {
        if (method_exists(app(), 'environmentFilePath')) {
            return app()->environmentFilePath();
        }
        return app()->basePath('.env');
    }
}