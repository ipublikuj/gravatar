<?php
/**
 * TGravatar.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Gravatar!
 * @subpackage     common
 * @since          1.0.0
 *
 * @date           05.04.14
 */

declare(strict_types = 1);

namespace IPub\Gravatar;

/**
 * Gravatar trait for presenters
 *
 * @package        iPublikuj:Gravatar!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
trait TGravatar
{
	/**
	 * @var Gravatar
	 */
	protected $gravatar;

	/**
	 * @param Gravatar $gravatar
	 *
	 * @return void
	 */
	public function injectGravatar(Gravatar $gravatar) : void
	{
		$this->gravatar = $gravatar;
	}
}
