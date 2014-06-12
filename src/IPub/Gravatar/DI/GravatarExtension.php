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
		$builder = $this->getContainerBuilder();

		// Install Gravatar service
		$builder->addDefinition($this->prefix('gravatar'))
			->setClass('IPub\Gravatar\Gravatar')
			->addSetup("setSize", array($config['size']))
			->addSetup("setExpiration", array($config['expiration']))
			->addSetup("setDefaultImage", array($config['defaultImage']));

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
			->addSetup('IPub\Gravatar\Latte\Macros::install(?->getCompiler())', array('@self'))
			->addSetup('addFilter', array('gravatar', array($this->prefix('@helpers'), 'gravatar')))
			->addSetup('addFilter', array('getGravatarService', array($this->prefix('@helpers'), 'getGravatarService')));
	}

	/**
	 * @param \Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, $extensionName = 'gravatar')
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new GravatarExtension());
		};
	}
}
