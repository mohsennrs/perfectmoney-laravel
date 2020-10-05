# perfectmoney-laravel
A wrapper on perfect-money's API

Installation:

	composer require mohsen-nurisa/perfectmoney-laravel

Usage: 
After installaion, execute the line below to publish the ServiceProvider and create necessary tables:

	php artisan vendor:publish
	php artisan migrate

After publishing the provider, you will need to except the following route in your VerifyCsrfToken middleware:

	'/pm/status',
	'/pm/payment',
	'/pm/nopayment',
	'/redeem'

In order to use perfectmoney payment gateway, put following lines in .env file and fill them with the informations from your own account:

	PM_PAYEE_ACCOUNT=
	ALTERNATIVE_PASSPHRASE=

And to use perfectmoney e-voucher redeem, put following lines in .env file and fill them with the informations from your own account:

	PM_ACCOUNT_ID=
	PM_ACCOUNT_PASSWORD=

To start perfectmoney payment, add following to you code:

	use Package\Perfectmoney\Facades\Perfectmoney;

Then you can initiate transfere using line below:

	Perfectmoney::sell($params);

The $params variable must contain the following parameters:

	'payment_amount' => the amount of payment,
	'payment_units' => The fiat that you want to use, ex:USD

Then attach the form inside 'data' index of the response into your then submit the form.
Two callback functions are required to be set in perfectmoney.php file inside config directory. Follow the example below:
	
	'pm_success_callback' => 'App\Http\Controllers\PerfectmoneyController::success', // callback function for successfull transfer
	'pm_fail_callback' => 'App\Http\Controllers\PerfectmoneyController::fail', // callback function unsuccessfull transfer
	'pm_fail_status' => 'App\Http\Controllers\PerfectmoneyController::status', // callback function to handle order status after successfull transfer
  
the 'success' and 'fail' methods must be static.
  
To redeem e-voucher use following line:

	Perfectmoney::redeemVoucher($params);

$params variable must contain following argumants:

	'vpm_voucher' => the voucher
	'vpm_activation_code' => The activation code

Note:
To use this wrapper, you first need to enable API in your perfectmoney account. Also you will need to set an Alternative Passphrase.

License
The MIT License (MIT). Please see License File for more information.





