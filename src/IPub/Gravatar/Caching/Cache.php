<?php
/**
 * Cache.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Gravatar!
 * @subpackage     Caching
 * @since          1.0.0
 *
 * @date           10.03.15
 */

declare(strict_types = 1);

namespace IPub\Gravatar\Caching;

use Nette\Caching;

/**
 * Loaded gravatar cache
 *
 * @package        iPublikuj:Gravatar!
 * @subpackage     Caching
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Cache extends Caching\Cache
{
	/**
	 * Remove all items cached by extension
	 *
	 * @param array $conditions
	 *
	 * @return void
	 */
	public function clean(array $conditions = NULL) : void
	{
		parent::clean([self::TAGS => ['ipub.gravatar']]);
	}
}
