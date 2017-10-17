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
    NOTE: This framework is in ALPHA stage - Not ready for production
```

# Table of Contents
<!-- https://github.com/naokazuterada/MarkdownTOC -->

<!-- MarkdownTOC list_bullets="*" bracket="round" lowercase="true" autolink="true" indent="" -->

* [1. Features](#1-features)
* [1.1. PSR based integrations](#11-psr-based-integrations)
* [2. Installation](#2-installation)
* [2.1. Using the spin-skeleton](#21-using-the-spin-skeleton)
* [4. Technical Details](#4-technical-details)
* [3. Folder structure](#3-folder-structure)

<!-- /MarkdownTOC -->

# 1. Features
* PHP 7.1+
* Platform agnostic. (Tested: Windows, Linux, Unix)
* Composer driven in all packages/extensions
* Template Engine support (skeleton uses [Plates](http://platesphp.com/) by default)
* PDO based DB connections (MySql,Oracle,CockroachDb,Firebird,Sqlite ...)
* DAO base classes for DB Entity representation

## 1.1. PSR based integrations
* Logger (PSR-3) Defaults to [Monolog](https://github.com/Seldaek/monolog).
* Huggable (PSR-8)
* HTTP Message (PSR-7). Defaults to [Guzzle](https://github.com/guzzle/guzzle)
* Container (PSR-11). Defaults to [The Leauge Container](http://container.thephpleague.com/)
* Events (PSR-14).
* SimpleCache (PSR-16). Defaults to APCu SimpleCache
* HTTP Factories (PSR-17)

# 2. Installation
Installing spin-framework as standalone with composer:
```bash
> composer require celarius/spin-framework
```

## 2.1. Using the spin-skeleton
To install and use the spin-framework it is highly recommended to start by cloning the [spin-skeleton](https://github.com/Celarius/spin-skeleton) and running `composer update -o` in the folder. This will download all needed packages, and create a template skeleton project, containing example configs, routes, controllers and many other things.

# 4. Technical Details
* [Helpers](doc/helpers.md)
* [Request lifecycle](doc/request_lifecycle.md)
* [Template Engines](doc/template_engines.md)

# 3. Folder structure
```txt
/<AppName>
  /src
    /app
      /Config
      /Middlewares
      /Controllers
      /Views
        /Templates
        /Errors
        /Pages
      /Models
      globals.php
    /public
    /storage
      /logs
      /cache
    /vendor
      /celarius/spin-framework
    composer.json
  /tests
```



