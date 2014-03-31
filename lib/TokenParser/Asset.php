<?php

/**
 * {% asset 'asset-place' bind 'collection' %}
 */
class TwigAssets_TokenParser_Asset extends Twig_TokenParser
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
		$asset = $this->parser->getExpressionParser()->parseExpression();
		$this->parser->getStream()->expect('bind');
		$collection = $this->parser->getExpressionParser()->parseExpression();
		$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

		return new TwigAssets_Node_Asset($asset, $collection, $token->getLine(), $this->getTag());
	}

	/**
	 * Gets the tag name associated with this token parser.
	 *
	 * @return string The tag name
	 */
	public function getTag()
	{
		return 'asset';
	}
}
