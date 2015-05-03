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

For more information about translations, check [Symfony documentation](http://symfony.com/doc/2.3/book/translation.html).

## Installation

The bundle uses [FOSUser](https://github.com/FriendsOfSymfony/FOSUserBundle).
Configuration of this bundle is also included on this document.

Installation is a quick 6 steps process:

1. Download FulgurioSocialNetworkBundle using composer
2. Enable the Bundle
3. Configure your application's security.yml
4. Configure the FOSUserBundle
5. Configure the bundle
6. Import FulgurioSocialNetworkBundle routing
7. Update your database schema

### Step 1: Download FulgurioSocialNetworkBundle using composer

First, edit composer.json, and add the bundle

``` json
{
    "require": {
        "fulgurio/socialnetwork-bundle" : "dev-master"
    }
}
```

and launch composer, it will load all dependencies.

``` bash
$ composer update
```

### Step 2: Enable the bundles

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
            new Fulgurio\SocialNetworkBundle\FulgurioSocialNetworkBundle()
        );
    }
```
### Step 3: Configure your application's security.yml

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

### Step 4: Configure the FOSUserBundle

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
    profile:
        form:
            type:  fulgurio_social_network_profile

stof_doctrine_extensions:
    orm:
        default:
            timestampable: true
            sluggable: true
```

### Step 5: Configure the bundle
Now configure the bundle, just set contact email (it's the "From:" email of
bundle sent email)

``` yaml
# app/config/config.yml
fulgurio_social_network:
    admin_email:
        contact:
            from: contact@example.com
        remove_avatar:
            from: contact@example.com
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

### Step 7: Update your database schema

Now that the bundle is configured, the last thing you need to do is update your
database schema because you have added a new entity, the `User` class which you
created in Step 3.

For ORM run the following command.

``` bash
$ php app/console doctrine:schema:update --force
```

Now that you have completed the basic installation and configuration of the
FulgurioSocialNetworkBundle, you are ready to learn about more advanced
features and usages of the bundle.
