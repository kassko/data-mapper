# Architecture — DataMapper (librairie PHP)

## Vue d’ensemble

Le projet est une librairie d’hydratation/lazy-loading orientée **attributs PHP 8** : l’objet métier déclare *quoi charger* et *comment* via des attributs (DataSource, DataSourceRef, Context, hooks…), et un **Loader** applique ces règles au moment opportun (lazy via getters ou eager via `Loading::TYPE_EAGER`).

Le runtime est volontairement léger et découplé d’un framework : l’intégration Symfony se fait principalement via la conformité PSR (PSR-11 container, PSR-3 logger, PSR-16 cache) et un `ServiceResolver` qui sait résoudre des services depuis plusieurs sources.

## Modules (packages) principaux

### 1) Point d’entrée / configuration
- **`DataMapperBuilder`** : assemble la stratégie de résolution de services (container, locators, factories…) et construit un `DataMapper`.
- **`DataMapper`** : façade runtime.
  - Instancie un `Loader` et l’enregistre globalement via `LoaderRegistry`.
  - Gère le **contexte applicatif** (`addToContext`, `addManyToContext`), et l’activation de la **collecte de lineage** (`enableLineageCollection`).

### 2) Hydratation / exécution
- **`Loader`** : cœur de l’exécution.
  - Déclenche l’hydratation d’une propriété (`loadProperty`) ou d’un groupe (`MultiPropDataSource`).
  - Lit la “métadonnée attributs” via `Metadata\AttributeReader`.
  - Résout et exécute les DataSources via `ServiceResolver`.
  - Applique la récursivité (hydrater des objets imbriqués), la sélection par priorité, les hooks, la mise à jour du contexte et l’anti-écrasement via verrouillage.

### 3) Déclaration (attributs)
- **`Attribute/*`** : ensemble des attributs configurant la stratégie d’hydratation.
  - `DataSourcesStore` (niveau classe) : registre local des sources.
  - `DataSourceRef` (niveau propriété) : référence vers une source stockée.
  - `SinglePropDataSource` / `MultiPropDataSource` : sources directes.
  - `Context` : injection de valeurs contextuelles pendant l’hydratation.
  - `PropertyConfigStore` / `PropertyConfig` : configurations de propriété réutilisables pour l'hydratation polymorphe.
  - `Needs`, `Loading`, hooks (`PropertySettingHook`, etc.).

### 4) Métadonnées
- **`Metadata\AttributeReader`** : encapsule Reflection + lecture des attributs et fournit un accès uniforme au Loader.

### 5) Registres (état global, faible couplage)
- **`LoaderRegistry`** : stockage global du Loader actif (permet à `LoadableTrait` de rester simple).
- **`ContextRegistry`** : stocke deux sources de contexte :
  - *application context* (persistant, défini via `DataMapper`),
  - *hydration context* (défini via `#[Context]`, priorité sur l’application).
- **`LockedPropertyRegistry`** : verrouillage de propriété via `WeakMap` (état externe à l’objet → évite la sérialisation involontaire).

### 6) Expressions
- **`Expression\ExpressionParser`** : résout des arguments "dynamiques" (références de propriétés `#prop`, expressions `expr(...)`, accès `context('k')`, `source('id')`, `service('id')`, etc.).
- **`Expression\SourceFunctionProvider`** : exécute et éventuellement cache les résultats de sources `source('id')`.

### 7) Observabilité
- **`DataCollector\DataLineageCollector` + `LineageEvent`** : collecte optionnelle d’événements (calls DataSource, hydrations, skips, hooks, context set…). Le Loader appelle le collecteur si activé.

## Flux de données (runtime)

### Scénario "lazy load" typique
1. Un getter côté objet métier appelle `loadProperty('x')` (via `LoadableTrait`).
2. `LoadableTrait` récupère le `Loader` global via `LoaderRegistry`.
3. `Loader::loadProperty()` :
   - vérifie le verrouillage (`LockedPropertyRegistry` / `isPropertyLocked()`),
   - vérifie si la propriété est déjà chargée,
   - lit les attributs de la propriété (`AttributeReader`).
4. Résolution de la source :
   - `DataSourceRef` → lookup dans `DataSourcesStore` (niveau classe),
   - ou source directe (Single/MultiProp) sur la propriété.
5. Exécution de la source : le Loader utilise `ServiceResolver` pour instancier/récupérer la classe source puis appelle la méthode.
   - Si des args sont présents, `ExpressionParser` peut évaluer `expr(...)`, `context(...)`, `source(...)`, etc.
6. Hydratation : mapping éventuel, récursivité, hooks, mise à jour du contexte (`ContextRegistry`), puis écriture de la propriété (setter ou accès direct selon stratégie).
7. Marquage “loaded” et, si activé, enregistrement des events dans `DataLineageCollector`.

### Points clés de conception
- **Découplage** via PSR et registres : le domaine n’a pas besoin de connaître le Loader concret.
- **État externe** (locks via WeakMap) pour éviter la pollution des objets.
- **Contexte à deux niveaux** (application vs hydration) pour permettre des expressions pilotées par l’environnement.
- **Observabilité** intégrée sans impacter le chemin critique quand désactivée.
