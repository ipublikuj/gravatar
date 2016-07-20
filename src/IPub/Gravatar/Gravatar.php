<?php
/**
 * Gravatar.php
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

use Nette;
use Nette\Http;
use Nette\Utils;

use IPub;
use IPub\Gravatar\Caching;
use IPub\Gravatar\Exceptions;
use IPub\Gravatar\Templating;

/**
 * Gravatar service
 *
 * @package        iPublikuj:Gravatar!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Gravatar extends \Nette\Object
{
	/**
	 * Define class name
	 */
	const CLASS_NAME = __CLASS__;

	/**
	 * @var string - URL constants for the avatar images
	 */
	const HTTP_URL = 'http://www.gravatar.com/avatar/';
	const HTTPS_URL = 'https://secure.gravatar.com/avatar/';

	/**
	 * @var int
	 */
	private $expiration = 172800; // two days

	/**
	 * The size to use for avatars.
	 *
	 * @var int
	 */
	private $size = 80;

	/**
	 * The default image to use
	 * Either a string of the gravatar-recognized default image "type" to use, a URL, or FALSE if using the...default gravatar default image (hah)
	 *
	 * @var mixed
	 */
	private $defaultImage = FALSE;

	/**
	 * The maximum rating to allow for the avatar.
	 *
	 * @var string
	 */
	private $maxRating = 'g';

	/**
	 * Should we use the secure (HTTPS) URL base?
	 *
	 * @var bool
	 */
	private $useSecureUrl = FALSE;

	/**
	 * @var Utils\Image
	 */
	private $image;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var bool
	 */
	private $hashEmail = TRUE;

	/**
	 * @var Caching\Cache
	 */
	private $cache;

	/**
	 * @param Http\Request $httpRequest
	 * @param Caching\Cache $cache
	 */
	public function __construct(
		Http\Request $httpRequest,
		Caching\Cache $cache
	) {
		$this->useSecureUrl = $httpRequest->isSecured();

		// Init cache
		$this->cache = $cache;
	}

	/**
	 * Get the email hash to use (after cleaning the string)
	 *
	 * @param string|NULL $email
	 *
	 * @return string - The hashed form of the email, post cleaning
	 */
	public function getEmailHash(string $email = NULL) : string
	{
		// Tack the email hash onto the end.
		if ($this->hashEmail === TRUE && $email !== NULL) {
			// Using md5 as per gravatar docs
			return hash('md5', strtolower(trim($email)));

		} elseif ($email !== NULL) {
			return $email;

		} else {
			return str_repeat('0', 32);
		}
	}

	/**
	 * Set the avatar size to use
	 *
	 * @param int $size - The avatar size to use, must be less than 512 and greater than 0.
	 */
	public function setSize(int $size)
	{
		if ($this->isSizeValid($size)) {
			$this->size = $size;
		}
	}

	/**
	 * Get the currently set avatar size
	 *
	 * @return int
	 */
	public function getSize() : int
	{
		return $this->size;
	}

	/**
	 * @param int $size
	 *
	 * @return bool
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function isSizeValid(int $size) : bool
	{
		if ($size > 512 || $size < 0) {
			throw new Exceptions\InvalidArgumentException('Size must be within 0 pixels and 512 pixels');
		}

		return TRUE;
	}

	/**
	 * Set image cache expiration
	 *
	 * @param int $expiration
	 */
	public function setExpiration(int $expiration)
	{
		$this->expiration = $expiration;
	}

	/**
	 * Set the default image to use for avatars
	 *
	 * @param mixed $image - The default image to use. Use boolean FALSE for the gravatar default, a string containing a valid image URL, or a string specifying a recognized gravatar "default".
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setDefaultImage($image)
	{
		// Quick check against boolean FALSE.
		if ($image === FALSE) {
			$this->defaultImage = FALSE;

		} else {
			// Check $image against recognized gravatar "defaults"
			// and if it doesn't match any of those we need to see if it is a valid URL.
			$_image = strtolower($image);

			if (in_array($_image, ['404', 'mm', 'identicon', 'monsterid', 'wavatar', 'retro'])) {
				$this->defaultImage = $_image;

			} else {
				if (filter_var($image, FILTER_VALIDATE_URL)) {
					$this->defaultImage = rawurlencode($image);

				} else {
					throw new Exceptions\InvalidArgumentException('The default image is not a valid gravatar "default" and is not a valid URL');
				}
			}
		}
	}

	/**
	 * Get the current default image setting
	 *
	 * @param string|NULL $defaultImage
	 *
	 * @return mixed - False if no default image set, string if one is set
	 */
	public function getDefaultImage(string $defaultImage = NULL)
	{
		if ($defaultImage !== NULL && in_array($defaultImage, ['404', 'mm', 'identicon', 'monsterid', 'wavatar', 'retro'])) {
			return $defaultImage;
		}

		if (filter_var($defaultImage, FILTER_VALIDATE_URL)) {
			return rawurldecode($defaultImage);
		}

		return $this->defaultImage;
	}

	/**
	 * Set the maximum allowed rating for avatars.
	 *
	 * @param string $rating - The maximum rating to use for avatars ('g', 'pg', 'r', 'x').
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setMaxRating(string $rating)
	{
		$rating = strtolower($rating);

		if (!in_array($rating, ['g', 'pg', 'r', 'x'])) {
			throw new Exceptions\InvalidArgumentException(sprintf('Invalid rating "%s" specified, only "g", "pg", "r", or "x" are allowed to be used.', $rating));
		}

		$this->maxRating = $rating;
	}

	/**
	 * Get the current maximum allowed rating for avatars
	 *
	 * @param string|NULL $maxRating
	 *
	 * @return string - The string representing the current maximum allowed rating ('g', 'pg', 'r', 'x').
	 */
	public function getMaxRating(string $maxRating = NULL) : string
	{
		if ($maxRating !== NULL && in_array($maxRating, ['g', 'pg', 'r', 'x'])) {
			return $maxRating;
		}

		return $this->maxRating;
	}

	/**
	 * Returns the Nette\Image instance
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
	public function getImageType() : string
	{
		return $this->type;
	}

	/**
	 * Check if we are using the secure protocol for the image URLs
	 *
	 * @return bool - Are we supposed to use the secure protocol?
	 */
	public function usingSecureImages() : bool
	{
		return $this->useSecureUrl;
	}

	/**
	 * Enable the use of the secure protocol for image URLs
	 */
	public function enableSecureImages()
	{
		$this->useSecureUrl = TRUE;
	}

	/**
	 * Disable the use of the secure protocol for image URLs
	 */
	public function disableSecureImages()
	{
		$this->useSecureUrl = FALSE;
	}

	/**
	 * Create gravatar image
	 *
	 * @param string|NULL $email
	 * @param int|NULL $size
	 *
	 * @return Utils\Image
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function get(string $email = NULL, int $size = NULL) : Utils\Image
	{
		// Set user email address
		if ($email !== NULL && !Utils\Validators::isEmail($email)) {
			throw new Exceptions\InvalidArgumentException('Inserted email is not valid email address');
		}

		if (!$size || !$this->isSizeValid($size)) {
			$size = NULL;
		}

		// Check if avatar is in cache
		if (!$gravatar = $this->cache->load($this->getEmailHash($email) . ($size ? '.' . $size : ''))) {
			// Get gravatar content
			$gravatar = @file_get_contents($this->buildUrl($email, $size));

			// Store facebook avatar url into cache
			$this->cache->save($this->getEmailHash($email) . ($size ? '.' . $size : ''), $gravatar, [
				Caching\Cache::EXPIRE => '7 days',
			]);
		}

		$this->image = Utils\Image::fromString($gravatar);
		$this->type = Utils\Image::JPEG;

		return $this->image;
	}

	/**
	 * Build the avatar URL based on the provided email address
	 *
	 * @param string|NULL $email
	 * @param int|NULL $size
	 * @param string|NULL $maxRating
	 * @param string|NULL $defaultImage
	 *
	 * @return string
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function buildUrl(string $email = NULL, int $size = NULL, string $maxRating = NULL, string $defaultImage = NULL) : string
	{
		// Set user email address
		if ($email !== NULL && !Utils\Validators::isEmail($email)) {
			throw new Exceptions\InvalidArgumentException('Inserted email is not valid email address');
		}

		// Create base url
		$url = $this->createUrl($email, $size, $maxRating, $defaultImage);

		// And we're done.
		return $url->getAbsoluteUrl();
	}

	/**
	 * Checks if a gravatar exists for the email. It does this by checking for the presence of 404 in the header
	 * returned. Will return null if fsockopen fails, for example when the hostname cannot be resolved.
	 *
	 * @param string $email
	 *
	 * @return bool|NULL Boolean if we could connect, null if no connection to gravatar.com
	 */
	public function exists(string $email)
	{
		$path = $this->buildUrl($email, NULL, NULL, '404');

		if (!$sock = @fsockopen('gravatar.com', 80, $errorNo, $error)) {
			return NULL;
		}

		fputs($sock, "HEAD " . $path . " HTTP/1.0\r\n\r\n");
		$header = fgets($sock, 128);
		fclose($sock);

		return strpos($header, '404') ? FALSE : TRUE;
	}

	/**
	 * @return Templating\Helpers
	 */
	public function createTemplateHelpers() : Templating\Helpers
	{
		return new Templating\Helpers($this);
	}

	/**
	 * @param string $email
	 * @param int|NULL $size
	 * @param string|NULL $maxRating
	 * @param string|NULL $defaultImage
	 *
	 * @return Http\Url
	 */
	private function createUrl(string $email, int $size = NULL, string $maxRating = NULL, string $defaultImage = NULL) : Http\Url
	{
		// Tack the email hash onto the end.
		$emailHash = $this->getEmailHash($email);

		// Start building the URL, and deciding if we're doing this via HTTPS or HTTP.
		$url = new Nette\Http\Url(($this->useSecureUrl ? static::HTTPS_URL : static::HTTP_URL) . $emailHash);

		if (!$size || !$this->isSizeValid($size)) {
			$size = NULL;
		}

		// Time to figure out our request params
		$params = [
			's' => $size,
			'r' => $this->getMaxRating($maxRating),
			'd' => $this->getDefaultImage($defaultImage),
			'f' => is_null($email) ? 'y' : NULL,
		];

		// Add query params
		$url->appendQuery($params);

		return $url;
	}
}
