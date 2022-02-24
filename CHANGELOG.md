# CHANGELOG

## 0.2.2 - 2022-02-24

* fixed issue ([#3][i3]) </br>
  The getter method for categories now returns an iterator that provides the category data by loading it page by page 
  to avoid memory problems due to the number of categories available

[i3]: https://github.com/otto-de/marketplace-php-sdk/issues/3

## 0.2.1 - 2021-12-16

* [feature] Add support for sandbox test environment ([#9][i9])

[i9]: https://github.com/otto-de/marketplace-php-sdk/issues/8

## 0.2.0 - 2021-07-07

* Update `products` to api `v2`

## 0.1.0 - 2021-03-21

* Initial release of the OTTO marketplace SDK for PHP.
* Added support for **Products**
* Added support for **Shipments**