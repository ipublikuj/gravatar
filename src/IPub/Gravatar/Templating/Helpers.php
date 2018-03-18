<?php
/**
 * Helpers.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Gravatar!
 * @subpackage     Templating
 * @since          1.0.0
 *
 * @date           05.04.14
 */

declare(strict_types = 1);

namespace IPub\Gravatar\Templating;

use Nette;

use IPub\Gravatar;

/**
 * Gravatar template filters
 *
 * @package        iPublikuj:Gravatar!
 * @subpackage     Templating
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Helpers
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

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
	 * @param string $email
	 * @param int|NULL $size
	 *
	 * @return string
	 */
	public function gravatar(string $email, ?int $size = NULL) : string
	{
		return $this->gravatar
			->buildUrl($email, $size);
	}

	/**
	 * @return Gravatar\Gravatar
	 */
	public function getGravatarService() : Gravatar\Gravatar
	{
		return $this->gravatar;
	}
}
