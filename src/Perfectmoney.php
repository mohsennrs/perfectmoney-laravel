<?php 
namespace Package\Perfectmoney;

use Auth;
use Illuminate\Http\Request;
use Package\Perfectmoney\Contracts\PerfectmoneyContract;
use Package\Perfectmoney\Exceptions\InvalidConfiguration;
use Package\Perfectmoney\Models\PerfectMoneyTransaction;
use Session;
class Perfectmoney extends PerfectmoneyContract
{	
    
    const CREATE_URL = "https://perfectmoney.is/acct/ev_create.asp";
    
    const ACTIVATE_URL = "https://perfectmoney.is/acct/ev_activate.asp";
    
    const RETURN_URL = "https://perfectmoney.is/acct/ev_remove.asp";

    protected $pm_payee_account;
    protected $alternate_phrase;
    protected $pm_account_id;
    protected $pm_account_password;


    public function __construct()
    {
        $this->pm_payee_account = config('perfectmoney.pm_payee_account');
        $this->alternate_phrase = config('perfectmoney.alternative_passphrase');
        $this->pm_account_id = config('perfectmoney.pm_account_id');
        $this->pm_account_password = config('perfectmoney.pm_account_password');
    }

    public function buy() 
    {
    	
    }

    public function sell($params) 
    {
        $request = new Request($params);

        $this->requiredSellParamsCheck($request);

    	$pmTransaction = new PerfectMoneyTransaction;
    	$pmTransaction->payment_id    = $request->get('payment_id');
    	$pmTransaction->payment_amount= $request->get('payment_amount');
    	$pmTransaction->payment_units = strtoupper($request->get('payment_units'));
    	$pmTransaction->payee_account = $this->pm_payee_account;

    	// $order->save();
    	$pmTransaction->save();

    	return ['status'=>'success','action'=>'submit_form','data'=>view('mohsen-nurisa/perfectmoney-laravel::form',compact('pmTransaction'))->render(),'message'=>__('sell_initiated_successfully')];

    }

    public function redeemVoucher($params) {
        $request = new Request($params);

        $this->requiredRedeemParamsCheck($request);


        $result = $this->submit(self::ACTIVATE_URL, [
            'Payee_Account' => $this->pm_payee_account,
            'ev_number' => $request->get('vpm_voucher'),
            'ev_code' => $request->get('vpm_activation_code'),
        ]);

        return $result;

    }

    public function pmSuccess(Request $request) 
    {
    	$payment_id = $request->PAYMENT_ID;

    	$pmTransaction = PerfectMoneyTransaction::where('payment_id', $payment_id)->first();
    	if(!$pmTransaction){
    		return abort(404);
    	}


        if (config('perfectmoney.pm_success_callback')) {
            $callback = config('perfectmoney.pm_success_callback');
            $params = ['status' => 'success', 'payment_id' => $request->PAYMENT_ID];
            $callback($params);
            // call_user_func_array(config('perfectmoney.pm_success_function'), $params);
            // call_user_func(config('perfectmoney.pm_success_function'));
            return true;
        }

        return ['status' => 'success', 'payment_id' => $request->get('PAYMENT_ID')];
	    
    }

    public function pmFail(Request $request)
    {
        $payment_id = $request->PAYMENT_ID;

        $pmTransaction = PerfectMoneyTransaction::where('payment_id', $payment_id)->firstOrFail();

        if ($pmTransaction->status == 'banking') {

            PerfectMoneyTransaction::where('id', $pmTransaction->id)->update(['status' => 'canceled']);

            if (config('perfectmoney.pm_fail_callback')) {
                $callback = config('perfectmoney.pm_fail_callback');
                $params = ['status' => 'failed', 'payment_id' => $request->get('PAYMENT_ID')];
                $callback($params);
                // call_user_func_array(config('perfectmoney.pm_success_function'), $params);
                // call_user_func(config('perfectmoney.pm_success_function'));
                return true;
            }
        }

        return ['status' => 'success', 'message' => 'pmFail', 'payment_id' => $request->get('PAYMENT_ID')];
    }

    public function pmStatus(Request $request)
    {
        $payment_id = $request->get('PAYMENT_ID');

        $pmTransaction = PerfectMoneyTransaction::where('payment_id', $payment_id)->where('status', 'banking')->first();

        if(!$pmTransaction){
        	return abort(422);
        }
        // define('ALTERNATE_PHRASE_HASH',  setting(''));

        // Path to directory to save logs. Make sure it has write permissions.
        define('PATH_TO_LOG',  '/');
        $string=
              $_POST['PAYMENT_ID'].':'.$_POST['PAYEE_ACCOUNT'].':'.
              $_POST['PAYMENT_AMOUNT'].':'.$_POST['PAYMENT_UNITS'].':'.
              $_POST['PAYMENT_BATCH_NUM'].':'.
              $_POST['PAYER_ACCOUNT'].':'.strtoupper(md5($this->alternate_phrase)).':'.
              $_POST['TIMESTAMPGMT'];

        $hash=strtoupper(md5($string));
        

        /* 
           Please use this tool to see how valid hash is generated: 
           https://perfectmoney.is/acct/md5check.html 
        */
        if($hash==$_POST['V2_HASH']){ // processing payment if only hash is valid

           /* In section below you must implement comparing of data you received
           with data you sent. This means to check if $_POST['PAYMENT_AMOUNT'] is
           particular amount you billed to client and so on. */

           if($_POST['PAYMENT_AMOUNT']==$pmTransaction->payment_amount 
            && $_POST['PAYEE_ACCOUNT']==$pmTransaction->payee_account 
            && $_POST['PAYMENT_UNITS']==$pmTransaction->payment_units){

                $pmTransaction->v2_hash = $_POST['V2_HASH'];
                $pmTransaction->payer_account = $_POST['PAYER_ACCOUNT'];
                $pmTransaction->timestampgmt = $_POST['TIMESTAMPGMT'];
                $pmTransaction->payment_batch_num = $_POST['PAYMENT_BATCH_NUM'];

                $pmTransaction->status = 'paid';

                $pmTransaction->save();

                if (config('perfectmoney.pm_status_callback')) {
                    $callback = config('perfectmoney.pm_status_callback');
                    $params = ['status' => 'failed', 'payment_id' => $request->get('PAYMENT_ID')];
                    $callback($params);
                    // call_user_func_array(config('perfectmoney.pm_success_function'), $params);
                    // call_user_func(config('perfectmoney.pm_success_function'));
                    return 'true';
                }

                return 'true';
           
            }

        }else{ // you can also save invalid payments for debug purposes

           // uncomment code below if you want to log requests with bad hash
            $f=fopen('request.log', "ab+");
           fwrite($f, date("d.m.Y H:i")."; REASON: bad hash; POST: ".serialize($_POST)."; STRING: $string; HASH: $hash\n");
           fclose($f); 
           print_r(date("d.m.Y H:i")."; REASON: bad hash; POST: ".serialize($_POST)."; STRING: $string; HASH: $hash\n");
        }

    }

    public function requiredSellParamsCheck($request) {
        
        if (!$this->pm_payee_account) {
            throw InvalidConfiguration::emptyPayeeAccount();
        }

        if (!$this->alternate_phrase) {
            throw InvalidConfiguration::emptyAlternativePassphrase();
        }

        $request->validate(['payment_amount' => 'required|numeric', 'payment_units' => 'required|string']);

    }

    public function requiredRedeemParamsCheck($request)
    {
        if (!$this->pm_account_id) {
            throw InvalidConfiguration::emptyAccountId();
        }

        if (!$this->pm_account_password) {
            throw InvalidConfiguration::emptyAccountPassword();
        }

        return $request->validate(['vpm_voucher' => 'required|string', 'vpm_activation_code' => 'required|string']);

    }

    protected function submit($url, $parameters, $method = "POST"){

        $curl = curl_init( $url );

        curl_setopt($curl, CURLOPT_VERBOSE, true);

        curl_setopt($curl, CURLOPT_POST, true);

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 

        curl_setopt($curl, CURLOPT_VERBOSE, true);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $parameters = array_merge($parameters, [
            'AccountID' => $this->pm_account_id,
            'PassPhrase' => $this->pm_account_password,
        ]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));

        $response = curl_exec($curl);

        if (curl_errno($curl)){
            return ['status'=>'failed','message' => curl_error($curl)];
        }

        if (empty($response)){
            return ['status'=>'failed','message' => 'Connection failed',];
        }

        curl_close($curl);

       $curl = null;

       $extracted = [];

       if(!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $response, $extracted, PREG_SET_ORDER)){
          return ['status'=>'failed','message'=> 'Ivalid input'];
       }

       $ar= [];
       foreach($extracted as $item){
          $key=$item[1];
          $ar[$key]=$item[2];
       }
       // if (in_array('ERROR',array_keys($ar))) {
       //     return ['status'=>'failed', 'data'=>array_values($ar)];
       // }

       return ['status'=>'success', 'response' => $ar];

    }
}

?>