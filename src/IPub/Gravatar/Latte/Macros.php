<?php
/**
 * Macros.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Gravatar!
 * @subpackage     Latte
 * @since          1.0.0
 *
 * @date           06.04.14
 */

declare(strict_types = 1);

namespace IPub\Gravatar\Latte;

use Nette;

use Latte;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;

/**
 * Gravatar latte macros definition
 *
 * @package        iPublikuj:Gravatar!
 * @subpackage     Latte
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Macros extends MacroSet
{

	/**
	 * Register latte macros
	 *
	 * @param Compiler $compiler
	 *
	 * @return static
	 */
	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);

		/**
		 * {gravatar $email[, $size]}
		 */
		$me->addMacro('gravatar', [$me, 'macroGravatar'], NULL, [$me, 'macroAttrGravatar']);

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
	public function macroGravatar(MacroNode $node, PhpWriter $writer) : string
	{
		$arguments = self::prepareMacroArguments($node->args);

		if ($arguments['email'] === NULL) {
			throw new Latte\CompileException('Please provide email address.');
		}

		return $writer->write('echo property_exists($this, "filters") ? %escape(call_user_func($this->filters->gravatar, ' . $arguments['email'] . ', ' . $arguments['size'] . ')) : $template->getGravatarService()->buildUrl(' . $arguments['email'] . ', ' . $arguments['size'] . ');');
	}


	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 *
	 * @return string
	 *
	 * @throws Latte\CompileException
	 */
	public function macroAttrGravatar(MacroNode $node, PhpWriter $writer) : string
	{
		$arguments = self::prepareMacroArguments($node->args);

		if ($arguments['email'] === NULL) {
			throw new Latte\CompileException('Please provide email address.');
		}

		return $writer->write('?> ' . ($node->htmlNode->name === 'a' ? 'href' : 'src') . '="<?php echo property_exists($this, "filters") ? %escape(call_user_func($this->filters->gravatar, ' . $arguments['email'] . ', ' . $arguments['size'] . ')) : $template->getGravatarService()->buildUrl(' . $arguments['email'] . ', ' . $arguments['size'] . ');?>" <?php');
	}


	/**
	 * @param string $macro
	 *
	 * @return array
	 */
	public static function prepareMacroArguments(string $macro) : array
	{
		$arguments = array_map(function ($value) {
			return trim($value);
		}, explode(',', $macro));

		$name = $arguments[0];
		$size = (isset($arguments[1]) && !empty($arguments[1])) ? $arguments[1] : NULL;

		return [
			'email' => $name,
			'size'  => $size,
		];
	}
}
