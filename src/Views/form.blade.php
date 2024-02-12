<form id='crypto-form' class='form' method='post' action='https://perfectmoney.is/api/step1.asp'><input type='hidden'
        name='PAYEE_ACCOUNT' value='{{config(' perfectmoney.pm_payee_account')}}'><input type='hidden' name='PAYEE_NAME'
        value='{{env(' APP_NAME')}}'><input type='hidden' name='PAYMENT_AMOUNT'
        value='{{$pmTransaction->payment_amount}}'><input type='hidden' name='PAYMENT_UNITS'
        value='{{$pmTransaction->payment_units}}'><input type='hidden' name='PAYMENT_ID'
        value='{{$pmTransaction->payment_id}}'><input type='hidden' name='STATUS_URL' value='{{route('
        pm.pm_status')}}'><input type='hidden' name='PAYMENT_URL' value='{{route(' pm.pm_payment')}}'><input
        type='hidden' name='NOPAYMENT_URL' value='{{route(' pm.pm_nopayment')}}'></form>