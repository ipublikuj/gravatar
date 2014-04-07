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
use Nette\Image,
	Nette\InvalidArgumentException;
use Nette\Utils\Validators;

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
	 * Email for gravatar creation
	 *
	 * @var string
	 */
	protected $email = NULL;

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
	 * A temporary internal cache of the URL parameters to use
	 *
	 * @var string
	 */
	protected $paramCache = NULL;

	/**
	 * @var string|Image
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
	 * @param Nette\Http\Request $httpRequest
	 */
	public function __construct(Nette\Http\Request $httpRequest)
	{
		$this->useSecureUrl = $httpRequest->isSecured();
	}

	/**
	 * @param string $email
	 *
	 * @return $this
	 *
	 * @throws InvalidArgumentException
	 */
	public function setEmail($email)
	{
		if (!Validators::isEmail($email)) {
			throw new InvalidArgumentException('Inserted email is not valid email address');
		}

		// Wipe out the param cache.
		$this->paramCache = NULL;

		$this->email = (string) $email;

		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Get the email hash to use (after cleaning the string)
	 *
	 * @return string - The hashed form of the email, post cleaning
	 */
	public function getEmailHash()
	{
		// Using md5 as per gravatar docs.
		return hash('md5', strtolower(trim($this->email)));
	}

	/**
	 * Set the avatar size to use
	 *
	 * @param int $size - The avatar size to use, must be less than 512 and greater than 0.
	 *
	 * @return $this
	 *
	 * @throws InvalidArgumentException
	 */
	public function setSize($size)
	{
		// Skip if size is not set
		if ( !$size ) {
			return $this;
		}

		// Wipe out the param cache.
		$this->paramCache = NULL;

		if (!is_int($size) && !ctype_digit($size)) {
			throw new InvalidArgumentException('Avatar size specified must be an integer');
		}

		$this->size = (int) $size;

		if ($this->size > 512 || $this->size < 0) {
			throw new InvalidArgumentException('Avatar size must be within 0 pixels and 512 pixels');
		}

		return $this;
	}

	/**
	 * Get the currently set avatar size
	 *
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
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
	 * @throws InvalidArgumentException
	 */
	public function setDefaultImage($image)
	{
		// Quick check against boolean FALSE.
		if ($image === FALSE) {
			$this->defaultImage = FALSE;

			return $this;
		}

		// Wipe out the param cache.
		$this->paramCache = NULL;

		// Check $image against recognized gravatar "defaults", and if it doesn't match any of those we need to see if it is a valid URL.
		$_image = strtolower($image);
		$valid_defaults = array('404' => 1, 'mm' => 1, 'identicon' => 1, 'monsterid' => 1, 'wavatar' => 1, 'retro' => 1);

		if (!isset($valid_defaults[$_image])) {
			if (!filter_var($image, FILTER_VALIDATE_URL)) {
				throw new InvalidArgumentException('The default image specified is not a recognized gravatar "default" and is not a valid URL');

			} else {
				$this->defaultImage = rawurlencode($image);
			}

		} else {
			$this->defaultImage = $_image;
		}

		return $this;
	}

	/**
	 * Get the current default image setting
	 *
	 * @return mixed - False if no default image set, string if one is set.
	 */
	public function getDefaultImage()
	{
		return $this->defaultImage;
	}

	/**
	 * Set the maximum allowed rating for avatars.
	 *
	 * @param string $rating - The maximum rating to use for avatars ('g', 'pg', 'r', 'x').
	 *
	 * @return $this
	 *
	 * @throws InvalidArgumentException
	 */
	public function setMaxRating($rating)
	{
		// Wipe out the param cache.
		$this->paramCache = NULL;

		$rating = strtolower($rating);
		$valid_ratings = array('g' => 1, 'pg' => 1, 'r' => 1, 'x' => 1);
		
		if (!isset($valid_ratings[$rating])) {
			throw new InvalidArgumentException(sprintf('Invalid rating "%s" specified, only "g", "pg", "r", or "x" are allowed to be used.', $rating));
		}

		$this->maxRating = $rating;

		return $this;
	}

	/**
	 * Get the current maximum allowed rating for avatars
	 *
	 * @return string - The string representing the current maximum allowed rating ('g', 'pg', 'r', 'x').
	 */
	public function getMaxRating()
	{
		return $this->maxRating;
	}

	/**
	 * Returns the Nette\Image instance
	 *
	 * @return Image
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
	 * @return Image
	 */
	public function get()
	{
		$file = TEMP_DIR . DS .'cache'. DS .'_Gravatar.Images'. DS . $this->getEmailHash($this->email) . '_' . $this->size . '.jpeg';

		if ( !file_exists($file) || filemtime($file) < time() - $this->expiration ) {
			if ( !file_exists(TEMP_DIR . DS .'cache'. DS .'_Gravatar.Images') ) {
				mkdir(TEMP_DIR . DS .'cache'. DS .'_Gravatar.Images');
			}

			// Get gravatar content
			$img = @file_get_contents($this->buildUrl());

			if ( $img != NULL ) {
				file_put_contents($file, $img);
			}
		}

		$this->image	= Image::fromFile($file);
		$this->type		= Image::JPEG;

		return $this->image;
	}

	/**
	 * Build the avatar URL based on the provided email address
	 *
	 * @return string
	 */
	public function buildUrl()
	{
		// Tack the email hash onto the end.
		if ( $this->hashEmail == TRUE && !empty($this->email) ) {
			$emailHash = $this->getEmailHash();

		} else if ( !empty($this->email) ) {
			$emailHash = $this->email;

		} else {
			$emailHash = str_repeat('0', 32);
		}

		// Start building the URL, and deciding if we're doing this via HTTPS or HTTP.
		if ( $this->useSecureUrl ) {
			$url = new Nette\Http\Url(static::HTTPS_URL . $emailHash);

		} else {
			$url = new Nette\Http\Url(static::HTTP_URL . $emailHash);
		}

		// Check to see if the paramCache property has been populated yet
		if ( $this->paramCache === NULL ) {
			// Time to figure out our request params
			$params = array();
			$params['s'] = $this->getSize();
			$params['r'] = $this->getMaxRating();

			if ( $this->getDefaultImage() ) {
				$params['d'] = $this->getDefaultImage();
			}

			// Stuff the request params into the paramCache property for later reuse
			$this->paramCache = !empty($params) ? $params : NULL;
		}

		if ( empty($this->email) ) {
			$this->paramCache['f'] = 'y';
		}

		// And we're done.
		return $url->appendQuery($$this->paramCache)->getAbsoluteUrl();
	}

	/**
	 * @return TemplateHelpers
	 */
	public function createTemplateHelpers()
	{
		return new Helpers($this);
	}
}