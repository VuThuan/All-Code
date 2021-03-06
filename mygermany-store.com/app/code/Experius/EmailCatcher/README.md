# Magento 2 Module Experius email catcher / - logger

    ``experius/module-emailcatcher``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Versions](#markdown-header-versions)
 - [Enable email catcher](#markdown-header-enable-email-catcher)
 - [Change log](#markdown-header-change-log)

## Main Functionalities
 - Log all Emails send by Magento
 - View email contecnt in popup (Nice for testing and styling)
 - Forward  a catched email
 - Resend  a catched email
 - Cleanup of emails older then 30 days (cron or manual)

## Versions
- Version 3.0.0 or higher is fully compatible with 2.2.x and 2.3.x

[**Please note that from 3.0.0 onward the composer require changed to experius/module-emailcatcher**]

- Version 2.0.0 or higher is compatible with Magento 2.2 or higher
- Version 1.3.2 or higher is compatible with Magento 2.1.8 or higher
- Version lower then 1.3.2 could still be used for Magento 2.1.7 or lower but we recommend to install a newer version

## Installation
In production please use the `--keep-generated` option

### Type 1: Zip file
 - Unzip the zip file in `app/code/Experius`
 - Enable the module by running `php bin/magento module:enable Experius_EmailCatcher`
 - Apply database updates by running `php bin/magento setup:upgrade`
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer
 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require experius/module-emailcatcher`
 - Enable the module by running `php bin/magento module:enable Experius_EmailCatcher`
 - Apply database updates by running `php bin/magento setup:upgrade`
 - Flush the cache by running `php bin/magento cache:flush`

## Enable email catcher
Enable Email Catcher.
 - Stores > Settings > Configuration > Advanced > Email Catcher > General > Enable Email Catcher (emailcatcher/general/enabled)

Disable email sending (default Magento, advised for development)
 - Stores > Settings > Configuration > Advanced > System > Mail Sending Settings > Disable Email Communications (system/smpt/disable)

Admin grid
 - System > Tools > Email Catcher

## Change log
Version 3.0.0 - October 9th, 2019

 - [REFACTOR] DocBlocks and module composer require namespace changed
 - [FEATURE] Magento 2.3.3 support for forwarding and resending emails using the newly introduced \Magento\Framework\Mail\EmailMessageInterface. Fully backwards compatible to 2.2.x
 - [DOCS] Updated README, starting change logs

