<?php

/**
 * {% asset_build 'assetname' as 'js|css' [no_minify] %}
 */
class Assarte_TwigAssets_TokenParser_AssetBuilder extends Twig_TokenParser
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
		$stream->expect('as');
		$as = $this->parser->getExpressionParser()->parseExpression();
		$noMinify = false;
        if ($stream->test(Twig_Token::NAME_TYPE, 'no_minify')) {
            $stream->next();
			$noMinify = true;
        }
		$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

		return new Assarte_TwigAssets_Node_AssetBuilder($collection, $as, $noMinify, $token->getLine(), $this->getTag());
	}

	/**
	 * Gets the tag name associated with this token parser.
	 *
	 * @return string The tag name
	 */
	public function getTag()
	{
		return 'asset_build';
	}
}
