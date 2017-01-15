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

declare(strict_types = 1);

namespace IPubTests\Gravatar;

use Nette;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Gravatar;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

class ExtensionTest extends Tester\TestCase
{
	public function testFunctional()
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('gravatar.gravatar') instanceof Gravatar\Gravatar);
		Assert::true($dic->getService('gravatar.cache') instanceof Gravatar\Caching\Cache);
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer() : Nette\DI\Container
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		Gravatar\DI\GravatarExtension::register($config);

		$config->addConfig(__DIR__ . DS . 'files'. DS .'config.neon');

		return $config->createContainer();
	}
}

\run(new ExtensionTest());
