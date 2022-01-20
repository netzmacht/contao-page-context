Contao Page Context
===================

[![Build Status](https://github.com/netzmacht/contao-page-context/actions/workflows/diagnostics.yml/badge.svg)](https://github.com/netzmacht/contao-page-context/actions/workflows/diagnostics.yml)
[![Version](http://img.shields.io/packagist/v/netzmacht/contao-page-context.svg?style=flat-square)](http://packagist.org/packages/netzmacht/contao-page-context)
[![License](http://img.shields.io/packagist/l/netzmacht/contao-page-context.svg?style=flat-square)](http://packagist.org/packages/netzmacht/contao-page-context)
[![Downloads](http://img.shields.io/packagist/dt/netzmacht/contao-page-context.svg?style=flat-square)](http://packagist.org/packages/netzmacht/contao-page-context)
[![Contao Community Alliance coding standard](http://img.shields.io/badge/cca-coding_standard-red.svg?style=flat-square)](https://github.com/contao-community-alliance/coding-standard)

This Contao extension allows developer manually boot the page context of Contao when using custom routes.

Parts of the Contao CMS relies on the existing of global state initialized when rendering a page. Modules, content 
elements, insert tags and custom extensions might rely that this state is initialized. 

For instance Contao developers access the `$GLOBALS['objPage']` as there is no other way to get the current page.

However, when using a custom entrypoint, for example for an API, you don't have this state initialized. Using the Contao
library and functionality might end in unexpected errors. 

This is the point there this extension steps in. It allows you to boot the page context manually for your route.

In detail it, initialize following steps:

 - The Contao framework
 - Constant `BE_USER_LOGGED_IN` and `FE_USER_LOGGED_IN` to `false` if not defined.
 - Loading the page from the database
 - Globals `objPage`, `TL_ADMIN_NAME`, `TL_ADMIN_EMAIL`, `TL_KEYWORDS`, `TL_LANGUAGE`
 - Initialize the locale of the request and the translator
 - Loads the default language file
 - Calls `Controller::initializeStaticUrls()`
 - Initializes the page layout (triggers `getPageLayout` hook)  

Requirements
------------

 - Contao 4.4
 - PHP >= 7.1
 
Installation
------------

 ```
 php composer.phar require netzmacht/contao-page-context --update-no-dev -o 
 ```

Usage
-----

### 1. Implement a PageIdDeterminator and register it properly

First you have to provide a PageIdDeterminator. It's responsible to extract the page id from the given request.

You should limit the determinator to your special use case, that's why there is the `match()` method.

```php

<?php

declare(strict_types=1);

namespace My\Bundle;

use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use Netzmacht\Contao\PageContext\Exception\DeterminePageIdFailed;
use Symfony\Component\HttpFoundation\Request;

final class MyPageIdDeterminator implements PageIdDeterminator
{
    public function match(Request $request): bool
    {
        return ($request->attributes->get('_my_context') === 'page');
    }

    public function determinate(Request $request): int
    {
        if (!$request->attributes->has('pageId')) {
            throw new DeterminePageIdFailed('Could not determine page id for from request.');
        }

        return $request->attributes->getInt('pageId');
    }
}

```

Now you have to register it as a service and tag it as `Netzmacht\Contao\PageContext\Request\PageIdDeterminator`.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services
        http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <service id="My\Bundle\MyPageIdDeterminator">
            <tag name="Netzmacht\Contao\PageContext\Request\PageIdDeterminator" />
        </service>
    </services>
</container>
```

### 2. Prepare your route

As already seen in the example above, a custom route attribute is accessed, here `_my_context`. You should define it in 
your route configuration:

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<routes xmlns="http://symfony.com/schema/routing"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/routing
        http://symfony.com/schema/routing/routing-1.0.xsd">

    <route id="my_route" controller="My\Bundle\Action\ApiAction" path="/my/api/{pageId}" >
        <default key="_my_context">page</default>
        <default key="_scope">frontend</default>
        <requirement key="pageId">\d+</requirement>
    </route>
</routes>
```

### 3. Accessing the page object

That's it. If you try to access `$GLOBALS['objPage']` you should have the page object. Good news, you can avoid 
accessing the global state. Your current Request have a new attribute, called `_page_context`.

```php
<?php

declare(strict_types=1);

namespace My\Bundle\Action;

use Netzmacht\Contao\PageContext\Request\PageContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiAction
{
    public function __invoke(Request $request): Response
    {
        /** @var PageContext $context */
        $context = $request->attributes->get('_page_context');

        return new JsonResponse(
            [
                'pageId' => $context->page()->id,
                'rootId' => $context->rootPage()->id,
            ]
        );
    }
}
```
