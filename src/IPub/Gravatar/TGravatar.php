<?php
/**
 * TGravatar.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
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
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
trait TGravatar
{
	/**
	 * @var Gravatar
	 */
	protected $gravatar;

	/**
	 * @param Gravatar $gravatar
	 */
	public function injectGravatar(Gravatar $gravatar)
	{
		$this->gravatar = $gravatar;
	}
}
