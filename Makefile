.PHONY: install qa cs csf phpstan tests coverage-clover coverage-html

install:
	composer update

qa: phpstan cs

cs:
	vendor/bin/phpcs --standard=ruleset.xml --extensions=php,phpt --tab-width=4 --ignore=temp -sp src tests

csf:
	vendor/bin/phpcbf --standard=ruleset.xml --extensions=php,phpt --tab-width=4 --ignore=temp -sp src tests

phpstan:
	vendor/bin/phpstan analyse -l max -c phpstan.neon --memory-limit=512M src

tests:
	vendor/bin/tester -s -p php --colors 1 -C tests

coverage-clover:
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage ./coverage.xml --coverage-src ./src ./tests

coverage-html:
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage ./coverage.html --coverage-src ./src ./tests
