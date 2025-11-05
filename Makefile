.PHONY: analyse cs-fixer-fix cs-fixer-check help

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

analyse: ## Run PHPStan static analysis
	./vendor/bin/phpstan analyse

cs-fixer-fix: ## Fix code style issues with PHP-CS-Fixer
	./vendor/bin/php-cs-fixer fix

cs-fixer-check: ## Check code style issues with PHP-CS-Fixer (dry-run)
	./vendor/bin/php-cs-fixer fix --dry-run --diff
