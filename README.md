# ⚽️ Pimcore Football
A simple demo project built with Pimcore to manage football teams and players.

## Prequites
- [Docker](https://www.docker.com/) installed

## Installation & Setup
- git clone git@github.com:stephanie-reyn/pimcore-football.git
- cd pimcore-football

## Start Pimcore via Docker
docker compose up -d

## Import CVS (Players & Teams)
docker compose exec php php bin/console app:import-teams
