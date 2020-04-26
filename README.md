# Fluxx Magento 2
![Fluxx](view/adminhtml/web/images/logo.svg)

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/9c8e5a8ae6354821bf2f990a4d0ff397)](https://www.codacy.com/manual/DevMagentoFluxx/magento2?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=DevMagentoFluxx/magento2&amp;utm_campaign=Badge_Grade) ![StyleCI](https://github.styleci.io/repos/257997111/shield?branch=master)

## Extension features

*   Installment payment slip for your client
*   Payment method without redirection
*   Automatic status return

## Installation

We recommend installing by [composer](README.md#via-composer), but you can also do it [manually](README.md#manual).after installation it is necessary to [enable](README.md#enable) the module.

### Composer (recommend)

``` sh
composer require fluxxbrasil/magento2
```

### Manually

*   [Download](https://github.com/DevMagentoFluxx/magento2/archive/master.zip)
*   On your computer unzip the file
*   Navigate to the [root directory](https://devdocs.magento.com/guides/v2.3/install-gde/basics/basics_docroot.html) of the Magento 2
*   On your server create folder Fluxx in public_html/app/code/
*   Send folder Magento2 to public_html/app/code/Fluxx

#### Enable

``` sh
php bin/magento module:enable Fluxx_Magento2
bin/magento setup:upgrade --keep-generated 
```

## Configuration

After installation follow the steps in the **order** presented:

Attribute Relationship Definition:

In STORES -> Configuration -> Payment Method -> Fluxx -> Attribute Relationship Definition

*   The CPF will be an attribute obtained from the
*   The CPF attribute is
*   The Street attribute is
*   The address number is
*   The address district is
*   The address complement is

In STORES -> Configuration -> Payment Method -> Fluxx -> Credentials

*   Environment
*   Merchant Gateway Username
*   Merchant Gateway Key

## Technical feature

### Module configuration
*   Package details [composer.json](composer.json).
*   Module configuration details (sequence) in [module.xml](etc/module.xml).
*   Module configuration available through Stores->Configuration [system.xml](etc/adminhtml/system.xml)

Payment gateway module depends on `Sales`, `Payment` and `Checkout` Magento modules.
For more module configuration details, please look through [module development docs](http://devdocs.magento.com/guides/v2.0/extension-dev-guide/module-load-order.html).

### Dependency Injection configuration
> To get more details about dependency injection configuration in Magento 2, please see [DI docs](http://devdocs.magento.com/guides/v2.0/extension-dev-guide/depend-inj.html).

In a case of Payment Gateway, DI configuration is used to define pools of `Gateway Commands` with related infrastructure and to configure `Payment Method Facade` (used by `Sales` and `Checkout` modules to perform commands)

## License
[Open Source License](LICENSE.txt)

