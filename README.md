# Api EcoGarden

## Description

EcoGarden est une api donnant des conseils de jardinage pour bien cultiver 
les plantes, en fonction de la période de l'année.
Il s'agit d'une première version.

## Prérequis
- PHP 8.4
- Symfony 7.4.X (il sera installé via Composer)
- autres dépendances php et symfony installées via Composer

## Installation du projet

### 1. Cloner le projet

```bash
git clone https://github.com/Vivien60/Formation_OC_Symfony_P12_API_EcoGarden.git
cd Formation_OC_Symfony_P12_API_EcoGarden
composer install
```
créez votre fichier environnement (.env*)

### 2. Configuration de la base de données

#### 2.1 Créer la base de données
- spécifiez le connecteur à votre base de données dans votre .env
-  Puis :
    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    ```
- créez une clé API OpenWeatherMap si vous n'en avez pas, puis spécifiez là (OPENWEATHERMAP_API_KEY) dans votre .env
- mettre en place une config JWT : le bundle utilisé est lexik/jwt-authentication-bundle

#### 2.2 Générez des données
Des fixtures ont été créées pour générer des données aléatoires
- `php bin/console doctrine:fixtures:load`

## 3. Structure du projet

### Structure du projet
Classique Symfony, en skeleton : un minimum de bundles chargés

### Fonctionnalités

- Inscription et gestion d'utilisateurs (update, delete)
- Gestion des conseils de jardinage
- Météo du jour pour une ville donnée
- Listing des conseils pour un mois donnés, avec pagination

### Technologies utilisées

- **Backend** : PHP 8.4 avec Symfony 7.4.7
- **Base de données** : MySQL
- **Versionning** : Git


Ce projet est développé dans le cadre du parcours OpenClassrooms "Développeur d'application PHP/Symfony".