# magento2-cron-runner

Originally developed by [&copy;Creatuity](https://creatuity.com/) as [creatuity/magento2-cron-runner](https://github.com/creatuity/magento2-cron-runner)

Alternative cron runner for magento that aims for simplicity and handling large scale.

## Installation
  
```
composer require fsw2/magento2-cron-runner
php bin/magento module:enable Fsw_CronRunner
php bin/magento setup:upgrade
```

## Features
  
### Replaced cron scheduler

This module introduces another way of scheduling and executing cron jobs. Instead of runing a complex scheduling program and plan cron execution in advance, it creates a single table for all cron jobs in witch last execution time of job is stored among other values. Using last execution time and cron expression module checks if given job should be executed. Module uses a plugin to hook into `bin/magento cron:run` so you dont need to change anything in server configuration after installing this module.

### Admin Panel


### CLI
