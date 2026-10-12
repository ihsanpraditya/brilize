.PHONY: build zip clean

ZIP_DEPLOY=deploy.zip
DOCKER_CONTAINER=brilize-database-1
DB_USER=app
DB_PASSWORD=!ChangeMe!
DB_NAME=app
DB_EXPORT_FILE=database_migration.sql

help:
	@echo "=================================================="
	@echo "  PERINTAH MAKEFILE DEPLOYMENT SYMFONY"
	@echo "=================================================="
	@echo "  make build      - Build frontend assets & production dependencies"
	@echo "  make db-export  - Export database PostgreSQL dari Docker"
	@echo "  make zip-deploy - Jalankan build, export DB, dan buat deploy.zip"
	@echo "  make clean      - Bersihkan file dump SQL & kembalikan composer dev"
	@echo "  make help       - Menampilkan panduan bantuan ini"
	@echo "=================================================="

build:
	@echo "Building frontend assets..."
	bun run build
	@echo "Installing production vendor dependencies..."
	composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
	@echo "Build complete!"

db-export:
	@echo "Exporting PostgreSQL database from Docker ($(DOCKER_CONTAINER))..."
	docker exec -t -e PGPASSWORD='$(DB_PASSWORD)' $(DOCKER_CONTAINER) pg_dump -U $(DB_USER) $(DB_NAME) > $(DB_EXPORT_FILE)
	@echo "Database exported to $(DB_EXPORT_FILE)!"

zip-deploy: build db-export
	@echo "Creating deployment archive..."
	@zip -r $(ZIP_DEPLOY) . \
		-x "*.git*" \
		-x "node_modules/*" \
		-x ".env" \
		-x ".env.local" \
		-x "var/cache/*" \
		-x "var/log/*" \
		-x "Makefile"
	@echo "Archive 'deploy.zip' successfully created with PostgreSQL dump!"

clean:
	@echo "Cleaning up local build artifacts..."
	rm -f $(DB_EXPORT_FILE)
	composer install --no-interaction
	@echo "Done!"
