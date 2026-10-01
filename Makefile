.DEFAULT_GOAL := help
PS := soczewki24_ps
THEME := soczewki24

help: ## Lista komend
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-16s\033[0m %s\n", $$1, $$2}'

up: ## Start srodowiska
	docker compose up -d
	@echo "Sklep:    http://localhost:8090"
	@echo "Admin:    http://localhost:8090/adminsoczewki"
	@echo "Adminer:  http://localhost:8081"
	@echo "Mailpit:  http://localhost:8025"

down: ## Stop srodowiska
	docker compose down

reset: ## Stop + kasowanie wolumenow (czysta instalacja od zera)
	docker compose down -v

logs: ## Logi kontenera Presty
	docker compose logs -f prestashop

shell: ## Bash w kontenerze Presty
	docker compose exec prestashop bash

# bin/console ZAWSZE jako www-data. Odpalony jako root zostawia w var/cache katalogi
# nalezace do roota, przez co Apache (www-data) nie moze pisac i BO wywala 500.
cc: ## Czyszczenie cache Presty
	docker compose exec -u www-data prestashop php bin/console cache:clear --no-warmup
	docker compose exec -u www-data prestashop rm -rf var/cache/dev var/cache/prod

console: ## bin/console jako www-data, np. make console CMD="prestashop:theme:enable soczewki24"
	docker compose exec -u www-data prestashop php bin/console $(CMD)

theme-enable: ## Aktywacja motywu soczewki24 w sklepie
	docker compose exec -u www-data prestashop php bin/console prestashop:theme:enable $(THEME)

module-install: ## Instalacja modulu projektowego, np. make module-install MOD=croco_soczewki
	docker compose exec -u www-data prestashop php bin/console prestashop:module install $(MOD)

theme-install: ## npm install w motywie
	cd themes/$(THEME) && npm ci

theme-watch: ## Webpack w trybie watch
	cd themes/$(THEME) && npm run watch

theme-lint: ## Stylelint + eslint w motywie
	cd themes/$(THEME) && npm run stylelint && npm run lint

theme-storybook: ## Storybook na porcie 6006
	cd themes/$(THEME) && npm run storybook

theme-build: ## Produkcyjny build assetow
	cd themes/$(THEME) && npm run build

db-pull: ## Pobranie i anonimizacja bazy z produkcji
	./bin/pull-db.sh

db-dump: ## Lokalny dump bazy do bin/local.sql
	docker compose exec -T mysql mysqldump -uprestashop -pprestashop prestashop > bin/local.sql

lint: ## PHP lint na naszych modulach i override
	docker compose exec prestashop sh -c 'find modules/croco_* override -name "*.php" -exec php -l {} \;'

.PHONY: help up down reset logs shell cc console theme-enable module-install theme-install theme-watch theme-build theme-lint theme-storybook db-pull db-dump lint
