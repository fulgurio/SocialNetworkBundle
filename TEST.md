Unit test for FulgurioSocialNetworkBundle
================================================

You need Liip/LiipFunctionalTestBundle installed.

Add the following lines in your `deps` file:

``` ini
[liip/functional-test]
    git=http://github.com/liip/LiipFunctionalTestBundle.git
    target=/bundles/Liip/FunctionalTestBundle
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

Add the namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Liip'             => __DIR__.'/../vendor/bundles',
));
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
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
        }
}
```
