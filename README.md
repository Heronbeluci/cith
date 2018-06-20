# CITH
#### A Simple and complete authentication library for Codeigniter.

###### Installation:
First you will need to add a copy of [cith.php](https://github.com/HexSeed/cith/blob/master/cith.php "cith.php") in the `app/libraries` .
After that we should add the configuration variables in `app/config/config.php` .
```php
// - Auth Security
$config['Cith_cryptCost'] = '12';
$config['Cith_loginPage'] = '/login';

// - Auth Database
$config['Cith_table'] = 'accounts'; // Accounts table name.
$config['Cith_loginIndex'] = 'email'; // Username or Email column name.
$config['Cith_passIndex'] = 'password'; // Password column name.
```
Now you can load the library in a controller, with: *(we recommend load it in the constructor)*
```php
 $this->load->library('cith');
```

------------
###### How to use:
- Creating a new account:
> In the array you can put how many items do you want, all will be written in the database, but remember, you need to put the configured login and password indexes.
```php
$this->cith->register([
      'email' => 'admin@admin.com', // required item
      'password' => 'admin', // required item
      'nickname' => 'Super Admin' // optional item
], false) // if you want a session to be created after the registration, set true.
```
- Logging in a account:
> This function can return `invalid-login`, `invalid-password` or `authenticated`.
```php
this->cith->login('admin@admin.com', 'admin');
```
- Logging out a account:
> If you call without any parameter this function will redirect to login page.
```php
this->cith->logout('/exit-page');
```
- Retrieving account data.
> This will return a object with all account data, you can set the boolean to true if you want a uncached data, this will request a updated data from database.
```php
this->cith->account(false);
```
- Defining a restricted area, with only logged in accounts.
> Will redirect if the session does not exists, you can set the boolean to true if you want to check the password in the database, for any password changes or something else.
```php
this->cith->force(false);
```
