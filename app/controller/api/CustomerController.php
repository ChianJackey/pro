<?php
declare (strict_types = 1);

namespace app\controller\api;

use think\Request;
use app\models\Customer;

class CustomerController
{
    public function customerAll(Request $request) {
        $name = $request->param('name', '');
        return getRsp(200, Customer::getCustomerAllOrName($name, -1));
    }
}
