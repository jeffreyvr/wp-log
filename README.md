<p align="center"><a href="https://vanrossum.dev" target="_blank"><img src="https://raw.githubusercontent.com/jeffreyvr/vanrossum.dev-art/main/logo.svg" width="320" alt="vanrossum.dev Logo"></a></p>

<p align="center">
<a href="https://packagist.org/packages/jeffreyvanrossum/wp-log"><img src="https://img.shields.io/packagist/dt/jeffreyvanrossum/wp-log" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/jeffreyvanrossum/wp-log"><img src="https://img.shields.io/packagist/v/jeffreyvanrossum/wp-log" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/jeffreyvanrossum/wp-log"><img src="https://img.shields.io/packagist/l/jeffreyvanrossum/wp-log" alt="License"></a>
</p>

# WP Log

A simple package to write entries to a log file.

## Installation

```bash
composer require jeffreyvanrossum/wp-log
```

## Usage

You can setup your log with:

```php
$log = new \Jeffreyvr\WPLog\Log('Your log');
```

You can define a custom file path, if you don't, the default is the `wp-content/uploads/` folder with the file name being a sanitized version of your log name.

```php
$log->setFilePath(wp_upload_dir()['basedir'] . '/logs/your-log-filename.log');
```

Writing to your log can be done like so:

```php
$log->write('Your log message');

$log->write(['foo' => 'bar']);
```

You may clear your log with:

```php
$log->clear();
```

The `interface` method will render an interface, which you can use to display the log somewhere in the admin area.

If you want to display the log page in the admin menu, you can call:

```php
$log->interface()->inAdminMenu(slug: 'optional-slug', parent: 'tools.php');
```

To set a custom capability use:

```php
$log->interface->setCapability('manage_options');
```

Or if you want to add it as a plugin link instead:

```php
$log->interface()->asPluginLink(basename: plugin_basename(__FILE__), slug: 'optional-slug');
```

You can instead also call `$log->interface()->render()` to render it somewhere you want.

To prevent your log from becoming very large, the default limit is set to 1000 items. You can overwrite this:

```php
$log->setClearLimit(100);

// If you don't want to limit your log, you can pass 0.
$log->setClearLimit(0);
```

## Contributors
* [Jeffrey van Rossum](https://github.com/jeffreyvr)
* [All contributors](https://github.com/jeffreyvr/wp-log/graphs/contributors)

## License
MIT. Please see the [License File](/LICENSE) for more information.
