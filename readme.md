# Gravatar

[![Build Status](https://img.shields.io/travis/iPublikuj/gravatar.svg?style=flat-square)](https://travis-ci.org/iPublikuj/gravatar)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/iPublikuj/gravatar.svg?style=flat-square)](https://scrutinizer-ci.com/g/iPublikuj/gravatar/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/ipub/gravatar.svg?style=flat-square)](https://packagist.org/packages/ipub/gravatar)
[![Composer Downloads](https://img.shields.io/packagist/dt/ipub/gravatar.svg?style=flat-square)](https://packagist.org/packages/ipub/gravatar)

Gravatar creator for [Nette Framework](http://nette.org/)

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

## Documentation

Learn how to show and get users gravatars in [documentation](https://github.com/iPublikuj/gravatar/blob/master/docs/en/index.md).

***
Homepage [http://www.ipublikuj.eu](http://www.ipublikuj.eu) and repository [http://github.com/iPublikuj/gravatar](http://github.com/iPublikuj/gravatar).
