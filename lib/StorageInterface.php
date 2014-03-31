<?php

interface TwigAssets_StorageInterface
{
	/**
	 * Stores a builded asset-collection on the given name
	 * 
	 * @param string $name The name of the asset-collection to load
	 * @param string $content The content of the asset-collection
	 */
	public function store($name, $content);
	
	/**
	 * Returns the path where asset-collections should be accessible - or FALSE if not.
	 */
	public function getAccessPath();
}
