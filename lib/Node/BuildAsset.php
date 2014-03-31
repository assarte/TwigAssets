<?php

class TwigAssets_Node_BuildAsset extends Twig_Node
{
	/**
	 * @var bool
	 */
	protected $noMinify = false;
	
	public function __construct(Twig_Node_Expression $collection, Twig_Node_Expression $as, $noMinify = false, Twig_NodeInterface $body, $lineno, $tag = null)
	{
		$this->noMinify = $noMinify;
		parent::__construct(array('collection' => $collection, 'as' => $as, 'body' => $body), array(), $lineno, $tag);
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
			
			// begin placeholder block: ***placeholder
			->raw('echo "***".$this->env->getExtension(\'assets\')->getCollection(')
				->subcompile($this->getNode('collection'))
			->raw(')->createPlaceholder()->setType(')
				->subcompile($this->getNode('as'))
			->raw(')->setMinifiable('.($this->noMinify? 'false' : 'true').')->getPlaceholder();'."\n")
			
			// echo body with '{% use_asset %}'
				->subcompile($this->getNode('body'))
			->raw(';'."\n")
			
			// end placeholder block: placeholder***
			->raw('echo $this->env->getExtension(\'assets\')->getCollection(')
				->subcompile($this->getNode('collection'))
			->raw(')->getPlaceholder()."***";'."\n")
		;
	}
}
