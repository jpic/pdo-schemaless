PDO decorator which makes MySQL work normally, without the need if predefined strutures.

## Installing madPDO

Runtime code is maintained against PHP 5.2.6 and PHP 5.2.12.

Get the repo with:

    git clone http://github.com/jpic/pdo-schemaless.git

Then include:

    pdo-schemaless/pdo.php

Then instanciate madPDO, ie:

    $pdo = new madPDO( 'mysql:dbname=testdb;host=localhost', 'root' );

Now MySQL forgot it needed schemas.

## Testing madPDO

Test code needs PHPUnit 3.5 (sorry) and PHP 5.3.2 (sorry).

### Installing PHP 5.3.2 for testing

Install PHP from sources (sorry).
Install APC from sources (sorry).
Install XDebug >= 2.0.5 from sources (sorry).

If using the new mysqlnd driver, don't forget to add to your mysql init script something like:

    # sorry, that is broken as well
    ln -sfn /var/run/mysqld/mysqld.sock /tmp/mysql.sock

### Installing PHPUnit 3.5

Sorry, this needs PHPUnt 3.5 (sorry), install with this bash commands:

    cd /usr/src
    
    git clone http://github.com/sebastianbergmann/phpunit.git
    git clone http://github.com/sebastianbergmann/php-code-coverage.git
    git clone http://github.com/sebastianbergmann/php-file-iterator.git
    git clone http://github.com/sebastianbergmann/php-text-template.git
    git clone http://github.com/sebastianbergmann/php-token-stream.git
    git clone http://github.com/sebastianbergmann/php-timer.git
    
    cd /usr/bin
    ln -sfn /usr/src/phpunit/phpunit.php phpunit

Add to php.ini:

    include_path = ".:/usr/share/php5:/usr/share/php:/usr/src/phpunit:/usr/src/php-code-coverage:/usr/src/php-file-iterator:/usr/src/php-text-template:/usr/src/php-token-stream:/usr/src/php-timer"

If lucky, and not yet exhausted, run the test suite:

    cd pdo-schemaless/tests
    phpunit suite.php

## Credits

[Vertical attribute tables](http://www.igvita.com/2010/03/01/schema-free-mysql-vs-nosql/)
[Originnal SQL tokenizer](http://www.tehuber.com/article.php?story=20081016164856267)
