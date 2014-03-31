<?php

class TwigAssets_Storage_Filesystem implements TwigAssets_StorageInterface
{
	const STORE_NAMESPACE = '__store__';
	
	/**
	 * @var Twig_Loader_Filesystem
	 */
	protected $loader;
	
	/**
	 * @var string
	 */
	protected $path;
	
	public function __construct(Twig_Loader_Filesystem $loader, $path, $namespace = self::STORE_NAMESPACE)
	{
		$this->loader = $loader;
		$this->path = str_replace('\\', '/', rtrim($path, '/\\').'/');
		$path = rtrim($this->path, '/\\');
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
			chmod($path, 0777); // just for sure
		}
		$this->loader->addPath($this->path, $namespace);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function store($name, $content)
	{
		if (strpos($name, '@') !== false) {
			$name = substr($name, (int)strpos($name, '/') + 1); // we must use storage path always
		}
		$name = str_replace('\\', '/', trim($name, '/\\'));
		$fullpath = $this->path.$name;
		$namedir = substr($fullpath, 0, strrpos($fullpath, '/')); // names may contents more folder paths
		if (!file_exists($namedir)) {
			mkdir($namedir, 0777, true);
			chmod($namedir, 0777); // just for sure
		}
		
		file_put_contents($fullpath, $content);
		chmod($fullpath, 0777);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getAccessPath()
	{
		return $this->path;
	}
}
