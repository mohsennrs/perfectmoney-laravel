<?php  
namespace Package\Perfectmoney\Exceptions;
use Exception;
use Illuminate\Notifications\Notification;

class InvalidConfiguration extends Exception
{
    public static function emptyPayeeAccount() {
    	return new self('Payee account parameter cannot be empty. Check perfectmoney config file for details.');
    }

    public static function emptyAlternativePassphrase() {
    	return new self('Alternative passphrase cannot be empty. Check perfectmoney config file for details.');
    }

    public static function emptyAccountId() {
    	return new self('Account ID cannot be empty. Check perfectmoney config file for details.');
    }

    public static function emptyAccountPassword() {
    	return new self('Account password cannot be empty. Check perfectmoney config file for details.');
    }
}
?>