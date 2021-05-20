.ONESHELL:
PHONY=up down cli

DC=docker-compose
DC_EXEC=${DC} exec
DC_EXEC_CONTAINER=${DC_EXEC} php-sagas-orchestrator

up:
	UID=1000 DOCKER_USER=phpuser ${DC} up -d --force-recreate --build
	${DC_EXEC_CONTAINER} composer install
	${DC_EXEC_CONTAINER} chown -R $(shell id -u):$(shell id -u) .
down:
	${DC_EXEC_CONTAINER} rm -Rf vendor
	${DC} down
cli:
	${DC_EXEC} --user=1000 php-sagas-orchestrator sh
