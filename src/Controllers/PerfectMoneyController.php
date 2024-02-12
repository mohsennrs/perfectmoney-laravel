<?php

namespace Package\Perfectmoney\Controllers;

use Illuminate\Routing\Controller;
use Package\Perfectmoney\Perfectmoney;
use Illuminate\Http\Request;


class PerfectMoneyController extends Controller
{
    protected $handler;

    public function __construct()
    {
        $this->handler = new Perfectmoney();
    }

    public function sell(Request $request)
    {
        $this->validateInitiationRequest($request);
        return $this->handler->sell($request);
    }

    public function pmSuccess(Request $request)
    {
        return $this->handler->pmSuccess($request);

    }

    public function pmFail(Request $request)
    {
        return $this->handler->pmFail($request);

    }

    public function pmStatus(Request $request)
    {
        return $this->handler->pmStatus($request);
    }

    public function validateInitiationRequest($request)
    {
        $request->validate(['payment_amount' => 'required|numeric', 'payment_units' => 'required']);
    }
}