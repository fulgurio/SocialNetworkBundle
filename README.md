Getting Started With FulgurioSocialNetworkBundle
================================================

The SocialNetworkBundle adds support to make a social network on your symfony 2
project.

## Prerequisites

### Translations

If you wish to use default texts provided in this bundle, you have to make
sure you have translator enabled in your config.

``` yaml
# app/config/config.yml

framework:
    translator: ~
```

For more information about translations, check [Symfony documentation](http://symfony.com/doc/2.0/book/translation.html).

## Installation

Installation is a quick 3 step process:

1. Download FulgurioSocialNetworkBundle
2. Configure the Autoloader
3. Enable the Bundle
4. Import FulgurioSocialNetworkBundle routing

### Step 1: Download FulgurioSocialNetworkBundle

Ultimately, the FulgurioSocialNetworkBundle files should be downloaded to the
`vendor/bundles/Fulgurio/SocialNetworkBundle` directory.

This can be done in several ways, depending on your preference. The first
method is the standard Symfony2 method.

**Using the vendors script**

Add the following lines in your `deps` file:

``` ini
[FulgurioSocialNetworkBundle]
    git=git://github.com/Fulgurio/SocialNetworkBundle.git
    target=bundles/Fulgurio/SocialNetworkBundle
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

**Using submodules**

If you prefer instead to use git submodules, then run the following:

``` bash
$ git submodule add git://github.com/Fulgurio/SocialNetworkBundle.git vendor/bundles/Fulgurio/SocialNetworkBundle
$ git submodule update --init
```

### Step 2: Configure the Autoloader

Add the `Fulgurio` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Fulgurio' => __DIR__.'/../vendor/bundles',
));
```

### Step 3: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Fulgurio\SocialNetworkBundle\FulgurioSocialNetworkBundle(),
    );
}
```

### Step 4: Import FulgurioSocialNetworkBundle routing

Now that you have activated and configured the bundle, all that is left to do is
import the FulgurioSocialNetworkBundle routing files.

By importing the routing files you will have ready made pages for things such as
logging in, creating users, etc.

In YAML:

``` yaml
# app/config/routing.yml
fos_user_security:
    resource: "@FulgurioSocialNetworkBundle/Resources/config/routing.yml"
```

Or if you prefer XML:

``` xml
<!-- app/config/routing.xml -->
<import resource="@FulgurioSocialNetworkBundle/Resources/config/routing.yml"/>
```

Now that you have completed the basic installation and configuration of the
FulgurioSocialNetworkBundle, you are ready to learn about more advanced
features and usages of the bundle.
