# Gravatar

Simple gravatar creator for [Nette Framework](http://nette.org/)

## Instalation

The best way to install ipub/gravatar is using  [Composer](http://getcomposer.org/):


```json
{
	"require": {
		"ipub/gravatar": "dev-master"
	}
}
```

or


```sh
$ composer require ipub/gravatar:@dev
```

After that you have to register extension in config.neon.

```neon
extensions:
	gravatar: IPub\Gravatar\DI\GravatarExtension
```

Package contains trait, which you will have to use in class, where you want to use gravatar creator. This works only for PHP 5.4+, for older version you can simply copy trait content and paste it into class where you want to use it.

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter
{

	use IPub\Gravatar\TGravatar;

}
```

You have to add few lines in base presenter or base control in section createTemplate

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter
{
	protected function createTemplate($class = NULL)
	{
		// Init template
		$template = parent::createTemplate($class);

		// Add gravatar to template
		$template->_gravatar = $this->gravatar;
		// Register template helpers
		$template->registerHelperLoader(callback($this->gravatar->createTemplateHelpers(), 'loader'));

		return $template;
	}
}
```

## Usage

### Using in Latte

```html
<img n:gravatar="john@doe.com, 100" />
```

output:

```html
<img src="http://www.gravatar.com/avatar/b530fd3b225b17f5f7e701283e710a6e?s=120&r=g&d=mm" />
```

### Extension params

```php
	# Gravatar displaying
	gravatar:
		defaultImage	: mm
```
