<?php
/**
 * GravatarExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Gravatar!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           02.03.15
 */

declare(strict_types = 1);

namespace IPub\Gravatar\Application;

use Nette;
use Nette\Utils;
use Nette\Http;

use IPub;
use IPub\Gravatar;
use IPub\Gravatar\Exceptions;

/**
 * Gravatar image response
 *
 * @package        iPublikuj:Gravatar!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class GravatarResponse extends Nette\Object implements Nette\Application\IResponse
{
	/**
	 * @var Utils\Image
	 */
	private $image;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @param string $email
	 * @param int $size 1-512
	 *
	 * @thrown Exceptions\InvalidArgumentException
	 * @thrown Exceptions\InvalidStateException
	 */
	public function __construct(string $email, int $size)
	{
		// Set user email address
		if (!Utils\Validators::isEmail($email)) {
			throw new Exceptions\InvalidArgumentException('Inserted email is not valid email address');
		}

		if ($size < 1 || $size > 512) {
			throw new Exceptions\InvalidArgumentException(sprintf('Unsupported size "%s", Gravatar expects "1 - 512".', $size));
		}

		$path = $this->createUrl($email, $size, 404);

		if (!$sock = @fsockopen('gravatar.com', 80, $errorNo, $error)) {
			return NULL;
		}

		fputs($sock, "HEAD " . $path . " HTTP/1.0\r\n\r\n");
		$header = fgets($sock, 128);
		fclose($sock);

		if (strpos($header, '404')) {
			throw new Exceptions\InvalidStateException('Gravatar image could not be loaded from the server.');
		}

		$path = $this->createUrl($email, $size);

		$img = @file_get_contents($path);

		$this->image = Utils\Image::fromString($img);
		$this->type = Utils\Image::JPEG;
	}


	/**
	 * Returns Nette\Utils\Image instance
	 *
	 * @return Utils\Image
	 */
	public function getImage() : Utils\Image
	{
		return $this->image;
	}


	/**
	 * Returns the type of a image
	 *
	 * @return string
	 */
	public function getType() : string
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
	private function createUrl(string $email, int $size, string $defaultImage = NULL) : string
	{
		// Tack the email hash onto the end.
		$emailHash = hash('md5', strtolower(trim($email)));

		// Start building the URL, and deciding if we're doing this via HTTPS or HTTP.
		$url = new Http\Url(Gravatar\Gravatar::HTTPS_URL . $emailHash);

		// Time to figure out our request params
		$params = [];
		$params['s'] = $size;
		$params['r'] = 'g';
		$params['d'] = $defaultImage ?: 'mm';

		// Add query params
		$url->appendQuery($params);

		// And we're done
		return $url->getAbsoluteUrl();
	}
}
