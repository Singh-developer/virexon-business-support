<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class BusinessRequest extends FormRequest {public function authorize():bool{return (bool)$this->user();}public function rules():array{return ['name'=>'required|string|max:180','contact_person'=>'nullable|string|max:180','email'=>'nullable|email|max:180','phone'=>'nullable|string|max:30','address'=>'nullable|string|max:1000','tax_number'=>'nullable|string|max:80','status'=>'required|in:active,inactive'];}}
