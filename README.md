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

The bundle uses [FOSUser](https://github.com/FriendsOfSymfony/FOSUserBundle).
Configuration of this bundle is also included on this document.

Installation is a quick 3 step process:

1. Download FulgurioSocialNetworkBundle
2. Configure the Autoloader
3. Enable the Bundle
4. Configure your application's security.yml
5. Configure the FOSUserBundle
6. Import FulgurioSocialNetworkBundle routing
7. Update your database schema

### Step 1: Download FulgurioSocialNetworkBundle

Ultimately, the FulgurioSocialNetworkBundle files should be downloaded to the
`vendor/bundles/Fulgurio/SocialNetworkBundle` directory.

This can be done in several ways, depending on your preference. The first
method is the standard Symfony2 method.

**Using the vendors script**

Add the following lines in your `deps` file:

``` ini
[FOSUserBundle]
    git=git://github.com/FriendsOfSymfony/FOSUserBundle.git
    target=bundles/FOS/UserBundle
    version=1.2.0

[Stof-DoctrineExtensionsBundle]
    git=http://github.com/stof/StofDoctrineExtensionsBundle.git
    version=origin/1.0.x
    target=/bundles/Stof/DoctrineExtensionsBundle

[gedmo-doctrine-extensions]
    git=http://github.com/Atlantic18/DoctrineExtensions.git
    version=origin/2.2.x

[knp-components]
    git=http://github.com/KnpLabs/knp-components.git
    version=v1.1

[KnpPaginatorBundle]
    git=http://github.com/KnpLabs/KnpPaginatorBundle.git
    target=bundles/Knp/Bundle/PaginatorBundle
    version=v2.2

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
$ git submodule add git://github.com/FriendsOfSymfony/FOSUserBundle.git vendor/bundles/FOS/UserBundle
$ git submodule add git://github.com/stof/StofDoctrineExtensionsBundle.git vendor/bundles/Stof/DoctrineExtensionsBundle
$ git submodule add git://github.com/Atlantic18/DoctrineExtensions.git vendor
$ git submodule add git://github.com/KnpLabs/knp-components.git vendor
$ git submodule add git://github.com/KnpLabs/KnpPaginatorBundle.git vendor/bundles/Knp/Bundle/PaginatorBundle
$ git submodule add git://github.com/Fulgurio/SocialNetworkBundle.git vendor/bundles/Fulgurio/SocialNetworkBundle
$ git submodule update --init
```

### Step 2: Configure the Autoloader

Add the namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'FOS' => __DIR__.'/../vendor/bundles',
    'Stof'             => __DIR__.'/../vendor/bundles',
    'Gedmo'            => __DIR__.'/../vendor/gedmo-doctrine-extensions/lib',
    'Knp\\Component'   => __DIR__.'/../vendor/knp-components/src',
    'Knp\\Bundle'      => __DIR__.'/../vendor/bundles',
    'Fulgurio' => __DIR__.'/../vendor/bundles',
));
```

### Step 3: Enable the bundles

Finally, enable the bundles in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\UserBundle\FOSUserBundle(),
        new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
        new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
        new Fulgurio\SocialNetworkBundle\FulgurioSocialNetworkBundle(),
    );
}
```
### Step 4: Configure your application's security.yml

In order for Symfony's security component to use the FOSUserBundle, you must
tell it to do so in the `security.yml` file. The `security.yml` file is where the
basic configuration for the security for your application is contained.

Below is a minimal example of the configuration necessary to use the FOSUserBundle
in your application:

``` yaml
# app/config/security.yml
security:
    providers:
        fos_userbundle:
            id: fos_user.user_manager

    encoders:
        FOS\UserBundle\Model\UserInterface: sha512

    firewalls:
        main:
            pattern: ^/
            form_login:
                provider:      fos_userbundle
                csrf_provider: form.csrf_provider
                remember_me:   true
            logout:       true
            anonymous:    true
            remember_me:
                key:      "%secret%"
                lifetime: 31536000 # 365 days, in seconds
                path:     /
                domain:   ~

    access_control:
        - { path: ^/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/register, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/resetting, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/, role: ROLE_ADMIN }

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: ROLE_ADMIN
```

Under the `providers` section, you are making the bundle's packaged user provider
service available via the alias `fos_userbundle`. The id of the bundle's user
provider service is `fos_user.user_manager`.

Next, take a look at examine the `firewalls` section. Here we have declared a
firewall named `main`. By specifying `form_login`, you have told the Symfony2
framework that any time a request is made to this firewall that leads to the
user needing to authenticate himself, the user will be redirected to a form
where he will be able to enter his credentials. It should come as no surprise
then that you have specified the user provider we declared earlier as the
provider for the firewall to use as part of the authentication process.

### Step 5: Configure the FOSUserBundle

Now that you have properly configured your application's `security.yml` to work
with the FOSUserBundle, the next step is to configure the bundle to work with
the specific needs of your application.

Add the following configuration to your `config.yml` file according to which type
of datastore you are using.

``` yaml
# app/config/config.yml
fos_user:
    db_driver:     orm
    firewall_name: main
    user_class:    Fulgurio\SocialNetworkBundle\Entity\User
    registration:
        form:
            type:  fulgurio_social_network_registration
    resetting:
        form:
            type:  fulgurio_social_network_resetting

stof_doctrine_extensions:
    orm:
        default:
            timestampable: true
            sluggable: true
```

### Step 6: Import FulgurioSocialNetworkBundle routing

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

### Step 7: Update your database schema

Now that the bundle is configured, the last thing you need to do is update your
database schema because you have added a new entity, the `User` class which you
created in Step 4.

For ORM run the following command.

``` bash
$ php app/console doctrine:schema:update --force
```

Now that you have completed the basic installation and configuration of the
FulgurioSocialNetworkBundle, you are ready to learn about more advanced
features and usages of the bundle.
