<?php
/**
 * GravatarResponse.php
 *
 * @copyright	More in license.md
 * @license	http://www.ipublikuj.eu
 * @author	Adam Kadlec http://www.ipublikuj.eu
 * @package	iPublikuj:Gravatar!
 * @subpackage	Application
 * @since	5.0
 *
 *
 * @date		02.03.15
 */

namespace IPub\Gravatar\Application;

use Nette;
use Nette\Utils;
use Nette\Http;

use IPub;
use IPub\Gravatar;

class GravatarResponse extends \Nette\Object implements \Nette\Application\IResponse
{
	/** @var string uri */
	const URI = 'http://www.gravatar.com/avatar/';

	/** @var int */
	const EXPIRATION = 172800; // two days

	/** @var string|Image */
	protected $image;

	/** @var string */
	protected $type;

	/**
	 * @param string email
	 * @param int size 1-512
	 */
	public function __construct($email, $size)
	{
		if ( (int) $size < 1 || (int) $size > 512 ) {
			throw new \Nette\InvalidArgumentException('Unsupported size `' . $size . '`, Gravatar API expects `1 - 512`.');
		}

		$arguments = array(
			's' => (int) $size,	// size
			'd' => 'mm',		// default image
			'r' => 'g',			// inclusive rating
		);

		$hash = md5(strtolower(trim($email)));

		$file = TEMP_DIR . '/cache/gravatar/' . $hash . '_' . $size . '.jpeg';

		if ( !file_exists($file) || filemtime($file) < time() - self::EXPIRATION ) {
			if ( !file_exists(TEMP_DIR . DS .'cache'. DS .'gravatar') ) {
				mkdir(TEMP_DIR . DS .'cache'. DS .'gravatar');
			}

			$query = http_build_query($arguments);
			$img = @file_get_contents(self::URI . $hash . '?' . $query);

			if ($img != NULL) {
				file_put_contents($file, $img);
			}
		}

		$this->image = Image::fromFile($file);
		$this->type = Image::JPEG;
	}

	/**
	 * Returns the path to a file or Nette\Image instance.
	 * @return string|Image
	 */
	final public function getImage()
	{
		return $this->image;
	}

	/**
	 * Returns the type of a image.
	 * @return string
	 */
	final public function getType()
	{
		return $this->type;
	}

	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(IRequest $httpRequest, IResponse $httpResponse)
	{
		echo $this->image->send($this->type, 85);
	}
}
