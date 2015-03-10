<?php
/**
 * GravatarExtension.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Gravatar!
 * @subpackage	Application
 * @since		5.0
 *
 * @date		02.03.15
 */

namespace IPub\Gravatar\Application;

use Nette;
use Nette\Utils;
use Nette\Http;

use IPub;
use IPub\Gravatar;

class GravatarResponse extends Nette\Object implements Nette\Application\IResponse
{
	/**
	 * @var Utils\Image
	 */
	protected $image;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @param string $email
	 * @param int $size 1-512
	 *
	 * @thrown Nette\InvalidArgumentException
	 * @thrown Nette\InvalidStateException
	 */
	public function __construct($email, $size)
	{
		// Set user email address
		if ($email !== NULL && !Utils\Validators::isEmail($email)) {
			throw new Nette\InvalidArgumentException('Inserted email is not valid email address');
		}

		if ((int) $size < 1 || (int) $size > 512) {
			throw new Nette\InvalidArgumentException("Unsupported size '$size', Gravatar expects '1 - 512'.");
		}

		$path = $this->createUrl($email, $size, 404);

		if (!$sock = @fsockopen('gravatar.com', 80, $errorNo, $error)) {
			return NULL;
		}

		fputs($sock, "HEAD " . $path . " HTTP/1.0\r\n\r\n");
		$header = fgets($sock, 128);
		fclose($sock);

		if (strpos($header, '404')) {
			throw new Nette\InvalidStateException('Gravatar image could not be loaded from the server.');
		}

		$path = $this->createUrl($email, $size);

		$img = @file_get_contents($path);

		$this->image = Utils\Image::fromString($img);
		$this->type = Utils\Image::JPEG;
	}

	/**
	 * Returns the path to a file or Nette\Utils\Image instance
	 *
	 * @return string|Utils\Image
	 */
	final public function getImage()
	{
		return $this->image;
	}

	/**
	 * Returns the type of a image
	 *
	 * @return string
	 */
	final public function getType()
	{
		return $this->type;
	}

	/**
	 * Sends response to output
	 *
	 * @param Http\IRequest $httpRequest
	 * @param Http\IResponse $httpResponse
	 *
	 * @return void
	 */
	public function send(Http\IRequest $httpRequest, Http\IResponse $httpResponse)
	{
		echo $this->image->send($this->type, 85);
	}

	/**
	 * @param string $email
	 * @param int $size
	 * @param string $defaultImage
	 *
	 * @return string
	 */
	private function createUrl($email, $size, $defaultImage = NULL)
	{
		// Tack the email hash onto the end.
		$emailHash = hash('md5', strtolower(trim($email)));

		// Start building the URL, and deciding if we're doing this via HTTPS or HTTP.
		$url = new Http\Url(Gravatar\Gravatar::HTTPS_URL . $emailHash);

		// Time to figure out our request params
		$params = [];
		$params['s'] = $size;
		$params['r'] = 'g';
		$params['d'] = $defaultImage ?:'mm';

		// Add query params
		$url->appendQuery($params);

		// And we're done.
		return $url->getAbsoluteUrl();
	}
}