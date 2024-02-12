<?php

Route::group(['prefix' => 'pm', 'as' => 'pm.', 'namespace' => 'Package\Perfectmoney\Controllers'], function () {
	Route::post('sell', ['as' => 'sell', 'uses' => 'PerfectMoneyController@sell']);

	Route::post('/status', [
		'as' => 'pm_status',
		'uses' => 'PerfectMoneyController@pmStatus',
	]);

	Route::post('/payment', [
		'as' => 'pm_payment',
		'uses' => 'PerfectMoneyController@pmSuccess',
	]);

	Route::post('/nopayment', [
		'as' => 'pm_nopayment',
		'uses' => 'PerfectMoneyController@pmFail',
	]);
});