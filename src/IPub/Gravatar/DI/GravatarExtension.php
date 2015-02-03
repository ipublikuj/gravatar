<?php
/**
 * GravatarExtension.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:Gravatar!
 * @subpackage	DI
 * @since		5.0
 *
 * @date		05.04.14
 */

namespace IPub\Gravatar\DI;

use Nette;
use Nette\DI;
use Nette\PhpGenerator as Code;

class GravatarExtension extends DI\CompilerExtension
{
	/**
	 * @var array
	 */
	protected $defaults = [
		'expiration'	=> 172800,
		'size'			=> 80,
		'defaultImage'	=> FALSE
	];

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		// Install Gravatar service
		$builder->addDefinition($this->prefix('gravatar'))
			->setClass('IPub\Gravatar\Gravatar')
			->addSetup("setSize", [$config['size']])
			->addSetup("setExpiration", [$config['expiration']])
			->addSetup("setDefaultImage", [$config['defaultImage']]);

		// Register template helpers
		$builder->addDefinition($this->prefix('helpers'))
			->setClass('IPub\Gravatar\Templating\Helpers')
			->setFactory($this->prefix('@gravatar') . '::createTemplateHelpers')
			->setInject(FALSE);

		// Install extension latte macros
		$latteFactory = $builder->hasDefinition('nette.latteFactory')
			? $builder->getDefinition('nette.latteFactory')
			: $builder->getDefinition('nette.latte');

		$latteFactory
			->addSetup('IPub\Gravatar\Latte\Macros::install(?->getCompiler())', ['@self'])
			->addSetup('addFilter', ['gravatar', [$this->prefix('@helpers'), 'gravatar']])
			->addSetup('addFilter', ['getGravatarService', [$this->prefix('@helpers'), 'getGravatarService']]);
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, $extensionName = 'gravatar')
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new GravatarExtension());
		};
	}
}
