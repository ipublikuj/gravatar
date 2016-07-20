<?php
/**
 * GravatarExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Gravatar!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           05.04.14
 */

declare(strict_types = 1);

namespace IPub\Gravatar\DI;

use Nette;
use Nette\DI;
use Nette\PhpGenerator as Code;

use IPub;
use IPub\Gravatar;
use IPub\Gravatar\Caching;
use IPub\Gravatar\Templating;

/**
 * Gravatar extension container
 *
 * @package        iPublikuj:Gravatar!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class GravatarExtension extends DI\CompilerExtension
{
	/**
	 * @var array
	 */
	protected $defaults = [
		'expiration'   => 172800,
		'size'         => 80,
		'defaultImage' => FALSE
	];

	public function loadConfiguration()
	{
		// Get container builder
		$builder = $this->getContainerBuilder();
		// Get extension configuration
		$configuration = $this->getConfig($this->defaults);

		// Install Gravatar service
		$builder->addDefinition($this->prefix('gravatar'))
			->setClass(Gravatar\Gravatar::CLASS_NAME)
			->addSetup('setSize', [$configuration['size']])
			->addSetup('setExpiration', [$configuration['expiration']])
			->addSetup('setDefaultImage', [$configuration['defaultImage']]);

		// Create cache services
		$builder->addDefinition($this->prefix('cache'))
			->setClass(Caching\Cache::CLASS_NAME, ['@cacheStorage', 'IPub.Gravatar'])
			->setInject(FALSE);

		// Register template helpers
		$builder->addDefinition($this->prefix('helpers'))
			->setClass(Templating\Helpers::CLASS_NAME)
			->setFactory($this->prefix('@gravatar') . '::createTemplateHelpers')
			->setInject(FALSE);
	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile()
	{
		// Get container builder
		$builder = $this->getContainerBuilder();

		// Install extension latte macros
		$latteFactory = $builder->getDefinition($builder->getByType(Nette\Bridges\ApplicationLatte\ILatteFactory::class) ?: 'nette.latteFactory');

		$latteFactory
			->addSetup('IPub\Gravatar\Latte\Macros::install(?->getCompiler())', ['@self'])
			->addSetup('addFilter', ['gravatar', [$this->prefix('@helpers'), 'gravatar']]);
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, string $extensionName = 'gravatar')
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new GravatarExtension());
		};
	}
}
