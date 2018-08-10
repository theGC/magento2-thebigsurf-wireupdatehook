# TheBigSurf - WireUpdateHook

Listen for product and attribute events and pass them to a configured API endpoint

### To Install via Composer:

```
$ composer require thebigsurf/wire-update-hook
```

### To Install Manually:

paste the folder into:

* [magentoDir]/app/code/TheBigSurf/WireUpdateHook

> run

```
$ bin/magento module:enable TheBigSurf_WireUpdateHook
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile
```