# Summary: Deep Path Mapping Fix for Raw Data Hydration

**Date:** 2026-01-10  
**Branch:** `fix/code/FixAndTest_DeepPathMapping`  
**Commit:** 2ce1487

## Context

L'utilisateur avait un doute sur le fonctionnement du deep path mapping avec les sources de données de type raw data (tableaux associatifs). Les tests ont confirmé ce doute : le deep path ne fonctionnait que pour les sources DTO (via ObjectMapper), pas pour les raw data (via Hydrator).

## Problème Identifié

### Symptôme
Lors de l'hydratation d'un objet depuis un tableau de données brutes, les propriétés avec un `sourceField` utilisant la notation pointée (ex: `address.street`) restaient à `null`.

### Cause Racine
La méthode `hydrateObject()` dans `Loader.php` utilisait un accès direct au tableau :
```php
if (!array_key_exists($fieldName, $data)) { ... }
$value = $data[$fieldName];
```

Ce code cherche une clé littérale `address.street` dans le tableau, au lieu de parcourir `$data['address']['street']`.

## Solution Implémentée

### Nouvelles Méthodes

1. **`fieldExistsInData(string $fieldPath, array $data): bool`**
   - Vérifie si un chemin profond existe dans un tableau
   - Supporte la notation pointée (ex: `address.street.number`)
   - Traverse les tableaux imbriqués

2. **`resolveValueFromData(string $fieldPath, array $data): mixed`**
   - Résout une valeur depuis un tableau en utilisant un chemin profond
   - Retourne la valeur trouvée à la fin du chemin

### Modification de `hydrateObject()`
Remplacement de l'accès direct par les nouvelles méthodes :
```php
// Avant
if (!array_key_exists($fieldName, $data)) { ... }
$value = $data[$fieldName];

// Après
if (!$this->fieldExistsInData($fieldName, $data)) { ... }
$value = $this->resolveValueFromData($fieldName, $data);
```

## Tests Créés

### Structure des Tests
```
tests/Integration/Features/DeepPathMapping/
├── DeepPathMappingTest.php          # 14 tests
└── Fixtures/
    ├── AddressDto.php               # DTO avec rue/ville
    ├── StreetDto.php                # DTO avec nom/numéro de rue
    ├── AddressWithStreetDto.php     # DTO imbriqué (3 niveaux)
    ├── PersonDto.php                # DTO source avec adresse imbriquée
    ├── PersonWithDeepAddressDto.php # DTO source avec 3 niveaux
    ├── PersonWithFlattenedAddress.php      # Cible avec deep path 2 niveaux
    └── PersonWithDeeplyFlattenedAddress.php # Cible avec deep path 3 niveaux
```

### Scénarios Couverts

| Test | DTO (ObjectMapper) | Raw Data (Hydrator) |
|------|-------------------|---------------------|
| Nesting 2 niveaux | ✅ | ✅ (corrigé) |
| Nesting 3 niveaux | ✅ | ✅ (corrigé) |
| Objet/tableau null | ✅ | ✅ (corrigé) |
| Chemin partiellement null | ✅ | ✅ (corrigé) |
| Hydratation objet existant | ✅ | ✅ (corrigé) |

## Résultats

```
Tests: 14, Assertions: 68
OK (14 tests passed)
```

Tous les 214 tests d'intégration passent sans régression.

## Fichiers Modifiés

| Fichier | Changement |
|---------|------------|
| `src/Loader/Loader.php` | +65 lignes (2 méthodes + modification hydrateObject) |

## Fichiers Ajoutés

| Fichier | Description |
|---------|-------------|
| `tests/Integration/Features/DeepPathMapping/DeepPathMappingTest.php` | 403 lignes, 14 tests |
| `tests/Integration/Features/DeepPathMapping/Fixtures/*.php` | 7 fixtures |

## Impact

- **Breaking Changes:** Aucun
- **Comportement:** Le `#[Property(sourceField: 'deep.path')]` fonctionne maintenant comme documenté avec le Hydrator
- **Performance:** Impact négligeable (traversée de tableaux pour les chemins profonds uniquement)
