<?php
/**
 * Test: IPub\Gravatar\Gravatar
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

use IPub\Gravatar;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

class GravatarTest extends Tester\TestCase
{
	/**
	 * @var Gravatar\Gravatar
	 */
	private $gravatar;

	/**
	 * {@inheritdoc}
	 */
	public function setUp() : void
	{
		parent::setUp();

		$dic = $this->createContainer();

		// Get extension services
		$this->gravatar = $dic->getService('gravatar.gravatar');
	}

	public function testGravatarUrlWithDefaultOptions() : void
	{
		Assert::equal('http://www.gravatar.com/avatar/aabfda88704a1ab55db46d4116442222?s=80&r=g&d=mm', $this->gravatar->buildUrl('john.doe@ipublikuj.eu'));
	}

	public function testGravatarSecureUrlWithDefaultOptions() : void
	{
		$this->gravatar->enableSecureImages();

		Assert::equal('https://secure.gravatar.com/avatar/aabfda88704a1ab55db46d4116442222?s=80&r=g&d=mm', $this->gravatar->buildUrl('john.doe@ipublikuj.eu', NULL));
	}

	public function testGravatarInitializedWithOptions() : void
	{
		$this->gravatar->setSize(20);
		$this->gravatar->setMaxRating('g');
		$this->gravatar->setDefaultImage('mm');

		Assert::equal('http://www.gravatar.com/avatar/aabfda88704a1ab55db46d4116442222?s=20&r=g&d=mm', $this->gravatar->buildUrl('john.doe@ipublikuj.eu'));
	}

	public function testGravatarExists() : void
	{
		Assert::false($this->gravatar->exists('fake.email@ipublikuj.eu'));
		Assert::true($this->gravatar->exists('adam.kadlec@gmail.com'));
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer() : Nette\DI\Container
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		Gravatar\DI\GravatarExtension::register($config);

		$config->addConfig(__DIR__ . DS . 'files' . DS . 'config.neon');

		return $config->createContainer();
	}
}

\run(new GravatarTest());
