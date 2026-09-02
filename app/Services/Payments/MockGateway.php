<?php
namespace App\Services\Payments;
class MockGateway implements PaymentGatewayInterface {
 public function createPayment(array $data):array{return ['status'=>'created','order_id'=>'mock_'.bin2hex(random_bytes(8)),'payment_id'=>null,'checkout_url'=>url('/payments/mock/'.$data['reference'])];}
 public function verifyPayment(array $data):array{return ['status'=>$data['status']??'successful','payment_id'=>$data['payment_id']??'mock_pay_'.bin2hex(random_bytes(6))];}
 public function handleWebhook(string $rawBody,array $headers):array{return ['event_id'=>hash('sha256',$rawBody),'event_type'=>'mock.payment','payload'=>json_decode($rawBody,true)??[]];}
 public function refund(array $data):array{return ['status'=>'refunded','refund_id'=>'mock_ref_'.bin2hex(random_bytes(6))];}
 public function getPaymentStatus(string $paymentId):array{return ['status'=>'successful','payment_id'=>$paymentId];}
}
