.ONESHELL:
.PHONY: help

H1=echo === ${1} ===
TAB=echo "\t"

help:
	@$(call H1,php-sagas-orchestrator)
	$(TAB) make up - build container, install dependencies
	$(TAB) make down - remove dependencies, stop container
	$(TAB) make cli - enter into container shell
