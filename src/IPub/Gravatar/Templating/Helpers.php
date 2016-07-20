<?php
/**
 * Helpers.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Gravatar!
 * @subpackage     Templating
 * @since          1.0.0
 *
 * @date           05.04.14
 */

declare(strict_types = 1);

namespace IPub\Gravatar\Templating;

use Nette;

use Latte\Engine;

use IPub\Gravatar;

/**
 * Gravatar template filters
 *
 * @package        iPublikuj:Gravatar!
 * @subpackage     Templating
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Helpers extends Nette\Object
{
	/**
	 * Define class name
	 */
	const CLASS_NAME = __CLASS__;

	/**
	 * @var Gravatar\Gravatar
	 */
	private $gravatar;

	/**
	 * @param Gravatar\Gravatar $gravatar
	 */
	public function __construct(Gravatar\Gravatar $gravatar)
	{
		$this->gravatar = $gravatar;
	}

	/**
	 * Register template filters
	 *
	 * @param Engine $engine
	 */
	public function register(Engine $engine)
	{
		$engine->addFilter('gravatar', [$this, 'gravatar']);
	}

	/**
	 * @param string $email
	 * @param int|NULL $size
	 *
	 * @return string
	 */
	public function gravatar(string $email, int $size = NULL) : string
	{
		return $this->gravatar
			->buildUrl($email, $size);
	}
}
