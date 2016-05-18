# Swedbank SPP
## About
Allows integration with Swedbank payment portal payment system across the Baltic and the Nordic. Supports the main Baltic banks direct payments (Banklinks), credit card and PayPal services. It provides unified workflow for working with various payment methods available in Swedbank payment portal service. The supported operations are:
+ Payment transaction initiating
+ Transaction result request
+ _Payment refunding (coming soon)_
+ Password change

See the [CHANGELOG](CHANGELOG.md) file for version history.

## Requirements
This library requires PHP 5.5+, CURL and SimpleXML functions to be available, and your server should be able to communicate with Swedbank servers. Valid credentials (that are obtained from Swedbank) are required to use payment gateway. 

## Installation

The recommended way of installation is by using Composer. Simply add dependency in your `composer.json`:
```json
"require" : {
    "webmasterlv/swedbank-spp": "^1.0"
}
```
or by issuing `composer require webmasterlv/swedbank-spp` from your command prompt or shell.

## Usage

Most of the operations are done through `Gateway` class. It contains methods to simplify interaction with the payment service. For a complete working example, please see `example.zip` in the sources folder. Follow instructions in `setup.php` file.

#### 1. Credentials:

First of all, you need valid credentials for development and/or production. Production is not required at testing stage, but you can add them later for easy switching between production and testing environments with one variable.

```php
use Swedbank\SPP\Accreditation;

$accreditation = new Accreditation();
$accreditation -> setDev('devuser', 'devpass', 'http://dev.example.com/payments/');
$accreditation -> setProd('produser', 'prodpass', 'http://www.example.com/payments/');
```

The first argument is what Swedbank calls 'vTID'. The third argument is response/return URL. It *should be* fully-qualified URL. This URL should be accessible from outside world, because to this URL payment gateway will send customers after payment. Also in this URL you will check status of the transaction.

#### 2. Merchant

Define information about merchant:

```php
use Swedbank\SPP\Merchant;

$merchant = new Merchant();
$merchant -> setRegion(Gateway::REGION_LAT);
$merchant -> setLanguage(Gateway::LANG_LAT);
```
You should call `setRegion()` if you operate in Lithuania or Estonia (region defaults to Latvia). Available options are:
```
Gateway::REGION_LAT Latvia
Gateway::REGION_LIT Lithuania
Gateway::REGION_EST Estonia
```
And also choose your preferred interface language (works for internet banks only, please consult documentation for supported languages):

```
Gateway::LANG_ENG   English 
Gateway::LANG_EST   Estonian
Gateway::LANG_LIT   Lithuanian
Gateway::LANG_LAT   Latvian
Gateway::LANG_RUS   Russian
```

#### 3. Initialize gateway

```php
use Swedbank\SPP\Gateway;

$gateway = new Gateway($accreditation, $merchant, Gateway::ENV_DEV);
```
Third argument defines your transaction environment. Available options are `Gateway::ENV_DEV` (testing) and `Gateway::ENV_PROD` (production). You should always test your transaction in testing environment before switching to production mode.

##### 3.1. Event logging

`Gateway` class supports PSR-3 compliant logger interface. Just pass your favorite logger implementation:
```php
$gateway -> setLogger($logger);
```

#### 4. Performing operations

##### 4.1. Create transaction
The first step to initiate payment, is to request payment gateway for a session. This introduces three more objects - Order, Customer and payment method. 
Describe your order:
```php
use Swedbank\SPP\Order;

$orderId = 12345;
$orderDescr = 'Payment for book';
$orderAmount = 10;

$order = new Order($orderId, $orderDescr, $orderAmount);
```
Where `$orderID` - your internal order ID, `$orderDescr` - textual description of payment subject, `$orderAmount` - payment amount in absolute units (10 = €10, 2.95 = €2.95).

Describe your customer. You can use constructor but it is shorter to create customer object statically:
```php
use Swedbank\SPP\Customer;

$customer = Customer::fromArray([
	'email' => 'email@example.com',
	'first_name' => 'Jānis',
	'last_name' => 'Bērziņš',
	'city' => 'Rīga',
	'phone' => '+37120000000',
	'zip_code' => 'LV-1000',
	'address' => 'Pilsētas iela 10-21',
	'country' => 'LV'
]);
```
Only email is mandatory. Provide as much data as you are willing to. The data gets passed to payment system.

Select your payment method:
```php
use Swedbank\SPP\Payment\CreditCard;
use Swedbank\SPP\Payment\InternetBank;
use Swedbank\SPP\Payment\PayPal;

// Internet banking
$method	 = new InternetBank(InternetBank::SWEDBANK);

// PayPal
$method = new PayPal();

// Credit Card
$method = new CreditCard();
```
For `InternetBank` you must pass required bank. Which banks are available depends on your agreement with Swedbank. Available options are:

    InternetBank::SWEDBANK      Swedbank
    InternetBank::NORDEA        Nordea
    InternetBank::SEB_LATVIA    SEB Latvia
    InternetBank::SEB_LITHUANIA SEB Lithuania
    InternetBank::DNB			DNB
    InternetBank::DANSKE		Danske
    InternetBank::CITADELE		Citadele


Request a payment session. At this point, you should have your internal order ID generated and passed to Order object. After you have successfully retrieved payment session, save reference ID and payment method somewhere, so you can get later it through your internal order ID.
```php
$result	 = $gateway -> createTransaction($order, $customer, $method);
if ($result -> isSuccess()) {
    // Save gateway order ID and payment method for later retrieval
    $referenceId = $result -> getReference();
    // Redirect customer to this URL
    $redirectUrl = $result -> getRedirectUrl(); // User redirect URL
    header("Location: ".$redirectUrl);
    exit;
} else {
    $errorCode = $result -> getStatus(); // Error code (see 5.2)
    $errorMessage = $result -> getMessage(); // Error message
    $internalCode = $result -> getRemoteStatus(); // Internal error code
}
```
If there was an error creating transaction, `$result` object contains some methods that will help you determine the cause - see 5.1 for details.


##### 4.2. Request transaction status

After payment, customer returns to your store URL, specified above in `Accreditation` object, along with some additional data in URL. You do not need to use them directly, gateway instead will check transaction status for you. If you are checking payment status in you return URL, call this command:
```php
$isExtended = false;

// Your internal order ID, this should always be present
$orderID = $_GET['_merchantRef'];

// The status returned from payment system. You should not rely on this! This is just to prematurely 
// indicate whether payment was successful or not. If you cannot detect transaction status at this moment,
// just redirect customer to hinted page. Available values are 'success' or 'fail'.
$status = $_GET['_banklinkStatus'];

// Reference ID. May not be present! Use $orderID to determine reference ID.
$referenceId = $_GET['dts_reference'];

// Assuming you now have $referenceId
// Assuming you have your Gateway instance ready

$method = new CreditCard(); // or PayPal() or InternetBank() depending on original payment method.

$status = $gateway -> getStatus($order, $customer, $method, $referenceId, $isExtended);

if ($status -> isSuccess()) {
    // Payment is successful!
    // Update data about your order accordingly.
}
```
A note on arguments:
`$isExtended` controls whether gateway should always request extended data (defaults to `false`). If you set this to `true`, operation will take longer to complete since it will make two network requests, but you will get your status faster (this is especially true for internet bank transactions). Transaction check immediately after payment most likely will return Pending status, whereas extended status will return Success. I recommend setting it to true if you want definite information about status (or don't want to make "Please wait, checking payment status..." page)

See 5.1 on details about response object.

##### 4.3. Extended info

If you need extended data of transaction, such as customer details, you can request it this way:
```php
$result = $gateway -> getPaymentInfo($reference, $paymethod, $order);
if ($result -> isSuccess()) {
    $data = $result -> getExtendedData();
    var_dump($data);
}
```
Also you can get the same data with `getStatus()` operation, by setting `$isExtended` argument to `true`.

##### 4.4. Change gateway password
You can change your password for current environment by calling:
```php
$result = $gateway -> setPassword('newpassword');

if ($result -> isSuccess()) {
    echo "Password changed to " . $result -> getNewPassword();
} else {
    echo $result -> getMessage();
}
```
If you pass empty string as password, payment system will generate random password for you. Please be sure to save new password.

##### 4.5. Payment notifications

SPP provides and event notification mechanism outside of the standard transaction messaging flow between the merchant and Payment Gateway. This allows you to receive information about transaction status as soon as it is available. SPP pushes `POST` request to merchant's provided URL.
To validate a transaction, you can use this code:
```php
$respond = true;
$result = $gateway -> validatePayment($respond);
if ($result->isSuccess()) {
    $order = $result -> getOrder();
    $orderId = $order -> getId(); // Your Order ID
}

```
`$respond` indicates whether automatically respond with success message. It sends XML headers and data, so it you need to respond manually, set this to false and send XML response as:
```xml
<Response>OK</Response>
```

#### 5. Responses

Each operation returns `Response` object that provides common methods to check for status.

##### 5.1. Response object

`Response` interface delegates following methods:

`isSuccess()` - whether requested operation completed successfully. Meaning of success depends on operation context. For transaction status, it means that customer payment was successful.

`getMessage()` - message associated with operation, typically this means error description.

`getReference()` - returns payment system reference id. Each requests have its unique reference ID, but only some are useful to you (for example, you should save reference ID when creating new transaction).

`getStatus()` - status code of operation. See 5.2 for available values.

`getSource()` - source of response (error). Possible values are - `network` (error happened in within you host), `banklink` (error in payment service)

`getRemoteStatus()` - internal result code of operation. Please consult Swedbank documentation on these codes.

##### 5.2. Response constants

    Gateway::STATUS_SUCCESS	    - operation was successful;
	Gateway::STATUS_PENDING	    - payment status is not available;
	Gateway::STATUS_ERROR       - operation failed;
	Gateway::STATUS_CANCELED	- payment was canceled;
	Gateway::STATUS_INVESTIGATE - requires investigation;
	Gateway::STATUS_REFUSED     - payment was refused;
	Gateway::STATUS_REFUNDED    - payment is refunded;
	Gateway::STATUS_TIMEOUT     - timeout while communicating with payment service;
	Gateway::STATUS_STARTED     - payment just started;
	Gateway::STATUS_UNKNOWN     - unknown error.


#### Support
If you encounter problems integrating payment system, when results are not what you expected or there seems to be a bug, don't hesitate to create a pull request or issue.

#### Contributing
See the [CONTRIBUTING](CONTRIBUTING.md) file.
