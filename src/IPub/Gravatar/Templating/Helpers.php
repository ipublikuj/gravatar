<?php
/**
 * Helpers.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Gravatar!
 * @subpackage	Templating
 * @since		5.0
 *
 * @date		05.04.14
 */

namespace IPub\Gravatar\Templating;

use Nette;

use Latte\Engine;

use IPub\Gravatar;

class Helpers extends Nette\Object
{
	/**
	 * @var Gravatar\Gravatar
	 */
	private $gravatar;

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
		$engine->addFilter('gravatar', array($this, 'gravatar'));
		$engine->addFilter('getGravatarService', array($this, 'getGravatarService'));
	}

	/**
	 * @param string $email
	 * @param null|int $size
	 *
	 * @return string
	 */
	public function gravatar($email, $size = NULL)
	{
		return $this->gravatar
			->buildUrl($email, $size);
	}

	/**
	 * @return Gravatar\Gravatar
	 */
	public function getGravatarService()
	{
		return $this->gravatar;
	}
}