# Sura

<p align="center">
    <a href="https://packagist.org/packages/sura/framework">
        <img src="https://poser.pugx.org/sura/framework/downloads" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/sura/framework">
        <img src="https://poser.pugx.org/sura/framework/v/stable" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/sura/framework">
    <img src="https://poser.pugx.org/sura/framework/license" alt="License">
    </a>
</p>

## Installation
<a name="server-requirements"></a>
### Server Requirements

The Sura framework has a few system requirements. 

However, if you are not using Homestead, you will need to make sure your server meets the following requirements:

<div class="content-list" markdown="1" style="display: flex;flex-direction: column">

- PHP >= 7.4
- JSON PHP Extension
- ICONV PHP Extension
- GD PHP extension
- MySQLI PHP Extension
</div>

<a name="installing-sura"></a>
### Installing Sura

It's recommended that you use [Composer](https://getcomposer.org/) to install Sura.

```bash
$ composer create-project sura/sura:^0.8 MYPROJECT --prefer-dist
```

The database configuration is located in config/config.php

To create a migration, use the -migrate

```bash
$ php craft -migrate
```

To create a user admin, use the -make:add-user <name> <lastname> <mail> <pass>

```bash
$ php craft -make:add-user Ivan Petrov petrov@example.com password
```

<a name="configuration"></a>
### Configuration

#### Public Directory

After installing Sura, you should configure your web server's document / web root to be the `public` directory. The `index.php` in this directory serves as the front controller for all HTTP requests entering your application.

#### Configuration Files

All of the configuration files for the Sura framework are stored in the `config` directory.


<a name="urls"></a>
### URLs

#### Apache

Sura includes a `public/.htaccess` file that is used to provide URLs without the `index.php` front controller in the path. Before serving Sura with Apache, be sure to enable the `mod_rewrite` module so the `.htaccess` file will be honored by the server.

If the `.htaccess` file that ships with Sura does not work with your Apache installation, try this alternative:
```
    Options +FollowSymLinks -Indexes
    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
```
#### Nginx

If you are using Nginx, the following directive in your site configuration will direct all requests to the `index.php` front controller:
```
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover security related issues, please email semyon492@ya.ru instead of using the issue tracker.

## For enterprise

semyon492@ya.ru

### Financial Contributors

Become a financial contributor and help us sustain our community. (semyon492@ya.ru)

## License

The Sura Framework is licensed under the MIT license. See [License File](LICENSE.md) for more information.