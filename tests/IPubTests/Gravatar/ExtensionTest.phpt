<?php
/**
 * Test: IPub\Gravatar\Extension
 * @testCase
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Gravatar!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           10.01.15
 */

namespace IPubTests\Gravatar;

use Nette;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Gravatar;

require __DIR__ . '/../bootstrap.php';

class ExtensionTest extends Tester\TestCase
{
	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		Gravatar\DI\GravatarExtension::register($config);

		$config->addConfig(__DIR__ . '/files/config.neon');

		return $config->createContainer();
	}

	public function testFunctional()
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('gravatar.gravatar') instanceof Gravatar\Gravatar);
		Assert::true($dic->getService('gravatar.cache') instanceof Gravatar\Caching\Cache);
	}
}

\run(new ExtensionTest());
