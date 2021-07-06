[![Test and Verify](https://github.com/otto-de/marketplace-php-sdk/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/otto-de/marketplace-php-sdk/actions/workflows/php.yml)

# Notice!
**Only products and shipments are currently supported. Other interfaces will follow!**

# Introduction
The otto market PHP SDK is an easy way for developers to access the otto market api in their php code, and build their programs on top of that.
A detailed documentation about all interfaces can be found at [our documentation][otto-market-api].

Jump to:
* [Requirements](#Requirements)
* [Installation](#Installation)
* [Use of the SDK](#Use-of-the-SDK)
* [Example CLI Client](#Example-CLI-Client)
* [Features](#Features)
* [Resources](#Resources)


## Requirements
1. **Sign up for Otto Market** – You need a working seller account. [Register here][otto-market-signup] and fetch your credentials.
2. **Minimum requirements** – To run the SDK, your system needs **PHP >= 7.4**.
   

## Installation
To use the SDK we recommend using [Composer]. It is available via [Packagist] under the [`otto/marketplace-php-sdk`][packagist-install].
You can include the SDK in your project using the following in the root directory of your project.:
```
composer require otto/marketplace-php-sdk
```

# Use of the SDK

### Set a new deliveryType and deliveryTime 

```php
<?php

use Otto\Market\Client\Configuration;
use Otto\Market\Products\Model\Delivery;
use Otto\Market\Client\PartnerApiClient;

// Configure the client
$configuration = Configuration::forLive('my-api-username', 'my-api-password');
$client = new PartnerApiClient($configuration);

// Update the delivery information for all products
$myProducts = $client->getPartnerProductClient()->getProducts();
foreach ($myProducts as $productVariation) {
    $delivery = new Delivery();
    $delivery->setType('PARCEL');
    $delivery->setDeliveryTime(2);
    
    $productVariation->setDelivery($delivery);
}

// Save the updated products
$client->getPartnerProductClient()->postProducts($myProducts);
```

## Example CLI Client
For an implemented example go to [samples](samples).

## Features

We offer the following methods for usage:

- [products][products-doc]
  * get brands
  * get categories
  * get category defintion from category
  * get products
  * post products from json
  * get activeStatus
  * post activeStatus
  * get marketplaceStatus
- [shipments][shipments-doc]
  * get shipments
  * post shipments
  
## Resources

* **License** – see [Apache 2.0 License](./LICENSE)




[otto-market-signup]: https://www.otto.market/
[otto-market-api]: https://api.otto.market/
[composer]: http://getcomposer.org
[packagist]: http://packagist.org
[packagist-install]: https://packagist.org/packages/otto/marketplace-php-sdk
[shipments-doc]: https://api.otto.market/docs/06_Shipments/v1/shipment-interface.html
[products-doc]: https://api.otto.market/docs/03_Products/v2/products-interface.html

