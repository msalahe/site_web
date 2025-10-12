# Refactorisation des contrôleurs Type et Projet

## 📋 Vue d'ensemble

Cette refactorisation améliore la structure du code en suivant les principes SOLID et les meilleures pratiques Symfony.

## 🎯 Objectifs atteints

### 1. Séparation des responsabilités (SRP - Single Responsibility Principle)
- Les **contrôleurs** gèrent uniquement les requêtes HTTP et les réponses
- Les **services** contiennent toute la logique métier
- Les **repositories** s'occupent de l'accès aux données

### 2. Architecture mise en place

```
src/
├── Controller/
│   ├── TypeController.php      (Refactorisé)
│   └── ProjetController.php    (Refactorisé)
├── Service/
│   ├── TypeService.php         (Nouveau)
│   ├── ProjetService.php       (Nouveau)
│   └── ImageService.php        (Nouveau)
├── DTO/
│   └── ApiResponse.php         (Nouveau)
└── Entity/
    ├── Type.php
    ├── Projet.php
    └── Image.php
```

## 📦 Nouveaux fichiers créés

### Services

#### **TypeService.php**
Gère toute la logique métier liée aux types de projet :
- `getAllTypes()` - Récupère tous les types
- `getTypeById(int $id)` - Récupère un type par ID
- `createType(string $name)` - Crée un nouveau type
- `updateType(int $id, string $name)` - Met à jour un type
- `deleteType(int $id)` - Supprime un type

#### **ProjetService.php**
Gère toute la logique métier liée aux projets :
- `getAllProjets()` - Récupère tous les projets
- `getProjetById(int $id)` - Récupère un projet par ID
- `getProjetsByType()` - Récupère les projets groupés par type
- `createProjet(...)` - Crée un nouveau projet
- `updateProjet(...)` - Met à jour un projet
- `deleteProjet(int $id)` - Supprime un projet et ses images

#### **ImageService.php**
Gère toute la logique métier liée aux images :
- `uploadImage(UploadedFile $file, Projet $projet)` - Upload une image
- `uploadMultipleImages(array $files, Projet $projet)` - Upload plusieurs images
- `deleteImage(int $id)` - Supprime une image
- `deleteProjectImages(Projet $projet)` - Supprime toutes les images d'un projet

### DTO (Data Transfer Object)

#### **ApiResponse.php**
Standardise les réponses JSON de l'API :
- `success(mixed $data, string $message, int $statusCode)` - Réponse de succès
- `error(string $message, int $statusCode, mixed $errors)` - Réponse d'erreur
- `html(string $html, int $statusCode)` - Réponse HTML (pour modaux)

## ✨ Améliorations apportées

### TypeController

**Avant :**
```php
// Logique métier mélangée dans le contrôleur
// Gestion d'erreur avec dd()
// Réponses JSON incohérentes
// Pas de méthodes HTTP spécifiées
```

**Après :**
```php
// Contrôleur léger, délègue au service
// Gestion d'erreur propre avec try-catch
// Réponses JSON standardisées via ApiResponse
// Méthodes HTTP explicites dans les routes
```

### ProjetController

**Avant :**
```php
// Upload d'images dans le contrôleur
// Code dupliqué
// Gestion manuelle des fichiers
// dd() pour le debugging
```

**Après :**
```php
// Upload délégué à ImageService
// Code réutilisable
// Gestion centralisée des fichiers
// Flash messages pour l'UX
// Gestion d'erreurs cohérente
```

## 🔧 Configuration

Le fichier `config/services.yaml` a été simplifié :

```yaml
parameters:
    photo_dir: 'C:/web_site/site_web_j4r/public/uploads/images'

services:
    _defaults:
        autowire: true
        autoconfigure: true
        bind:
            string $photoDir: '%photo_dir%'
```

Le paramètre `$photoDir` est automatiquement injecté dans tous les services qui en ont besoin.

## 📝 Avantages de cette refactorisation

1. **Testabilité** : Les services peuvent être testés unitairement
2. **Réutilisabilité** : La logique métier peut être utilisée ailleurs (CLI, API, etc.)
3. **Maintenabilité** : Code plus clair et organisé
4. **Évolutivité** : Facile d'ajouter de nouvelles fonctionnalités
5. **Cohérence** : Réponses API standardisées
6. **Sécurité** : Meilleure gestion des erreurs et validation

## 🚀 Utilisation

### Exemple TypeController

```php
// Créer un type
POST /type/new/type/projet
Body: { "service": { "type_projet": "Mon Type" } }

// Modifier un type
POST /type/update/type/projet
Body: { "service": { "idType": 1, "type_projet": "Type modifié" } }

// Supprimer un type
DELETE /type/delete/1
```

### Exemple ProjetController

```php
// Créer un projet avec images
POST /projet/new
Form Data: name, description, type, images[]

// Modifier un projet
POST /projet/{id}/edit
Form Data: name, description, type, images[]

// Supprimer un projet
DELETE /delete/projet/{id}

// Supprimer une image
DELETE /delete/image/{id}
```

## 🔄 Migration

Cette refactorisation est **rétrocompatible**. Les routes et comportements existants sont préservés, seule l'implémentation interne a changé.

## 📚 Prochaines étapes recommandées

1. Ajouter des tests unitaires pour les services
2. Ajouter la validation avec Symfony Validator
3. Implémenter des événements Symfony pour les actions (ProjetCreatedEvent, etc.)
4. Ajouter la pagination pour la liste des projets
5. Créer des endpoints API RESTful (si besoin)
6. Ajouter la gestion de permissions (Voter Symfony)

## 🛠️ Maintenance

Pour ajouter une nouvelle fonctionnalité :

1. Ajouter la méthode dans le **Service** approprié
2. Créer la route dans le **Controller**
3. Utiliser **ApiResponse** pour les réponses JSON
4. Gérer les erreurs avec try-catch
5. Ajouter des tests

---

**Date de refactorisation :** 2025-10-12
**Version Symfony :** 6.0
