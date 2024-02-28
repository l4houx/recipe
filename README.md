# En cours

# Création d'un multi-site recette/blog de A à Z (CMS WORDPRESS) avec Symfony 7, Yourdomain | Multipurpose Bootstrap 5 HTML Template (Dark/Light) Version 1.1.0

## Environnement de développement

### Prérequis
* PHP 8.2
* Composer
* Symfony CLI
* Docker
* Docker Compose

Vous pouvez vérifier les prérequis (sauf Docker et Docker Compose) avec la commande suivante (depuis Symfony CLI) :

```sh
symfony check:requirements
```

### Lancer l'environnement de développement

Suivant:
A) Exécutez docker docker compose up -d database pour démarrer votre conteneur de base de données
or ```sh docker compose up -d ``` pour les démarrer tous.

B) Exécutez symfony serve -d pour démarrer votre serveur
```sh symfony serve -d ``` commencer

C) Exécuter docker compose stop arrêtera tous les conteneurs dans docker-compose.yaml.
```sh docker compose down ``` arrêtera et détruira les conteneurs.

D) Exécutez symfony serve:stop pour arrêter votre serveur
```sh symfony serve:stop ``` arrêter


### ⚙️ Installation

--------------

## Installez les dépendances PHP
```sh
composer install
```
