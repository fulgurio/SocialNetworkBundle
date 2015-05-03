Unit test for FulgurioSocialNetworkBundle
================================================

You need Liip/LiipFunctionalTestBundle installed.

Add the following lines in `composer.json` :

``` ini
    "require-dev": {
        "doctrine/doctrine-fixtures-bundle": "2.2.*",
        "liip/functional-test-bundle": "^1.2"
    },
```

Call composer to get the bundle

``` bash
$ ./composer update
```

Finally, enable the bundles in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    // ...
        if (in_array($this->getEnvironment(), array('dev', 'test')))
        {
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
        }
}
```

To test, you need to turn off translator service

``` yaml
# app/config/config_test.yml
framework:
    translator:
        enabled: false
```
