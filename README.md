Symfony product product catalog Application
========================

The "Symfony product Application" is a application created to show product catalog which includes,
      product CRUD functionality using authorised ROLE_ADMIN(admin) for the selected product,
      search product,
      publishing comments for the product,
      Comment Approve/Reject/Delete using authorised ROLE_ADMIN for the selected product.
      view application in locale lan3guage English,Uk, 
      User management with two roles ROLE_ADMIN and ROLE_USER,Update user details 
      Use of symfony console commands to add new user, delete user and view user list


User management:

      User roles -> role_admin,role_user

       * To use below command, open a terminal window, enter into your project directory
       * and execute the following:
       *
      You can create users by executing below commands:

        To find the help to execute command
        $ php bin/console app:add-user --help

        To create regular user
        $ php bin/console app:add-user

        To create admin user
        $ php bin/console app:add-user --admin

        To delete user
        $ php bin/console app:delete-user

        To list the user and send user report to the user   
        $ php bin/console app:list-users

      Update user details
      Update user password

role_admin functionalities:
-------------------------------------------------------
login with admin role
can change the language of the application redering
Front end ->
Admin can view product listing page including all the products.
Admin can approve/reject/delete comments for authorized product.
Admin can not approve/reject/delete comments for unauthorised products.
Admin can edit authorised or his own product
Admin can search all the product

Admin panel ->
Admin can view authorised(his own) product listing page.
Admin can add product.
Admin can edit and delete authorised products.

role_user functionalities:
-------------------------------------------------------
login with role user
can view all the products.
can view admin approved comments if any for the product.
can publish the comment for the product.
can search product
can change the language of the application redering

Requirements
------------

  * PHP 7.2.9 or higher;
  * PDO-mysql PHP extension enabled;
  * and the [usual Symfony application requirements]

Installation
------------

[Download Symfony] to install the `symfony` binary on your computer and run
this command:

you can use Composer:

```bash
$ composer create-project symfony/website-skeleton product-catalogue-symfony

OR 

$ git clone https://github.com/shilgari-vandana/product-catalogue-symfony.git
$ cd product-catalogue-symfony
$ composer update
```

Usage
---------------------------------------------------------------------------------------
Manual DB import::

Import the DB schema from ./product-catalogue-symfony/product-catalogue-symfony.sql

OR

Using symfony commands::

PHP BIN/CONSOLE DOCTRINE:DATABASE:CREATE

PHP BIN/CONSOLE DOCTRINE:MIGRATIONS:MIGRATE

To load dummy data into database,

PHP BIN/CONSOLE DOCTRINE:FIXTURES:LOAD

------------------------------------------

There's no need to configure anything to run the application. If you have
[installed Symfony], run this command and access the application in your
browser at the given URL (<http://localhost> by default):



```bash
$ cd product-catalogue-symfony/
$ symfony serve


```

If you don't have the Symfony binary installed, run `php -S localhost:8000 -t public/`
to use the built-in PHP web server or [configure a web server][3] like Nginx or
Apache to run the application.

Tests
-----

Execute this command to run tests:

```bash
$ cd  product-catalogue-symfony/
$ ./vendor/bin/phpunit

eg..

D:\Vandana\product-catalogue-symfony\vendor\bin>phpunit -c ..\..\phpunit.xml.dist
PHPUnit 7.5.20 by Sebastian Bergmann and contributors.

...................................                               35 / 35 (100%)

Time: 14.85 seconds, Memory: 48.00 MB

OK (35 tests, 74 assertions)

D:\Vandana\product-catalogue-symfony\vendor\bin>