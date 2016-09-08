# php_codesniffer_yii2_githooks

There must be a description

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

    composer require --dev mj4444/php_codesniffer_yii2_githook:dev-master

or

    php composer.phar require --dev mj4444/php_codesniffer_yii2_githook:dev-master

Install in isolated folder if there are conflicts with other packages composer.

Usage
-----

Execute script `vendor/bin/codesniffer_git_install` for install git hooks and filters.

Execute script `vendor/bin/codesniffer "file.name"` for check files.

Execute script `vendor/bin/codesniffer_fix "file.name"` for fix files.

Use file "GIT_ROOT/.phpcsgit" for configuration codesniffer.

Links
-----

- [GitHub](https://github.com/mj4444ru/php_codesniffer_yii2_githooks)
- [Packagist](https://packagist.org/packages/mj4444/php_codesniffer_yii2_githook)
