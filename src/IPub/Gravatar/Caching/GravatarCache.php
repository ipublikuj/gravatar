<?php
/**
 * AssetCache.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Gravatar!
 * @subpackage	Caching
 * @since		5.0
 *
 * @date		10.03.15
 */

namespace IPub\Gravatar\Caching;

use Nette;
use Nette\Caching;

use IPub;
use IPub\AssetsLoader;

class GravatarCache extends Caching\Cache
{
	/**
	 * Remove all items cached by extension
	 *
	 * @param array $conditions
	 */
	public function clean(array $conditions = NULL)
	{
		parent::clean([self::TAGS => ['ipub.gravatar']]);
	}
}