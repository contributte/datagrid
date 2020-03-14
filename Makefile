.PHONY: qa lint cs csf phpstan tests coverage

all:
	@$(MAKE) -pRrq -f $(lastword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/^# File/,/^# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$' | xargs

vendor: composer.json composer.lock
	composer install

qa: lint phpstan cs

lint: vendor
	vendor/bin/linter src tests

cs: vendor
	vendor/bin/phpcs --standard=vendor/ninjify/coding-standard/ruleset-gamee.xml --extensions=php,phpt --tab-width=4 --ignore=temp -sp src tests

csf: vendor
	vendor/bin/phpcbf --standard=vendor/ninjify/coding-standard/ruleset-gamee.xml --extensions=php,phpt --tab-width=4 --ignore=temp -sp src tests

phpstan: vendor
	vendor/bin/phpstan analyse -l max -c phpstan.neon src

tests: vendor
	vendor/bin/tester -s -p php --colors 1 -C tests

coverage: vendor
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage ./coverage.xml --coverage-src ./src ./tests
