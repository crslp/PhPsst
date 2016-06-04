# PhPsst

A PHP library for distributing (one time) passwords/secrets in a more secure way

## Installation
Add the package as a requirement to your `composer.json`:
```bash
$ composer require felixsand/PhPsst
```

##Usage
```php
<?php

use PhPsst\PassDist;

$storage = new StorageInterface();
$phPsst = new PhPsst($storage);
$secret = $phPsst->store('my secret password');
echo 'The passwords ID and encryption key: ' . $secret;
echo 'The password: ' . $phPsst->retrieve($secret);
```


##Requirements
- PHP 5.6 or above.
- Redis

##Author
Felix Sandström <http://github.com/felixsand>

##License
Licensed under the MIT License - see the `LICENSE` file for details.
