TwigAssets
==========

Implements an on-the-fly asset manager for Twig

This is a part of a light-weight framework, Prometheus: http://webapper.vallalatiszolgaltatasok.hu/#!/prometheus
(language only in hungarian, sorry)

<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by/4.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">TwigAssets</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://webapper.vallalatiszolgaltatasok.hu/#!/prometheus" property="cc:attributionName" rel="cc:attributionURL">Assarte D'Raven</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International License</a>.

How you help me
---------------

Feel free to use my extension, I hope that you may enjoy that and may help you on your better efficiency. Well, you should donate me some credits via PayPal if my help counts for you on your work:

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5KQ66J5DF97RA">
<img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
</a>

...or give me some positive feedback on my e-mail adress (you can see that in my profile).

Thanks anyway!

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
		),
		'name_generator_cb'	=> 'md5' // You may use any type of callbacks
	))
);
```
That's all in your PHP code!

How to use
----------

You've got four new *Tags* and one *Function* for Twig:
 * `{{ asset_empty('collection-name') }}`: This function checks if an asset collection is empty/unexistant or not. You should use it in conditional places if you not want to include noise-like empty CSS or JS files on your HTML code.
 * `{% asset 'path/to/asset.file' bind 'collection-name' %}`: This indicates that a template requires an asset. You can use and reuse many assets as you want and where you want. You can bind any assets to any collections. You can name any collections as you want. All assets in an exact collection will be unique even if you require more than once.
 * `{% build 'collection-name' as 'css|js' [no_minify] %}...{% endbuild %}`: **The new way of building a collection!** A `build` block's contents displayed only if collection has some - one at least - asset. Use the new `use_asset` tag within to display the filename of builded asset collection. This way is more efficent if you want optionally include a collection of assets based on that if it has assets or not. You can place a `build` block before the adding of any assets to the `build`ed collection (you cannot do this with the `if not asset_empty('collection-name')`-way)!
 * `{% use_asset 'collection-name' %}`: Displays an asset collection's filename within a `build` block. For example:

		{% build 'default-css' as 'css' %}
			<link type="text/css" rel="stylesheet" media="all" href="path/to/public/assets/{% use_asset 'default-css' %}">
		{% endbuild %}

 * `{% asset_build 'collection-name' as 'css|js' [no_minify] %}`: **This is the old way of building and placeing a collection.** You should use this if you sure about that the collection always contains one or more assets. This tag  indicates a place where a collection of assets needs to be used. Here you must specify the type of the specific collection (`'js'` and `'css'` supported by default). You can control the minifing of assets with the optional `no_minify` switch. For example:

		<link type="text/css" rel="stylesheet" media="all" href="path/to/public/assets/{% asset_build 'default-css' as 'css' %}">

How to minify
-------------

You should use the Extension's `minify` option to indicate if you want to minify your asset-collections (`true`) or not (`false`). If you indicates that you want to use minifing you must setup a minifier-callback by the `minifier_callback` option. The minifier callback must have two arguments: `string $content, string $type`. This callback must returns the minified version of the passed `$content`'s content.
An example of usage:
```php
$env->addExtension(
	new Assarte_TwigAssets_Extension_Assets(array(
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
An interesting tip: you can use minifier_callback to replace URLs in your assets with framework controlled addresses.
```php
$env->addExtension(
	new Assarte_TwigAssets_Extension_Assets(array(
		...,
		'minifing'			=> true,
		'minifier_callback'	=> function($content, $type) {
			$content = preg_replace_callback(
				'#/\*\s+@url\s+([^\*]+)\s+\*/#i', function($matches) use ($app) {
					return my_frameworks_url_generator($matches[1], true);
				}, $content
			);
		}
	))
);
```
The code above can replace a CSS rule like this:
```css
#myCoolDiv {
	background: url(/* @url /my/magical/background.jpg */);
}
```
...to something like this...
```css
#myCoolDiv {
	background: url(/index.php?trickey_image_watermarker=/my/magical/background.jpg);
}
```

It's simple as hell. Anyway, you should grab these libs with ease:
 * JSMinPlus: https://github.com/mrclay/minify/blob/master/min/lib/JSMinPlus.php
 * CSSmin: https://github.com/mrclay/minify/blob/master/min/lib/CSSmin.php
