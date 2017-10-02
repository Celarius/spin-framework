# Spin - A PHP UI/REST framework

Spin is a application framework for making Web UI's and REST API's quickly and effectively with PHP. It uses [PSR standards](http://www.php-fig.org/psr/)
for most things, and allows for plugging in almost any PSR compatible component, such as loggers, HTTP libraries etc.

# Features
* PHP 7.1+
* Composer driven in all components
* Controller->Model->View support (template engines, [Plates](http://platesphp.com/) by default)
* PDO based DB connections
* DAO classes for DB Entity representation

## PSR based integrations
* Logger (PSR-3) Defaults to Monolog.
* Huggable (PSR-8)
* HTTP Message (PSR-7). Defaults to Guzzle
* Container (PSR-11). Defaults to The Leauge Container
* Events (PSR-14).
* SimpleCache (PSR-16). Defaults to APCu SimpleCache (in memory)
* HTTP Factories (PSR-17)

