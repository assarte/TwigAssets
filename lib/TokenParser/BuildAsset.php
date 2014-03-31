<?php

/**
 * {% build 'assetname' as 'js|css' [no_minify] %}...{% use_asset 'assetname' %}...{% endbuild %}
 */
class TwigAssets_TokenParser_BuildAsset extends Twig_TokenParser
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
		
		$stream->expect(Twig_Token::BLOCK_END_TYPE);
		$body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
		$stream->expect(Twig_Token::BLOCK_END_TYPE);

		return new TwigAssets_Node_BuildAsset($collection, $as, $noMinify, $body, $token->getLine(), $this->getTag());
	}
	
	public function decideBlockEnd(Twig_Token $token)
	{
		return $token->test('endbuild');
	}
	
	/**
	 * Gets the tag name associated with this token parser.
	 *
	 * @return string The tag name
	 */
	public function getTag()
	{
		return 'build';
	}
}
