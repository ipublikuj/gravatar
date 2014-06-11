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
use Nette\DI\Compiler;
use Nette\DI\Configurator;
use Nette\PhpGenerator as Code;

if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

if (isset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
	unset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']);
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

class GravatarExtension extends Nette\DI\CompilerExtension
{
	/**
	 * @var array
	 */
	protected $defaults = array(
		'expiration'	=> 172800,
		'size'			=> 80,
		'defaultImage'	=> FALSE
	);

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$container = $this->getContainerBuilder();

		// Install extension latte macros
		$latteFactory = $container->hasDefinition('nette.latteFactory')
			? $container->getDefinition('nette.latteFactory')
			: $container->getDefinition('nette.latte');

		$install = 'IPub\Gravatar\Latte\Macros::install';
		$latteFactory->addSetup($install . '(?->getCompiler())', array('@self'));

		$gravatar = $container->addDefinition($this->prefix('gravatar'))
			->setClass('IPub\Gravatar\Gravatar')
			->addSetup("setSize", array($config['size']))
			->addSetup("setExpiration", array($config['expiration']))
			->addSetup("setDefaultImage", array($config['defaultImage']));

		// Register template helpers
		$container->addDefinition($this->prefix('helpers'))
			->setClass('IPub\Gravatar\Templating\Helpers', array($gravatar));
	}

	/**
	 * @param \Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, $extensionName = 'gravatarExtension')
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new GravatarExtension());
		};
	}
}
