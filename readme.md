[![Latest Stable Version](https://poser.pugx.org/celarius/spin-framework/v/stable)](https://packagist.org/packages/celarius/spin-framework)
[![Total Downloads](https://poser.pugx.org/celarius/spin-framework/downloads)](https://packagist.org/packages/celarius/spin-framework)
[![License](https://poser.pugx.org/nofuzz/framework/license)](https://packagist.org/packages/celarius/spin-framework)
[![PHP7 Ready](https://img.shields.io/badge/PHP7-ready-green.svg)](https://packagist.org/packages/celarius/spin-framework)

# Spin - A super lightweight PHP UI/REST framework
[![Latest Unstable Version](https://poser.pugx.org/celarius/spin-framework/v/unstable)](https://packagist.org/packages/celarius/spin-framework)
[![Build Status](https://travis-ci.org/Celarius/spin-framework.svg?branch=master)](https://travis-ci.org/Celarius/spin-framework)

Spin is a application framework for making Web UI's and REST API's quickly and effectively with PHP. It uses [PSR standards](http://www.php-fig.org/psr/)
for most things, and allows for plugging in almost any PSR compatible component, such as loggers, HTTP libraries etc.

```txt
    NOTE: This framework is in RC stage - Please contribute to make it complete
```

<!-- https://github.com/naokazuterada/MarkdownTOC -->

<!-- MarkdownTOC list_bullets="-" bracket="round" lowercase="true" autolink="true" indent= -->

- [1. Features](#1-features)
- [1.1. PSR based integrations](#11-psr-based-integrations)
- [2. Installation](#2-installation)
- [2.1. Using the spin-skeleton](#21-using-the-spin-skeleton)
- [2.2. Testing](#22-testing)
- [3. Technical Details](#3-technical-details)
- [3.1. Apache VHost configuration](#31-apache-vhost-configuration)

<!-- /MarkdownTOC -->

# 1. Features
* PHP 7.1+
* Platform agnostic. (Windows, \*nix)
* Routing engine, with route groups
* Middleware
* Containers
* Composer driven in packages/extensions
* PDO based DB connections (MySql,Oracle,CockroachDb,Firebird,Sqlite ...)
* DAO base classes for DB Entity representation
* Extendable with other frameworks (ORM, Templates etc.)


## 1.1. PSR based integrations
* Logger (PSR-3) Defaults to [Monolog](https://github.com/Seldaek/monolog)
* HTTP Message (PSR-7). Defaults to [Guzzle](https://github.com/guzzle/guzzle)
* Container (PSR-11). Defaults to [The Leauge Container](http://container.thephpleague.com/)
* SimpleCache (PSR-16). Defaults to APCu SimpleCache
* HTTP Factories (PSR-17)


# 2. Installation
Installing spin-framework as standalone with composer:
```bash
composer require celarius/spin-framework
```

## 2.1. Using the spin-skeleton
To install and use the spin-framework it is highly recommended to start by cloning the [spin-skeleton](https://github.com/Celarius/spin-skeleton) and
running `composer update -o` in the folder. This will download all needed packages, and create a template skeleton project, containing example 
configs, routes, controllers and many other things.

## 2.2. Testing
Having PHPUnit installed simply type
```txt
phpunit
```
At the command prompt and all tests will be executed.

# 3. Technical Details
* [Helpers](doc/helpers.md)

## 3.1. Apache VHost configuration
```txt
<VirtualHost *:80>

    Define domain.name              mydomain.com
    Define alias.domain.name        www.mydomain.com
    Define path_to_root             C:/Path/Project
    Define environment              DEV


    ServerName ${domain.name}
    ServerAlias ${alias.domain.name}
    ServerAdmin webmaster@${domain.name}

    DocumentRoot "${path_to_root}\src\public"

    ErrorLog "logs/${domain.name}.error.log"
    CustomLog "logs/${domain.name}.access.log" common

    # Default caching headers for static content in /public
    <FilesMatch "\.(ico|pdf|flv|jpg|jpeg|png|gif|js|css|swf)$">
      Header set Cache-Control "public, max-age=604800, must-revalidate"
    </FilesMatch>

    <Directory "${path_to_root}\src\public">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Order allow,deny
        Allow from all
        Require all granted

        # Set Variables
        SetEnv ENVIRONMENT ${environment}

        # Load files in this order on "/"
        DirectoryIndex bootstrap.php index.php index.html

        # Disable appending a "/" and 301 redirection when a directory
        # matches the requested URL
        DirectorySlash Off

        # Set Rewrite Engine ON to direct all requests to
        # the `bootstrap.php` file
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ bootstrap.php [QSA,L]
    </Directory>
</VirtualHost>
```
