<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Contracts\Auth\Guard;
use App\Customer;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
    	if (\Request::has('CompanyNumber')) {
    		$company_number = \Request::get('CompanyNumber');
    	    $customer = Customer::where('company_number','=',decrypt($company_number))->first();
    	    $customer['company_number'] = encrypt($customer['company_number']);
    	    
            \View::share('customer_details',$customer);
        }
    }
}
