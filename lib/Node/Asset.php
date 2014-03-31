<?php

class TwigAssets_Node_Asset extends Twig_Node
{
	public function __construct(Twig_Node_Expression $asset, Twig_Node_Expression $collection, $lineno, $tag = null)
	{
		parent::__construct(array('asset' => $asset, 'collection' => $collection), array(), $lineno, $tag);
	}
	
	/**
	 * Compiles the node to PHP.
	 *
	 * @param Twig_Compiler A Twig_Compiler instance
	 */
	public function compile(Twig_Compiler $compiler)
	{
		$compiler
			->addDebugInfo($this)
			->write('')
			->raw('if (!$this->env->getExtension(\'assets\')->isCollectionExists(')
				->subcompile($this->getNode('collection'))
			->raw('))'."\n")
			->write('')
			->raw('{ $this->env->getExtension(\'assets\')->createCollection(')
				->subcompile($this->getNode('collection'))
			->raw('); }'."\n")
			->write('')
			->raw('$this->env->getExtension(\'assets\')->getCollection(')
				->subcompile($this->getNode('collection'))
			->raw(')->add(')
				->subcompile($this->getNode('asset'))
			->raw(');'."\n")
		;
	}
}
