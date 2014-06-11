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

use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use Latte\Template;
use Nette;;
use IPub\Gravatar\Gravatar;



class Macros extends MacroSet
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
	 * @throws CompileException
	 * @return string
	 */
	public function macroGravatar(MacroNode $node, PhpWriter $writer)
	{
		$this->isUsed = TRUE;
		$arguments = self::prepareMacroArguments($node->args);

		if ($arguments["email"] === NULL) {
			throw new CompileException("Please provide email address.");
		}

		return $writer->write('echo %escape($_gravatar->buildUrl('. $arguments['email'] .', '. $arguments['size'] .'))');
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @throws CompileException
	 * @return string
	 */
	public function macroAttrGravatar(MacroNode $node, PhpWriter $writer)
	{
		$this->isUsed = TRUE;
		$arguments = self::prepareMacroArguments($node->args);

		if ($arguments["email"] === NULL) {
			throw new CompileException("Please provide email address.");
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
	 * @throws \Nette\InvalidStateException
	 */
	public static function validateTemplateParams($template)
	{
		if ($template instanceof Nette\Templating\Template) {
			$params = $template->getParameters();

		} elseif ($template instanceof Nette\Bridges\ApplicationLatte\Template) {
			$params = $template->getParameters();

		} elseif ($template instanceof Template) {
			$params = $template->getParameters();

		} else {
			throw new \InvalidArgumentException('Expected instanceof Template, ' . get_class($template) . ' given.');
		}

		/** @var \Nette\Application\UI\Control[]|string[] $params */

		if (!isset($params['_gravatar']) || !$params['_gravatar'] instanceof Gravatar) {
			$where = isset($params['control']) ? " of component " . get_class($params['control']) . '(' . $params['control']->getName() . ')' : NULL;

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

		$name = $arguments[0];
		$size = (isset($arguments[1]) && !empty($arguments[1])) ? $arguments[1] : NULL;

		return array(
			'email' => $name,
			'size' => $size,
		);
	}
}
