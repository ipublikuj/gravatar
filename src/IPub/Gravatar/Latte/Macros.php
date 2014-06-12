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

use Latte;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;

use IPub;

class Macros extends MacroSet
{
	/**
	 * Register latte macros
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
	 * @throws Latte\CompileException
	 */
	public function macroGravatar(MacroNode $node, PhpWriter $writer)
	{
		$arguments = self::prepareMacroArguments($node->args);

		if ($arguments["email"] === NULL) {
			throw new Latte\CompileException("Please provide email address.");
		}

		return $writer->write('echo %escape($template->getGravatarService()->buildUrl('. $arguments['email'] .', '. $arguments['size'] .'))');
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 *
	 * @return string
	 *
	 * @throws Latte\CompileException
	 */
	public function macroAttrGravatar(MacroNode $node, PhpWriter $writer)
	{
		$arguments = self::prepareMacroArguments($node->args);

		if ($arguments["email"] === NULL) {
			throw new Latte\CompileException("Please provide email address.");
		}

		return $writer->write('?> '. ($node->htmlNode->name === 'a' ? 'href' : 'src') .'="<?php echo %escape($template->getGravatarService()->buildUrl('. $arguments['email'] .', '. $arguments['size'] .'))?>" <?php');
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
			'email'	=> $name,
			'size'	=> $size,
		);
	}
}
