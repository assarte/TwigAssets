<?php

/**
 * Helper class to store and handle asset files
 */
class TwigAssets_Collection
{
	/**
	 * @var TwigAssets_Extension_Assets
	 */
	protected $extension;
	
	/**
	 * @var array
	 */
	protected $assets = array();
	
	/**
	 * @var string
	 */
	protected $placeholder;
	
	/**
	 * Its value may: "js", "css" or other externally added handler to minify
	 * @var string
	 */
	protected $type = '';
	
	/**
	 * @var bool
	 */
	protected $minifiable = true;
	
	/**
	 * @var callback
	 */
	protected $nameGeneratorCallback;
	
	public function __construct(TwigAssets_Extension_Assets $extension)
	{
		$this->extension = $extension;
	}
	
	/**
	 * @var TwigAssets_Extension_Assets
	 */
	public function getExtension()
	{
		return $this->extension;
	}
	
	/**
	 * @return int
	 */
	public function getCount()
	{
		return count($this->assets);
	}
	
	/**
	 * @param string $asset
	 * @return TwigAssets_Collection this
	 */
	public function add($asset)
	{
		$this->assets[$asset] = $asset; // Unique add
		return $this;
	}
	
	/**
	 * @return TwigAssets_Collection this
	 */
	public function createPlaceholder()
	{
		if (isset($this->placeholder)) return $this;
		
		$this->placeholder = 'x-'.uniqid().'-'.md5(uniqid()); // "x-" protects from automatic int casting
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getPlaceholder()
	{
		return $this->placeholder;
	}
	
	/**
	 * @param string $type
	 * @return TwigAssets_Collection this
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * @return TwigAssets_Collection this
	 */
	public function setMinifiable($minifiable)
	{
		$this->minifiable = (bool)$minifiable;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isMinifiable()
	{
		return $this->minifiable;
	}
	
	/**
	 * @return string
	 */
	public function renderAssets()
	{
		$loader = $this->extension->getEnvironment()->getLoader();
		$result = '';
		
		foreach ($this->assets as $asset) {
			$result .= $loader->getSource($asset);
		}
		
		return $result;
	}
	
	/**
	 * @return TwigAssets_Collection this
	 */
	public function dumpAssets()
	{
		echo $this->renderAssets();
		return $this;
	}
	
	/**
	 * @param callback $cb
	 */
	public function setNameGeneratorCallback($cb)
	{
		$this->nameGeneratorCallback = $cb;
	}
	
	public function getGeneratedName()
	{
		$result = '';
		$names = array();
		
		foreach ($this->assets as $asset) {
			$names[] = str_replace(
				array('@', '/', '\\', '.'),
				array('!', '-', '-', '-'),
				$asset
			);
		}
		
		$result = join(';', $names);
		
		if (is_callable($this->nameGeneratorCallback)) {
			$result = call_user_func($this->nameGeneratorCallback, $this, $result);
		}
		
		return $result;
	}
}
