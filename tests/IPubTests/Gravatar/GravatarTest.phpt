<?php
/**
 * Test: IPub\Gravatar\Gravatar
 * @testCase
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Gravatar!
 * @subpackage	Tests
 * @since		5.0
 *
 * @date		10.01.15
 */

namespace IPub\Gravatar;

use Nette;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Gravatar;

require __DIR__ . '/../bootstrap.php';

class GravatarTest extends Tester\TestCase
{
	/**
	 * @var Gravatar\Gravatar
	 */
	private $gravatar;

	/**
	 * Set up
	 */
	public function setUp()
	{
		parent::setUp();

		$dic = $this->createContainer();

		// Get extension services
		$this->gravatar = $dic->getService('gravatar.gravatar');
	}

	public function testGravatarUrlWithDefaultOptions()
	{
		Assert::equal('http://www.gravatar.com/avatar/aabfda88704a1ab55db46d4116442222?s=80&r=g&d=mm', $this->gravatar->buildUrl('john.doe@ipublikuj.eu'));
	}

	public function testGravatarSecureUrlWithDefaultOptions()
	{
		$this->gravatar->enableSecureImages();

		Assert::equal('https://secure.gravatar.com/avatar/aabfda88704a1ab55db46d4116442222?s=80&r=g&d=mm', $this->gravatar->buildUrl('john.doe@ipublikuj.eu', null));
	}

	public function testGravatarInitializedWithOptions()
	{
		$this->gravatar
			->setSize(20)
			->setMaxRating('g')
			->setDefaultImage('mm');

		Assert::equal('http://www.gravatar.com/avatar/aabfda88704a1ab55db46d4116442222?s=20&r=g&d=mm', $this->gravatar->buildUrl('john.doe@ipublikuj.eu'));
	}

	public function testGravatarExists()
	{
		Assert::false($this->gravatar->exists('fake.email@ipublikuj.eu'));
		Assert::true($this->gravatar->exists('adam.kadlec@gmail.com'));
	}

	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	protected function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		Gravatar\DI\GravatarExtension::register($config);

		$config->addConfig(__DIR__ . '/files/config.neon', $config::NONE);

		return $config->createContainer();
	}
}

\run(new GravatarTest());