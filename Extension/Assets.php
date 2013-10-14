<?php

/**
 * Implements an on-the-fly asset manager for Twig
 * 
 * <h1>How it works</h1>
 * <p>You should change Twig's <pre>base_template_class</pre> option value to:
 * <pre>'Assarte_TwigAssets_Template'</pre>. This is highly recommended.</p>
 * <pre><code>		$loader = new Twig_Loader_Filesystem('path/to/templates');
		$env = new Twig_Environment($loader, array(
			'cache'					=> 'path/to/compiled/sources',
			'base_template_class'	=> 'Assarte_TwigAssets_Template'
		));
 * </code></pre>
 * 
 * <p>You must add the <pre>Assarte_TwigAssets_Extension_Assets</pre> extension to Twig.</p>
 * <pre><code>		$env->addExtension(
			new Assarte_TwigAssets_Extension_Assets(array(
				'namespace'			=> Assarte_TwigAssets_Storage_Filesystem::STORE_NAMESPACE,
				'storage'			=> new Assarte_TwigAssets_Storage_Filesystem(
					$this->loader,
					'path/to/public/assets/'
				)
			))
		);
 * </code></pre>
 * <p>That's all in your PHP code!</p>
 * <h1>How to use</h1>
 * <p>You've got two new <i>Tags</i> for Twig:</p>
 * <ul>
 * <li><pre>{% asset 'path/to/asset.file' bind 'collection-name' %}</pre>: This indicates that
 * 		a template requires an asset. You can use and reuse many assets as you want and where you
 * 		want. You can bind any assets to any collections. You can name any collections as you want.
 * 		All assets in an exact collection will be unique even if you require more than once.</li>
 * <li><pre>{% asset_build 'collection-name' as 'css|js' [no_minify] %}</pre>: This indicates a
 * 		place where a collection of assets needs to be used. Here you must specify the type of the
 * 		specific collection ('js' and 'css' supported by default). You can control the minifing of
 * 		assets with the optional <pre>no_minify</pre> switch. For example:
 * 		<pre><code><link type="text/css" rel="stylesheet" media="all" href="path/to/public/assets/{% asset_build 'default' as 'css' %}"></code></pre></li>
 * </ul>
 * <h1>How to minify</h1>
 * <p>You should use the Extension's <pre>minify</pre> option to indicate if you want to minify
 * your asset-collections (<pre>true</pre>) or not (<pre>false</pre>). If you indicates that you
 * want to use minifing you must setup a minifier-callback by the <pre>minifier_callback</pre>
 * option. The minifier callback must have two arguments: <pre>string $content, string $type</pre>.
 * This callback must returns the minified version of the passed <pre>$content</pre>'s content.</p>
 */
class Assarte_TwigAssets_Extension_Assets extends Twig_Extension
{
	/**
	 * @var string
	 */
	protected $namespace;
	
	/**
	 * @var bool
	 */
	protected $rebuild = true;
	
	/**
	 * @var Assarte_TwigAssets_StorageInterface
	 */
	protected $storage;
	
	/**
	 * @var bool
	 */
	protected $minifing = false;
	
	/**
	 * @var callback
	 */
	protected $minifierCallback = null;
	
	/**
	 * Array of allowed asset types (file extensions)
	 * @var array
	 */
	protected $allowedAssetTypes = null;
	
	/**
	 * Array of Assarte_TwigAssets_AssetCollection
	 * @var array
	 */
	protected $assetCollections = array();
	
	/**
	 * @var Twig_Environment
	 */
	protected $env;
	
	public function __construct(array $options = array())
	{
		$defaults = array(
			'namespace'				=> null,
			'rebuild'				=> true,
			'storage'				=> null,
			'minifing'				=> false,
			'minifier_callback'		=> array($this, 'nullMinifier'),
			'allowed_asset_types'	=> array('js', 'css')
		);
		
		$options = array_merge($defaults, $options);
		
		if (!isset($options['storage'])) {
			throw new LogicException('Option \'storage\' expected but not found. You must setup a storage first!');
		}
		
		$this->namespace = isset($options['namespace'])? '@'.$options['namespace'].'/' : '';
		$this->rebuild = $options['rebuild'];
		$this->storage = $options['storage'];
		$this->minifing = $options['minifing'];
		$this->minifierCallback = $options['minifier_callback'];
		$this->allowedAssetTypes = array_combine($options['allowed_asset_types'], $options['allowed_asset_types']);
	}
	
	/**
	 * Initializes the runtime environment.
	 *
	 * This is where you can load some file that contains filter functions for instance.
	 *
	 * @param Twig_Environment $environment The current Twig_Environment instance
	 */
	public function initRuntime(Twig_Environment $environment)
	{
		$this->env = $environment;
		parent::initRuntime($environment);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'assets';
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getTokenParsers()
	{
		return array(
			new Assarte_TwigAssets_TokenParser_AssetBuilder(),
			new Assarte_TwigAssets_TokenParser_Asset()
		);
	}
	
	/**
	 * @param string $type
	 */
	public function addAssetType($type)
	{
		$this->allowedAssetTypes[$type] = $type;
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function isCollectionExists($name)
	{
		return isset($this->assetCollections[$name]);
	}
	
	/**
	 * @param string $name
	 * @return Assarte_TwigAssets_AssetCollection
	 */
	public function createCollection($name)
	{
		$this->assetCollections[$name] = new Assarte_TwigAssets_Collection($this);
		return $this->assetCollections[$name];
	}
	
	/**
	 * @param string $name
	 * @return Assarte_TwigAssets_AssetCollection
	 */
	public function getCollection($name)
	{
		return $this->assetCollections[$name];
	}
	
	/**
	 * @return Twig_Environment
	 */
	public function getEnvironment()
	{
		return $this->env;
	}
	
	/**
	 * Returns storage namespace with trailing '@'.
	 * 
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}
	
	/**
	 * @param string $content
	 * @param string $type
	 * @return string
	 */
	public function nullMinifier($content, $type)
	{
		return $content;
	}
	
	/**
	 * @param string $content
	 * @return string
	 */
	public function buildContent($content)
	{
		// quick check
		$assetsPlaceholders = array();
		$isPlaceholderFound = false;
		foreach (
			/* @var $collection Assarte_TwigAssets_Collection */
			$this->assetCollections as $name=>$collection
		) {
			$assetType = $collection->getType();
			if ($assetType !== '' and !isset($this->allowedAssetTypes[$assetType])) {
				throw new RuntimeException('Collection \''.$name.'\' using unsupported type: '.$assetType);
			}
			
			$placeholder = $collection->getPlaceholder();
			$assetsPlaceholders[$placeholder] = $collection;
			if (!$isPlaceholderFound and strpos($content, $placeholder) !== false) {
				$isPlaceholderFound = true;
			}
		}
		
		// quick return if no placeholders to replace
		if (!$isPlaceholderFound) return $content;
		
		foreach (
			/* @var $collection Assarte_TwigAssets_Collection */
			$assetsPlaceholders as $placeholder=>$collection
		) {
			if (strpos($content, $placeholder) !== false) {
				$assetName = $this->namespace.$collection->getGeneratedName();
				$assetType = $collection->getType();
				$assetBuild = $collection->renderAssets();
				if ($this->minifing) {
					$assetBuild = call_user_func($this->minifierCallback, $assetBuild, $assetType);
				}
				
				$this->storage->store($assetName.'.'.$assetType, $assetBuild);
				
				$assetPath = $this->storage->getAccessPath();
				if ($assetPath !== false) {
					$content = str_replace($placeholder, str_replace($this->namespace, '', $assetName).'.'.$assetType, $content);
				} else {
					$content = str_replace($placeholder, $assetBuild, $content);
				}
			}
		}
		
		return $content;
	}
}
