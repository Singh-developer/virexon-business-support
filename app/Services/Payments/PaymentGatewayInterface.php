<?php
namespace App\Services\Payments;
interface PaymentGatewayInterface {public function createPayment(array $data):array;public function verifyPayment(array $data):array;public function handleWebhook(string $rawBody,array $headers):array;public function refund(array $data):array;public function getPaymentStatus(string $paymentId):array;}
