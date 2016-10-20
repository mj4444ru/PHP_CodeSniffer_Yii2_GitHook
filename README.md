php_codesniffer_yii2_githooks
=============================

There must be a description

Installation
------------

The preferred way to install this extension is through [composer][].

    composer require --dev mj4444/php_codesniffer_yii2_githook:dev-master

or

    php composer.phar require --dev mj4444/php_codesniffer_yii2_githook:dev-master

Install in isolated folder if there are conflicts with other packages composer.

Usage
-----

Execute script `vendor/bin/codesniffer_git_install` for install git hooks and filters.

Execute script `vendor/bin/codesniffer "file.name"` for check files.

Execute script `vendor/bin/codesniffer_fix "file.name"` for fix files.

Use file "**GIT_ROOT/.phpcsgit**" for configuration codesniffer.

Resolving conflicts
-------------------

Use `// @codingStandardsIgnoreFile` to skip file check.

Use `// @codingStandardsIgnoreLine` to skip line check.

Use `// @codingStandardsIgnoreStart` and `// @codingStandardsIgnoreEnd` to skip fragment check.

Use param `IGNORE` in config "**.phpcsgit**"

Configuring
-----------

Параметры использования хранятся в файле "**.phpcsgit**" расположенным в корне проекта.

Если используется [JSHINT][], файл настроек "**.jshintrc**" будет искаться в корне проекта 

Файл настроек для [JSHINT][] взят из статьи [JavaScript, the winning style][javascript-the-winning-style] [ [Eng][javascript-the-winning-style-original] ].

### Формат файла "**.phpcsgit**"

Файл представляет из себя набор строк, где каждая строка - отдельный параметр. Имя и значения разделены знаком `=` без пробелов.

Используемые парметры:

 - **VERSION** - Служебный параметр, определяет текущую версию файла (необходим для автообновления, менять его нельзя).
 - **STANDARD** - Определяет стандарт проверки. Возможные стандарты указаны в комментариях файла.
 - **ENCODING** - Подробнее [Setting the default encoding][setting-the-default-encoding].
 - **IGNORE_WARNINGS** - Возможные значения `Y`, `N`. Подробнее [Ignoring warnings][ignoring-warnings-when-generating-the-exit-code].
 - **PROGRESS** - Возможные значения `Y`, `N`. Подробнее [Showing progress][showing-progress-by-default].
 - **COLORS** - Возможные значения `Y`, `N`. Подробнее [Using colors in output][using-colors-in-output-by-default].
 - **FILTER_NO_ABORT** - Возможные значения `Y`, `N`. Не прерывать добавление файла в репозиторий при ошибке. Не влияет на **commit**.
                         Для отключения проверки при комите, используйте параметр `--no-verify`.
                         Так же отключает автоисправление при невозможности исправления всех ошибок.
 - **EXTENSIONS** - Расширения проверяемых файлов. Подробнее [Specifying Valid File Extensions][specifying-valid-file-extensions].
                    The type of the file can be specified using: `ext/type` e.g., `module/php,es/js`.
 - **PHPEXTENSIONS** - Расширения проверяемых файлов c помощью `php -l`.
 - **JSHINTEXTENSIONS** - Расширения проверяемых файлов c помощью [JSHINT][] (для отключения, можно параметр сделать пустым).
 - **IGNORE** - Пути которые будут проигнорированы при проверке через запятую.
                Путь относительно корня проекта, возможно использование `*` для указания любых символов (не регулярное выражение).

### Формат файла "**.jshintrc**"

[Docs][JSHINTDocs]

Warnings
--------

При исправлении JS файла утилитой [phpcbf][], проверка [JSHINT][] выполняется дважды (до и после исправления).

При выводе сообщения "**Check fails after fixing errors in phpcbf**", позиции ошибок [JSHINT][] будут неверными 

Links
-----

 - **php_codesniffer_yii2_githooks** [GitHub][php_codesniffer_yii2_githooks], [Packagist][php_codesniffer_yii2_githooks_packagist]
 - **PHP_CodeSniffer** [GitHub][PHP_CodeSniffer]
 - **yii2-coding-standards** [GitHub][yii2-coding-standards]
 - **jshint** [Install][JSHINTInstall], [Docs][JSHINTDocs]


[composer]: http://getcomposer.org/download/
[JSHINT]: http://jshint.com/
[JSHINTDocs]: http://jshint.com/docs/
[JSHINTInstall]: http://jshint.com/install/
[php_codesniffer_yii2_githooks]: https://github.com/mj4444ru/php_codesniffer_yii2_githooks
[php_codesniffer_yii2_githooks_packagist]: https://packagist.org/packages/mj4444/php_codesniffer_yii2_githook
[PHP_CodeSniffer]: https://github.com/squizlabs/PHP_CodeSniffer
[phpcbf]: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Fixing-Errors-Automatically
[yii2-coding-standards]: https://github.com/yiisoft/yii2-coding-standards
[setting-the-default-encoding]: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Configuration-Options#setting-the-default-encoding
[ignoring-warnings-when-generating-the-exit-code]: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Configuration-Options#ignoring-warnings-when-generating-the-exit-code
[showing-progress-by-default]: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Configuration-Options#showing-progress-by-default
[using-colors-in-output-by-default]: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Configuration-Options#using-colors-in-output-by-default
[specifying-valid-file-extensions]: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage#specifying-valid-file-extensions
[javascript-the-winning-style]: https://habrahabr.ru/post/189872/
[javascript-the-winning-style-original]: http://seravo.fi/2013/javascript-the-winning-style
