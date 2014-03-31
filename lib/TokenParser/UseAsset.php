<?php

/**
 * {% use_asset 'assetname' %}
 */
class TwigAssets_TokenParser_UseAsset extends Twig_TokenParser
{
	/**
	 * Parses a token and returns a node.
	 *
	 * @param Twig_Token $token A Twig_Token instance
	 *
	 * @return Twig_NodeInterface A Twig_NodeInterface instance
	 */
	public function parse(Twig_Token $token)
	{
		$stream = $this->parser->getStream();
		$collection = $this->parser->getExpressionParser()->parseExpression();
		$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

		return new TwigAssets_Node_UseAsset($collection, $token->getLine(), $this->getTag());
	}

	/**
	 * Gets the tag name associated with this token parser.
	 *
	 * @return string The tag name
	 */
	public function getTag()
	{
		return 'use_asset';
	}
}
