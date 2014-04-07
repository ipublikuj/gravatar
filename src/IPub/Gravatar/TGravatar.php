<?php
/**
 * TGravatar.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Gravatar!
 * @subpackage	common
 * @since		5.0
 *
 * @date		05.04.14
 */

namespace IPub\Gravatar;

use Nette;

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