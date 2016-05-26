# SparkPost plugin for CakePHP

[![Build Status](https://travis-ci.org/narendravaghela/cakephp-sparkpost.svg?branch=master)](https://travis-ci.org/narendravaghela/cakephp-sparkpost)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This plugin provides email delivery using [SparkPost](https://www.sparkpost.com/).

## Requirements

This plugin has the following requirements:

* CakePHP 3.0.0 or greater.
* PHP 5.4.16 or greater.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

```
composer require narendravaghela/cakephp-sparkpost
```

After installation, [Load the plugin](http://book.cakephp.org/3.0/en/plugins.html#loading-a-plugin)
```php
Plugin::load('SparkPostEmail');
```
Or, you can load the plugin using the shell command
```sh
$ bin/cake plugin load SparkPostEmail
```

## Setup

Set your SparkPost credentials in `EmailTransport` settings in app.php

```php
'EmailTransport' => [
...
  'sparkpost' => [
      'className' => 'SparkPostEmail.SparkPost',
      'apiKey' => '123456789123456789' // your api key
  ]
]
```

And create new delivery profile for sparkpost in `Email` settings.

```php
'Email' => [
    'default' => [
        'transport' => 'default',
        'from' => 'you@localhost',
        //'charset' => 'utf-8',
        //'headerCharset' => 'utf-8',
    ],
    'sparkpost' => [
        'transport' => 'sparkpost'
    ]
]
```

## Usage

You can now simply use the CakePHP `Email` to send an email via SparkPost.

```php
$email = new Email('sparkpost');
$result = $email->from(['foo@example.com' => 'Example Site'])
  ->to('bar@example.com')
  ->subject('Welcome to CakePHP')
  ->template('welcome')
  ->viewVars(['foo' => 'Bar'])
  ->emailFormat('both')
  ->attachments([
      'cake_icon1.png' => Configure::read('App.imageBaseUrl') . 'cake.icon.png',
      'cake_icon2.png' => ['file' => Configure::read('App.imageBaseUrl') . 'cake.icon.png'],
      WWW_ROOT . 'favicon.ico'
  ])
  ->send('Your email content here');
```

You can also set custom headers using `ch:` prefix.

```php
$email = new Email('sparkpost');
$result = $email->from(['foo@example.com' => 'Example Site'])
  ->to('bar@example.com')
  ->addHeaders(['ch:My-Custom-Header' => 'Awesome header string'])
  ->addHeaders(['ch:Another-Custom-Header' => 'Awesome header string'])
  ->emailFormat('both')
  ->attachments([
      'cake_icon1.png' => Configure::read('App.imageBaseUrl') . 'cake.icon.png',
      'cake_icon2.png' => ['file' => Configure::read('App.imageBaseUrl') . 'cake.icon.png'],
      WWW_ROOT . 'favicon.ico'
  ])
  ->send('Your email content here');
```

You can set other custom parameters which SparkPost supports using `co:` prefix.

```php
$email = new Email('sparkpost');
$result = $email->from(['foo@example.com' => 'Example Site'])
  ->to('bar@example.com')
  ->emailFormat('both')
  ->addHeaders(['ch:My-Custom-Header' => 'Awesome header string'])
  ->addHeaders(['co:open_tracking' => true])
  ->addHeaders(['co:click_tracking' => true])
  ->addHeaders(['co:transactional' => true])
  ->addHeaders(['co:metadata' => ['meta1' => 'value1', 'meta2' => 'value2']])
  ->send('Your email content here');
```

That is it.

## Reporting Issues

If you have a problem with this plugin or any bug, please open an issue on [GitHub](https://github.com/narendravaghela/cakephp-sparkpost/issues).
