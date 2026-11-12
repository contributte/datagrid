.PHONY: install qa dresscode dresscode-fix phpstan tests coverage playwright

DRESSCODE = temp/dresscode/bin/dresscode

install:
	composer update

qa: phpstan dresscode

dresscode: $(DRESSCODE)
	$(DRESSCODE) check

dresscode-fix: $(DRESSCODE)
	$(DRESSCODE) fix

$(DRESSCODE):
	composer create-project dresscode/dresscode temp/dresscode "^1.4" --no-dev --no-interaction --no-progress

phpstan:
	vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=512M

tests:
	vendor/bin/tester -s -p php --colors 1 -C tests/Cases

coverage:
ifdef GITHUB_ACTION
	vendor/bin/tester -s -p php --colors 1 -C --coverage coverage.xml --coverage-src src tests/Cases
else
	vendor/bin/tester -s -p php --colors 1 -C --coverage coverage.html --coverage-src src tests/Cases
endif

playwright:
	npm run test:browser
