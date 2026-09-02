<?php
namespace App\Services;
use App\Enums\{CardStatus,PaymentStatus,TransactionType};use App\Models\{Payment,Transaction,VirtualCard};use Illuminate\Support\Facades\DB;use Illuminate\Validation\ValidationException;use RuntimeException;
class PaymentService {
 public function create(int $userId,int $businessId,int $cardId,float $amount,string $gateway):Payment{
  return DB::transaction(function()use($userId,$businessId,$cardId,$amount,$gateway){
   $card=VirtualCard::query()->lockForUpdate()->findOrFail($cardId);
   if($card->business_id!==$businessId)throw ValidationException::withMessages(['card_id'=>'Selected card does not belong to the selected business.']);
   $user=\App\Models\User::findOrFail($userId);
   if($user->isAgent() && $card->agent_id!==$user->id)throw ValidationException::withMessages(['card_id'=>'Agents may only use their own unique virtual card.']);
   if($card->status!==CardStatus::ACTIVE)throw ValidationException::withMessages(['card_id'=>'Card is not active.']);
   if($card->expiry_date->isPast())throw ValidationException::withMessages(['card_id'=>'Card has expired.']);
   if($amount>(float)$card->per_transaction_limit)throw ValidationException::withMessages(['amount'=>'Amount exceeds the per-transaction limit.']);
   $daily=(float)$card->transactions()->where('status','successful')->whereDate('created_at',today())->sum('amount');
   $monthly=(float)$card->transactions()->where('status','successful')->whereBetween('created_at',[now()->startOfMonth(),now()->endOfMonth()])->sum('amount');
   if($daily+$amount>(float)$card->daily_limit)throw ValidationException::withMessages(['amount'=>'Amount exceeds today’s card limit.']);
   if($monthly+$amount>(float)$card->monthly_limit)throw ValidationException::withMessages(['amount'=>'Amount exceeds this month’s card limit.']);
   if((float)$card->current_usage+$amount>(float)$card->card_limit)throw ValidationException::withMessages(['amount'=>'Amount exceeds the remaining card limit.']);
   $payment=Payment::create(['business_id'=>$businessId,'user_id'=>$userId,'card_id'=>$cardId,'gateway'=>$gateway,'amount'=>$amount,'currency'=>config('payment.currency'),'reference'=>'PAY-'.now()->format('YmdHis').'-'.str()->upper(str()->random(6)),'status'=>PaymentStatus::CREATED]);
   $gatewayResult=app(PaymentGatewayManager::class)->driver($gateway)->createPayment(['reference'=>$payment->reference,'amount'=>$amount,'currency'=>$payment->currency,'user_id'=>$userId]);
   $payment->update(['status'=>$gatewayResult['status']==='failed'?PaymentStatus::FAILED:PaymentStatus::PENDING,'gateway_order_id'=>$gatewayResult['order_id']??null,'gateway_payment_id'=>$gatewayResult['payment_id']??null,'gateway_response'=>$gatewayResult]);
   return $payment->fresh();
  });
 }
 public function markSuccessful(Payment $payment,?string $gatewayPaymentId,array $response=[]):void{DB::transaction(function()use($payment,$gatewayPaymentId,$response){$locked=Payment::query()->lockForUpdate()->findOrFail($payment->id);if($locked->status===PaymentStatus::SUCCESSFUL)return;$card=VirtualCard::query()->lockForUpdate()->find($locked->card_id);if(!$card)throw new RuntimeException('Card not found.');$locked->update(['status'=>PaymentStatus::SUCCESSFUL,'gateway_payment_id'=>$gatewayPaymentId,'gateway_response'=>$response,'completed_at'=>now()]);Transaction::firstOrCreate(['payment_id'=>$locked->id],['business_id'=>$locked->business_id,'user_id'=>$locked->user_id,'card_id'=>$locked->card_id,'gateway'=>$locked->gateway,'gateway_transaction_id'=>$gatewayPaymentId,'transaction_type'=>TransactionType::PAYMENT,'amount'=>$locked->amount,'fee'=>0,'net_amount'=>$locked->amount,'status'=>'successful','reference'=>$locked->reference,'description'=>'Card payment','metadata'=>[]]);$card->increment('current_usage',(float)$locked->amount);$card->update(['last_transaction_at'=>now()]);});}
 public function markFailed(Payment $payment,array $response=[]):void{$payment->update(['status'=>PaymentStatus::FAILED,'gateway_response'=>$response]);}
}
