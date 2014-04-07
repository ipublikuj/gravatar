<?php
/**
 * Macros.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Gravatar!
 * @subpackage	Latte
 * @since		5.0
 *
 * @date		06.04.14
 */

namespace IPub\Gravatar\Latte;

use Nette;
use Nette\Latte\Compiler,
	Nette\Latte\MacroNode,
	Nette\Latte\PhpWriter;

use IPub\Gravatar\Gravatar;

class Macros extends Nette\Latte\Macros\MacroSet
{
	/**
	 * @var bool
	 */
	private $isUsed = FALSE;

	/**
	 * @param Compiler $compiler
	 *
	 * @return ImgMacro|\Nette\Latte\Macros\MacroSet
	 */
	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);

		/**
		 * {gravatar $email[, $size]}
		 */
		$me->addMacro('gravatar', array($me, 'macroGravatar'), NULL, array($me, 'macroAttrGravatar'));

		return $me;
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 *
	 * @return string
	 *
	 * @throws \Nette\Latte\CompileException
	 */
	public function macroGravatar(MacroNode $node, PhpWriter $writer)
	{
		$this->isUsed = TRUE;
		$arguments = self::prepareMacroArguments($node->args);

		if ($arguments["email"] === NULL) {
			throw new Nette\Latte\CompileException("Please provide email address.");
		}

		return $writer->write('echo %escape($_gravatar->buildUrl('. $arguments['email'] .', '. $arguments['size'] .'))');
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 *
	 * @return string
	 *
	 * @throws Nette\Latte\CompileException
	 */
	public function macroAttrGravatar(MacroNode $node, PhpWriter $writer)
	{
		$this->isUsed = TRUE;
		$arguments = self::prepareMacroArguments($node->args);

		if ($arguments["email"] === NULL) {
			throw new Nette\Latte\CompileException("Please provide email address.");
		}

		return $writer->write('?> '. ($node->htmlNode->name === 'a' ? 'href' : 'src') .'="<?php echo %escape($_gravatar->buildUrl('. $arguments['email'] .', '. $arguments['size'] .'))?>" <?php');
	}

	/**
	 *
	 */
	public function initialize()
	{
		$this->isUsed = FALSE;
	}

	/**
	 * Finishes template parsing.
	 *
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		if (!$this->isUsed) {
			return array();
		}

		return array(
			get_called_class() . '::validateTemplateParams($template);',
			NULL
		);
	}

	/**
	 * @param \Nette\Templating\Template $template
	 *
	 * @throws \Nette\InvalidStateException
	 */
	public static function validateTemplateParams(Nette\Templating\Template $template)
	{
		$params = $template->getParameters();

		if (!isset($params['_gravatar']) || !$params['_gravatar'] instanceof Gravatar) {
			$where = isset($params['control']) ?
				" of component " . get_class($params['control']) . '(' . $params['control']->getName() . ')'
				: NULL;

			throw new Nette\InvalidStateException(
				'Please provide an instanceof IPub\\Gravatar\\Gravatar ' .
				'as a parameter $_gravatar to template' . $where
			);
		}
	}

	/**
	 * @param string $macro
	 *
	 * @return array
	 */
	public static function prepareMacroArguments($macro)
	{
		$arguments = array_map(function ($value) {
			return trim($value);
		}, explode(",", $macro));

		$name	= $arguments[0];
		$size	= (isset($arguments[1]) && !empty($arguments[1])) ? $arguments[1] : NULL;

		return array(
			"email"		=> $name,
			"size"		=> $size,
		);
	}
}