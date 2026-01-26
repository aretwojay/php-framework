# CMS Headless PHP – Projet Semestriel 3A

Projet réalisé dans le cadre du **Projet Semestriel 3A – Bloc 2 (RNCP)**.
Le projet repose sur un Framework construit en cours de classe , conteneurisé avec Docker, sans dépendances tierces.

---

## Fonctionnalités principales

### Framework
- Routing dynamique (GET / POST / PUT / DELETE)
- Gestion des requêtes et réponses HTTP
- ORM léger basé sur PDO
- Génération automatique du schéma de base de données
- Commandes CLI (`CreateDatabase`, `CreateSchema`)
- Moteur de templates simple

### CMS Headless
- Authentification (login / register / logout)
- CRUD des contenus (posts)
- Back-office d’administration
- API JSON en lecture seule

---

## Prérequis
- Docker
- Git

---

## Installation

### 1. Cloner le dépôt
git clone https://github.com/aretwojay/php-framework.git
cd php-framework
2. Lancer l’environnement Docker
docker compose up -d --build
3. Vérifier les containers
docker ps

---

## Containers attendus :

cms_decode (PHP / Apache)

cms_db (MySQL)

cms_phpmyadmin (optionnel)

---

## Initialisation de la base de données
docker exec -it cms_decode bash
php bin/console.php -c CreateDatabase
php bin/console.php -c CreateSchema

Ces commandes génèrent automatiquement le schéma à partir des entités PHP.

---

## Accès à l’application

Authentification / Admin : http://localhost:8080/login

API JSON (headless) : http://localhost:8080/api/posts

phpMyAdmin : http://localhost:8081

---

## Sécurité:

PDO avec requêtes préparées

Protection CSRF

Mots de passe hashés

Sessions sécurisées

---

## API

Une API JSON en lecture seule est disponible pour la consultation des contenus.

Documentation détaillée : API.md
