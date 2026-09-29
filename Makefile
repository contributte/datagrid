.DEFAULT_GOAL := help

##@ Help

.PHONY: help
help: ## Show this help
	@awk 'BEGIN {FS = ":.*##"; printf "Usage: make \033[36m<target>\033[0m\n"} /^[a-zA-Z0-9_.-]+:.*##/ { sub(/^ +/, "", $$2); printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) }' $(firstword $(MAKEFILE_LIST))

##@ Project

.PHONY: install
install: ## Install dependencies
	composer update

##@ QA

.PHONY: qa
qa: phpstan cs ## Run all QA checks

.PHONY: cs
cs: ## Check code style
ifdef GITHUB_ACTION
	vendor/bin/phpcs --standard=ruleset.xml --extensions=php,phpt --tab-width=4 --ignore=tests/tmp -q --report=checkstyle src tests | cs2pr
else
	vendor/bin/phpcs --standard=ruleset.xml --extensions=php,phpt --tab-width=4 --ignore=tests/tmp --colors -nsp src tests
endif

.PHONY: csf
csf: ## Fix code style
	vendor/bin/phpcbf --standard=ruleset.xml --extensions=php,phpt --tab-width=4 --ignore=tests/tmp --colors -nsp src tests

.PHONY: phpstan
phpstan: ## Run static analysis
	vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=512M

.PHONY: tests
tests: ## Run tests
	vendor/bin/tester -s -p php --colors 1 -C tests/Cases

.PHONY: coverage
coverage: ## Generate code coverage
ifdef GITHUB_ACTION
	vendor/bin/tester -s -p php --colors 1 -C --coverage coverage.xml --coverage-src src tests/Cases
else
	vendor/bin/tester -s -p php --colors 1 -C --coverage coverage.html --coverage-src src tests/Cases
endif
