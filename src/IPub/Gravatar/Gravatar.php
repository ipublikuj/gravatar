<?php
/**
 * Gravatar.php
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
use Nette\Caching;
use Nette\Http;
use Nette\Utils;

use IPub\Gravatar\Templating\Helpers;

class Gravatar extends \Nette\Object
{
	/**
	 * @var string - URL constants for the avatar images
	 */
	const HTTP_URL	= 'http://www.gravatar.com/avatar/';
	const HTTPS_URL	= 'https://secure.gravatar.com/avatar/';

	/**
	 * @var int
	 */
	protected $expiration = 172800; // two days

	/**
	 * The size to use for avatars.
	 *
	 * @var int
	 */
	protected $size = 80;

	/**
	 * The default image to use
	 * Either a string of the gravatar-recognized default image "type" to use, a URL, or FALSE if using the...default gravatar default image (hah)
	 *
	 * @var mixed
	 */
	protected $defaultImage = FALSE;

	/**
	 * The maximum rating to allow for the avatar.
	 *
	 * @var string
	 */
	protected $maxRating = 'g';

	/**
	 * Should we use the secure (HTTPS) URL base?
	 *
	 * @var bool
	 */
	protected $useSecureUrl = FALSE;

	/**
	 * @var Utils\Image
	 */
	protected $image;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var bool
	 */
	protected $hashEmail = TRUE;

	/**
	 * @var Caching\Cache
	 */
	protected $cache;

	/**
	 * @param Http\Request $httpRequest
	 * @param Caching\IStorage $cacheStorage
	 */
	public function __construct(
		Http\Request $httpRequest,
		Caching\IStorage $cacheStorage
	) {
		$this->useSecureUrl = $httpRequest->isSecured();

		// Init cache
		$this->cache = new Caching\Cache($cacheStorage, 'IPub.Gravatar');
	}

	/**
	 * Get the email hash to use (after cleaning the string)
	 *
	 * @param null|string $email
	 * 
	 * @return string - The hashed form of the email, post cleaning
	 */
	public function getEmailHash($email = NULL)
	{
		// Tack the email hash onto the end.
		if ($this->hashEmail == TRUE && $email !== NULL) {
			// Using md5 as per gravatar docs
			return hash('md5', strtolower(trim($email)));

		} else if ($email !== NULL) {
			return $email;

		} else {
			return str_repeat('0', 32);
		}
	}

	/**
	 * Set the avatar size to use
	 *
	 * @param int $size - The avatar size to use, must be less than 512 and greater than 0.
	 *
	 * @return $this
	 *
	 * @throws Nette\InvalidArgumentException
	 */
	public function setSize($size)
	{
		if (!is_int($size) && !ctype_digit($size)) {
			throw new Nette\InvalidArgumentException('Size specified must be an integer');

		} else if ($size > 512 || $size < 0) {
			throw new Nette\InvalidArgumentException('Size must be within 0 pixels and 512 pixels');
		}

		$this->size = (int) $size;

		return $this;
	}

	/**
	 * Get the currently set avatar size
	 *
	 * @param null|int $size
	 *
	 * @return int
	 */
	public function getSize($size = NULL)
	{
		if ($size && is_int($size)) {
			return (int) $size;

		} else {
			return $this->size;
		}
	}

	/**
	 * Set image cache expiration
	 *
	 * @param int $expiration
	 *
	 * @return $this
	 */
	public function setExpiration($expiration)
	{
		$this->expiration = (int) $expiration;

		return $this;
	}

	/**
	 * Set the default image to use for avatars
	 *
	 * @param mixed $image - The default image to use. Use boolean FALSE for the gravatar default, a string containing a valid image URL, or a string specifying a recognized gravatar "default".
	 *
	 * @return $this
	 *
	 * @throws Nette\InvalidArgumentException
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
					throw new Nette\InvalidArgumentException('The default image is not a valid gravatar "default" and is not a valid URL');
				}
			}
		}

		return $this;
	}

	/**
	 * Get the current default image setting
	 *
	 * @param null|string $defaultImage
	 *
	 * @return mixed - False if no default image set, string if one is set.
	 */
	public function getDefaultImage($defaultImage = NULL)
	{
		if (is_string($defaultImage) && in_array($defaultImage, ['404', 'mm', 'identicon', 'monsterid', 'wavatar', 'retro'])) {
			return $defaultImage;

		} else if (filter_var($defaultImage, FILTER_VALIDATE_URL)) {
			return rawurldecode($defaultImage);

		} else {
			return $this->defaultImage;
		}
	}

	/**
	 * Set the maximum allowed rating for avatars.
	 *
	 * @param string $rating - The maximum rating to use for avatars ('g', 'pg', 'r', 'x').
	 *
	 * @return $this
	 *
	 * @throws Nette\InvalidArgumentException
	 */
	public function setMaxRating($rating)
	{
		$rating = strtolower($rating);

		if (!in_array((string) $rating, ['g', 'pg', 'r', 'x'])) {
			throw new Nette\InvalidArgumentException(sprintf('Invalid rating "%s" specified, only "g", "pg", "r", or "x" are allowed to be used.', $rating));
		}

		$this->maxRating = $rating;

		return $this;
	}

	/**
	 * Get the current maximum allowed rating for avatars
	 *
	 * @param null|string $maxRating
	 *
	 * @return string - The string representing the current maximum allowed rating ('g', 'pg', 'r', 'x').
	 */
	public function getMaxRating($maxRating = NULL)
	{
		if (is_string($maxRating) && in_array((string) $maxRating, ['g', 'pg', 'r', 'x'])) {
			return (string) $maxRating;

		} else {
			return $this->maxRating;
		}
	}

	/**
	 * Returns the Nette\Image instance
	 *
	 * @return Utils\Image
	 */
	public function getImage()
	{
		return $this->image;
	}

	/**
	 * Returns the type of a image
	 *
	 * @return string
	 */
	public function getImageType()
	{
		return $this->type;
	}

	/**
	 * Check if we are using the secure protocol for the image URLs
	 *
	 * @return boolean - Are we supposed to use the secure protocol?
	 */
	public function usingSecureImages()
	{
		return $this->useSecureUrl;
	}

	/**
	 * Enable the use of the secure protocol for image URLs
	 *
	 * @return $this
	 */
	public function enableSecureImages()
	{
		$this->useSecureUrl = TRUE;

		return $this;
	}

	/**
	 * Disable the use of the secure protocol for image URLs
	 *
	 * @return $this
	 */
	public function disableSecureImages()
	{
		$this->useSecureUrl = FALSE;

		return $this;
	}

	/**
	 * Create gravatar image
	 *
	 * @param string|null $email
	 * @param int|null $size
	 *
	 * @return Utils\Image
	 *
	 * @throws Nette\InvalidArgumentException
	 */
	public function get($email = NULL, $size = NULL)
	{
		// Set user email address
		if ($email !== NULL && !Utils\Validators::isEmail($email)) {
			throw new Nette\InvalidArgumentException('Inserted email is not valid email address');
		}

		// Check if avatar is in cache
		if (!$gravatar = $this->cache->load($this->getEmailHash($email) .'.'. $this->getSize($size))) {
			// Get gravatar content
			$gravatar = @file_get_contents($this->buildUrl($email, $size));

			// Store facebook avatar url into cache
			$this->cache->save($this->getEmailHash($email) .'.'. $this->getSize($size), $gravatar, array(
				Caching\Cache::EXPIRE => '7 days',
			));
		}

		$this->image	= Utils\Image::fromString($gravatar);
		$this->type		= Utils\Image::JPEG;

		return $this->image;
	}

	/**
	 * Build the avatar URL based on the provided email address
	 *
	 * @param string|null $email
	 * @param int|null $size
	 * @param string|null $maxRating
	 * @param string|null $defaultImage
	 *
	 * @return string
	 * 
	 * @throws Nette\InvalidArgumentException
	 */
	public function buildUrl($email = NULL, $size = NULL, $maxRating = NULL, $defaultImage = NULL)
	{
		// Set user email address
		if ($email !== NULL && !Utils\Validators::isEmail($email)) {
			throw new Nette\InvalidArgumentException('Inserted email is not valid email address');
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
	 * @return Boolean|null Boolean if we could connect, null if no connection to gravatar.com
	 */
	public function exists($email)
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
	 * @return Helpers
	 */
	public function createTemplateHelpers()
	{
		return new Helpers($this);
	}

	/**
	 * @param string $email
	 * @param null|int $size
	 * @param null|string $maxRating
	 * @param null|string $defaultImage
	 *
	 * @return Http\Url
	 */
	private function createUrl($email, $size = NULL, $maxRating = NULL, $defaultImage = NULL)
	{
		// Tack the email hash onto the end.
		$emailHash = $this->getEmailHash($email);

		// Start building the URL, and deciding if we're doing this via HTTPS or HTTP.
		$url = new Nette\Http\Url(($this->useSecureUrl ? static::HTTPS_URL : static::HTTP_URL) . $emailHash);

		// Time to figure out our request params
		$params = [];
		$params['s'] = $this->getSize($size);
		$params['r'] = $this->getMaxRating($maxRating);
		$params['d'] = $this->getDefaultImage($defaultImage);
		$params['f'] = is_null($email) ? 'y' : NULL;

		// Add query params
		$url->appendQuery($params);

		return $url;
	}
}