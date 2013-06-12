# SpiffyRoutes Module

SpiffyRoutes is a module intended to make setting up routes quicker by providing annotations that can be
used directly on the controllers/actions themselves. SpiffyRoutes comes feature complete with caching and a
CLI tool to warm/clear the cache as required.

## Installation

Installation of SpiffyRoutes uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

```sh
php composer.phar require spiffy/spiffy-routes:dev-master
```

Then add `SpiffyRoutes` to your `config/application.config.php`

Installation without composer is not officially supported, and requires you to install and autoload
the dependencies specified in the `composer.json`.

## Supported Annotations

Below is a list of currently supported annotations. This list will be updated as more annotations are supported.

### Root

The root annotation is used on the *controller* level and specifies the prefix to apply to all routes
on actions inside the controller.

```php
<?php

use SpiffyRoutes\Annotation as Route;

/**
 * @Route\Root("/my")
 */
class MyController
{
    /**
     * @Route\Literal("/home")
     */
    public function indexAction()
    {
        // ... I resolve to /my/home
    }
}
```

### Literal

The literal annotation maps to the literal route type.


```php
<?php

use SpiffyRoutes\Annotation as Route;

class MyController
{
    /**
     * @Route\Literal("/home", name="index")
     */
    public function indexAction()
    {
        // ... I resolve to "/home", with name "index"
    }
}
```

## Caching

Caching is extremely important due to the amount of reflection required to parse the annotations. It's so important,
in fact, that caching is *not* optional. You can, however, set the cache adapter to `Zend\Cache\Storage\Adapter\Memory`
during development if you wish to rebuild the router configuration on every request.

By default, caching is enabled using the `SpiffyRoutes\Cache` service which is a `Zend\Cache\Storage\AdapterFilesystem`
with the cache path set to `data/spiffy-routes`.

## CLI Tool

A CLI tool is provided to build and clear the cache. Run your `public/index.php` from a console to see the relevent
information.

## Automatic Route Names

It's recommended that you specify a name for all routes e.g., `@Route\Literal("/", name="home")`. Failure to do so will
cause an automated route name to be generated based on a canonicalized version of the controller and action name.

For example, if you have a controller registered with the ControllerManager as `My\Controller` and are a adding a route
to the `indexAction` the auto-generated route name would be `my_controller_index`.