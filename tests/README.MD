## If PHPUNIT is installed correctly, tests can be run with the following command:
vendor/bin/phpunit .\tests\Unit\path\to\file(s)

### If you want to run all tests:
vendor/bin/phpunit .\tests\Unit\

### If you want to run tests only for a specific plugin:
vendor/bin/phpunit .\tests\Unit\LifePerformancesTests\
vendor/bin/phpunit .\tests\Unit\JamSessionTests\

### If you want to run only one specific test:
ex: vendor/bin/phpunit .\tests\Unit\LifePerformancesTests\SendEmailToAdminTest.php