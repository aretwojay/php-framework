# PHP Framework

Guide pour suivre étape par étape la création d'un framework simple en PHP.

Ce framework se base sur le modèle MVC et s'inspire de ce que peuvent faire Symfony et Laravel. Il sera composé d'un routeur, d'un ORM et d'un moteur de template simple.

On pourra utiliser ce framework pour créer des API ou des applications monolithes.

## Sommaire

<!--toc:start-->
- [PHP Framework](#php-framework)
  - [Sommaire](#sommaire)
  - [Étape 0](#étape-0)
  - [Étape 1](#étape-1)
  - [Étape 2](#étape-2)
  - [Étape 3](#étape-3)
  - [Étape 4](#étape-4)
  - [Étape 5](#étape-5)
  - [Étape 6](#étape-6)
  - [Étape 7](#étape-7)
  - [Étape 8](#étape-8)
  - [Étape 9](#étape-9)
  - [Étape 10](#étape-10)
  - [Étape 11](#étape-11)
  - [Étape 12](#étape-12)
  - [Étape 13](#étape-13)
  - [Étape 14](#étape-14)
<!--toc:end-->

## Étape 0

**Objectif** : initialiser le projet

- créer un fichier `docker-compose.yml` et un fichier `Dockerfile` avec les configurations nécessaires pour développer une application en PHP
- ajouter un fichier `app/index.php` pour pouvoir tester si le conteneur est valide

## Étape 1

**Objectif** : rediriger toutes les requêtes vers `app/src/index.php`

- pour pouvoir préparer le routeur, on a besoin de rediriger toutes les requêtes vers une entrée unique
- créer un dossier `app/src` : il contiendra l'intégralité du code PHP du framework
- y déplacer le fichier `index.php`
- créer un fichier `app/.htaccess` pour y écrire les règles de redirection

## Étape 2

**Objectif** : récupérer les informations de la requête pour qu'on puisse la traiter

- modifier le fichier `app/src/index.php` avec les fonctions nécessaires 

## Étape 3

**Objectif** : extraire le code précédent dans une classe

- créer un dossier `app/src/Http` : il contiendra toutes les fonctionnalités du framework liées aux requêtes HTTP
- y ajouter un fichier `app/src/Http/Request.php` avec le code d'`index.php`
- lancer la commande `composer init` pour pouvoir utiliser l'`autoload.php` de composer et charger dynamiquement les classes

## Étape 4

**Objectif** : préparer le routeur

- créer un dossier `app/config` : il contiendra tous les fichiers de configuration nécessaires au fonctionnement du framework
- y ajouter un fichier `app/config/routes.json` : on y enregistrera les configurations de nos endpoints
- modifier le fichier `app/src/index.php` pour accorder l'accès ou non aux endpoints en fonction du fichier `app/config/routes.json`

## Étape 5

**Objectif** : compléter le routeur avec une classe dédiée

- créer un fichier `app/src/Http/Router.php`
- y déplacer le code de `app/src/index.php`

## Étape 6

**Objectif** : exécuter un controller en fonction de l'endpoint

- créer un dossier `app/src/Controllers` : on y stockera les controllers
- créer un fichier `app/src/Controllers/AbstractController.php` : il servira de modèle à tous les futurs controllers
- adapter `app/src/Http/Router.php` pour qu'il instancie une classe controller et l'exécute

## Étape 7

**Objectif** : renvoyer une objet Response

- créer un fichier `app/src/Http/Response.php` avec la configuration pour une réponse HTTP
- faire en sorte que les controllers et le routeur retournent une instance de `Response.php`

## Étape 8

**Objectif** : permettre la connexion à une database

- modifier le fichier `docker-compose.yml` pour y inclure un service MySQL
- créer un dossier `app/src/Database` pour y stocker les fonctionnalités liées à la connexion à la base de données
- y ajouter un fichier `app/src/Database/DatabaseConnexion.php` pour créer une connexion à la bdd
- y ajouter un fichier `app/src/Database/Dsn.php` pour paramétrer la connexion à la bdd
- ajouter un fichier de configuration `app/config/database.json` pour y stocker les informations de connexion à la bdd
  - **!** ce fichier ne doit pas être commit dans un vrai projet

## Étape 9

**Objectif** : permettre de lancer des commandes dans le terminal

- sera utile pour créer la base de données par exemple
- créer un fichier `app/bin/console.php` qui fonctionnera comme un routeur mais dans le terminal pour les commandes
  - les commandes devront être lancées avec `php console.php -c <nom-de-la-commande>`
- créer un dossier `app/src/Commands` pour y stocker toutes les futures commandes
- créer un fichier `app/src/Commands/AbstractCommand.php` qui se basera sur le `Command pattern` et servira de base à toutes les futures commandes
- créer un fichier `app/src/Commands/CreateDatabase.php`

## Étape 10

**Objectif** : séparer les classes métiers des classes techniques

- toutes les classes liées au fonctionnement du framework doivent aller dans un dossier `app/src/Lib`
- toutes les autres restent dans `app/src`

 ## Étape 11

**Objectif** : permettre de lancer une commande pour créer le schéma de base de données

- créer un dossier `app/src/Lib/Entities` : on y stockera la classe abstraite pour définir des entités
- créer un fichier `app/src/Lib/Entities/AbstractEntity.php` : il servira de modèle à toutes les futures entités
- créer un dossier `app/src/Lib/Annotations/ORM` : on y stockera les attributs/annotations qui permettront de configurer les propriétés des entités
- créer un fichier `app/src/Lib/Commands/CreateSchema.php`

## Étape 12

**Objectif** : permettre à la création de schéma de base de gérer les foreign key

- ajouter une annotation `app/src/Lib/Annotations/ORM/References.php`
- adapter `app/src/Lib/Commands/CreateSchema.php` pour organiser les tables à créer en fonction des dépendances et rajouter les contraintes de FOREIGN KEY

## Étape 13

**Objectif** : ajouter un système de fonctions PHP pour construire une requête SQL

- créer une classe `app/src/Lib/Repositories/AbstractRepository.php` : elle servira de base à tous les autres repositories

## Étape 14

**Objectif** : permettre au controller de renvoyer une vue HTML

- adapter `app/src/Lib/Controllers/AbstractController.php` pour utiliser un template html et l'hydrater avant de la renvoyer dans la réponse
- adapter `app/src/Lib/Http/Request.php` et `app/src/Lib/Http/Router.php` pour permettre de récupérer les slugs dans l'url
