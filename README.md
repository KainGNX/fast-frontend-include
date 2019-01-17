# Fast Frontend Include
A general purpose context class that is used to easily include Javascript and CSS

The constructor will take the parameters for the context that the page is on, as well as the relative path for global includes. The global includes will happen first.

#### Usage
First create an array with `js` and `css` keys each containing a list of relative paths from your webroot, in the order you wish to include them. 

```php
    $globalIncludes = [
    'js' => [
        'js/global1.js',
        'js/global2.js'
    ],
    'css' => [
        'css/global1.css',
        'css/global2.css'
    ]
];
```

Then instantiate a `Context` class with first parameter being the `Context::contextKey` and second being the global includes. 
```php
    $viewContext = new \FastFrontend\View\Context('index', $globalIncludes);
```

### Usage in a template
In the example below `Context::js()` and `Context::css()` are used for tag output to their respective naming. First global includes, then context specific includes.

- For JS : `js/index/` in the example will be scanned for its contents and included in the order they are found
- For CSS : `css/index/` in the example will be scanned for its contents and included in the order they are found

Add the following to the `<head>` of your HTML.
```php
    echo $viewContext->js();
    echo $viewContext->css();
```

## Adding or removing includes
- To add or remove global files, change array passed into the `Context` object as needed.
- To add or remove context specific includes, simply add a file remove a file, no code change needed.

## Additional Info
If you wanted to include files specific to an about page, it would like like
```php
    $viewContext = new \FastFrontend\View\Context('about', $globalIncludes);
```
This would then look to include files in `js/about` and `css/about` if files are added. Context directories are only intended to contain files, no recursion is done to find subdirectories will js/css.