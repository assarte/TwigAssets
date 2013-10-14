<?php

class Assarte_TwigAssets_Storage_Array implements Assarte_TwigAssets_StorageInterface
{
	/**
	 * @var array
	 */
	protected $storage;
	
	public function __construct(array &$storage)
	{
		$this->storage = $storage;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function store($name, $content)
	{
		$this->storage[(string)$name] = $content;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getAccessPath()
	{
		return false;
	}
}
