# FraudRecord API client

## Requirements

This library requires PHP 5.4+. You will also need an implementation for connecting to the FraudRecord server (e.g. through cURL). An example implementation can be found [here](https://github.com/Dormilich/ripedb-client/blob/master/tests/Test/Guzzle6Adapter.php).

## Usage

You will need to create an account on https://www.fraudrecord.com and make a reporter profile and obtain your API key.

With the (cURL) client and the API key you can set up the web service object.
```php
use Dormilich\WebService\FraudRecord\WebService;

$api = new WebService(new MyClient(), $apiKey);

// query a client in the FraudRecord database
$result = $api->query([
    'name'   => 'John Smith',
    'email'  => 'john.smith@example.com',
]);

// report a client to FraudRecord
$code = $api->report([
    '_type'  => 'chargeback',
    '_text'  => 'This client made a chargeback after 3 months of server use.',
    '_value' =>  6,
    'name'   => 'John Smith',
    'email'  => 'john.smith@example.com',
    'ip'     => '192.168.2.1',
]);

// delete a client’s report
$api->delete('ea864c03abd2ce90');
```

### Errors

If the FraudRecord API returns an error code, a `FraudRecordException` is thrown.

### Method description

```php
string public function WebService::report ( array $data )
```

Report a client to the FraudRecord service.

#### Arguments

**data**<br>
An array of key-value pairs containing information about the client itself and the reported behaviour. 
Required keys are `_text`, `_type`, `_value`, and the client data. See [Data Variables](https://fraudrecord.com/developers/#dv1) for a list of useful values.

#### Return Value

Returns the Report Code for the submitted report. This can be used to show the report details via `https://www.fraudrecord.com/api/?showreport={code}`.



```php
QueryResult public function WebService::query ( array $data )
```

Query the FraudRecord database for a specific client.

#### Arguments

**data**<br>
An array of key-value pairs containing information about the client to look up. See [Data Variables](https://fraudrecord.com/developers/#dv1) for a list of useful values.

#### Return Value

`query()` returns an array-like, immutable object containing the values for the parameters `value` (sum of infringement
levels), `count` (number of reports for this client), `reliability` (a value between 0 and 10 depicting the trustworthyness
of the classification), and `code` (Report Code).



```php
void public function WebService::delete ( string $code )
```

Delete the specified report.

#### Arguments

**code**<br>
The Report Code returned from a report or found in the result for a query.

#### Return Value

This method does not return anything.
