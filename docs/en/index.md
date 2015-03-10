# Quickstart

Simple gravatar creator for [Nette Framework](http://nette.org/)

## Installation

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

## Usage

### Using in Latte

```html
<img n:gravatar="'john@doe.com', 100" />
```

output:

```html
<img src="http://www.gravatar.com/avatar/b530fd3b225b17f5f7e701283e710a6e?s=120&r=g&d=mm" />
```

### Using in presenters and components

This extension have service which could be used for generating gravatars images and store them into the cache.

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @param string $email
	 *
	 * @return \Nette\Utils\Image
	 */
	public function handleGetGravatar($email)
	{
		$image = $this->gravatar->get($email)

		return $image;
	}
}
```

### Sending directly as response

You can also directly send a image response of user gravatar. For example when you are displaying user avatar and user hasn't custom image, you can send gravatar instead.

```php
<?php

class BasePresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @param $user
	 */
	public function actionShowAvatar($user)
	{
		// Check if user has custom avatar
		if ($user->getAvatar()) {
			//...

		// User is without custom image
		} else {
			// so we could send gravatar
			$this->sendResponse(new IPub\Gravatar\Application\GravatarResponse($user->getEmail(), 150));
		}
	}
}
```

### Extension params

```php
	# Gravatar displaying
	gravatar:
		defaultImage	: mm
```
