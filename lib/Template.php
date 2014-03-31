<?php

abstract class TwigAssets_Template extends Twig_Template
{
	public function display(array $context, array $blocks = array())
	{
		ob_start();
		parent::display($context, $blocks);
		$content = ob_get_clean();
		
		$content = $this->env->getExtension('assets')->buildContent($content);
		
		echo $content;
	}
}
