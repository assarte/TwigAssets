TwigAssets
==========

Implements an on-the-fly asset manager for Twig

How it works
------------

You should change Twig's `base_template_class` option value to: `'Assarte_TwigAssets_Template'`. This is highly recommended.
```php
$loader = new Twig_Loader_Filesystem('path/to/templates');
$env = new Twig_Environment($loader, array(
	'cache'					=> 'path/to/compiled/sources',
	'base_template_class'	=> 'Assarte_TwigAssets_Template'
));
```

You must add the `Assarte_TwigAssets_Extension_Assets` extension to Twig.
```php
$env->addExtension(
	new Assarte_TwigAssets_Extension_Assets(array(
		'namespace'			=> Assarte_TwigAssets_Storage_Filesystem::STORE_NAMESPACE,
		'storage'			=> new Assarte_TwigAssets_Storage_Filesystem(
			$loader,
			'path/to/public/assets/'
		)
	))
);
```
That's all in your PHP code!

How to use
----------

You've got two new *Tags* for Twig:
 * `{% asset 'path/to/asset.file' bind 'collection-name' %}`: This indicates that a template requires an asset. You can use and reuse many assets as you want and where you want. You can bind any assets to any collections. You can name any collections as you want. All assets in an exact collection will be unique even if you require more than once.
 * `{% asset_build 'collection-name' as 'css|js' [no_minify] %}`: This indicates a place where a collection of assets needs to be used. Here you must specify the type of the specific collection (`'js'` and `'css'` supported by default). You can control the minifing of assets with the optional `no_minify` switch. For example:

		<link type="text/css" rel="stylesheet" media="all" href="path/to/public/assets/{% asset_build 'default' as 'css' %}">

How to minify
-------------

You should use the Extension's `minify` option to indicate if you want to minify your asset-collections (`true`) or not (`false`). If you indicates that you want to use minifing you must setup a minifier-callback by the `minifier_callback` option. The minifier callback must have two arguments: `string $content, string $type`. This callback must returns the minified version of the passed `$content`'s content.
An example of usage:
```php
$env->addExtension(
	new \Assarte_TwigAssets_Extension_Assets(array(
		...,
		'minifing'			=> true,
		'minifier_callback'	=> function($content, $type) {
			switch ($type) {
				default: {
					throw new RuntimeException('Invalid asset minifier type: '.$type);
					break;
				}
				case 'js': {
					$result = JSMinPlus::minify($content);
					return $result;
				}
				case 'css': {
					$minifier = new CSSmin();
					$result = $minifier->run($content);
					return $result;
				}
			}
		}
	))
);
```
It's simple as hell. Anyway, you can grab these libs with ease:
 * JSMinPlus: https://github.com/mrclay/minify/blob/master/min/lib/JSMinPlus.php
 * CSSmin: https://github.com/mrclay/minify/blob/master/min/lib/CSSmin.php
