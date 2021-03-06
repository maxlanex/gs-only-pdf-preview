#!/usr/bin/env bash

set -ex

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}

rm -rf cov
mkdir -p cov

rm -rf suhosin7
git clone https://github.com/sektioneins/suhosin7
cd suhosin7
phpize
./configure
make
cd ..

WP_TESTS_DIR=$WP_TESTS_DIR phpunit --exclude-group gopp-uninstall --coverage-php cov/main.cov
WP_TESTS_DIR=$WP_TESTS_DIR phpunit --group gopp-uninstall --coverage-php cov/uninstall.cov

WP_TESTS_DIR=$WP_TESTS_DIR PHPRC=ini_disable_functions phpunit --exclude-group gopp-uninstall --coverage-php cov/disable_functions.cov
WP_TESTS_DIR=$WP_TESTS_DIR PHPRC=ini_suhosin7 phpunit --exclude-group gopp-uninstall --coverage-php cov/suhosin7.cov

wget https://phar.phpunit.de/phpcov.phar

php phpcov.phar merge cov --clover clover.xml && bash <(curl -s https://codecov.io/bash)

rm -rf suhosin7
