# Architecture — DataMapper (PHP Library)

## Overview

The project is an hydration/lazy-loading library based on **PHP 8 attributes**: the domain object declares *what to load* and *how* via attributes (DataSource, DataSourceRef, Context, hooks…), and a **Loader** applies these rules at the appropriate time (lazy via getters or eager via `Loading::TYPE_EAGER`).

The runtime is intentionally lightweight and decoupled from any framework: Symfony integration is primarily achieved through PSR compliance (PSR-11 container, PSR-3 logger, PSR-16 cache) and a `ServiceResolver` that can resolve services from multiple sources.

## Main Modules (Packages)

### 1) Entry Point / Configuration
- **`DataMapperBuilder`**: assembles the service resolution strategy (container, locators, factories…) and builds a `DataMapper`.
- **`DataMapper`**: runtime facade.
  - Instantiates a `Loader` and registers it globally via `LoaderRegistry`.
  - Manages **application context** (`addToContext`, `addManyToContext`), and **lineage collection** activation (`enableLineageCollection`).

### 2) Hydration / Execution
- **`Loader`**: execution core.
  - Triggers property hydration (`loadProperty`) or group hydration (`MultiPropDataSource`).
  - Reads attribute metadata via `Metadata\AttributeReader`.
  - Resolves and executes DataSources via `ServiceResolver`.
  - Applies recursion (hydrating nested objects), priority selection, hooks, context updates, and anti-overwrite via locking.

### 3) Declaration (Attributes)
- **`Attribute/*`**: set of attributes configuring the hydration strategy.
  - `DataSourcesStore` (class level): local source registry.
  - `DataSourceRef` (property level): reference to a stored source.
  - `SinglePropDataSource` / `MultiPropDataSource`: direct sources.
  - `Context`: injection of contextual values during hydration.
  - `PropertyConfigStore` / `PropertyConfig`: reusable property configurations for polymorphic hydration.
  - `Needs`, `Loading`, hooks (`PropertySettingHook`, etc.).
  - `Param`: parameter injection for constructors, getters, and setters.

### 4) Metadata
- **`Metadata\AttributeReader`**: encapsulates Reflection + attribute reading and provides uniform access to the Loader.

### 5) Registries (Global State, Loose Coupling)
- **`LoaderRegistry`**: global storage of the active Loader (allows `LoadableTrait` to remain simple).
- **`ContextRegistry`**: stores two context sources:
  - *application context* (persistent, defined via `DataMapper`),
  - *hydration context* (defined via `#[Context]`, takes priority over application).
- **`LockedPropertyRegistry`**: property locking via `WeakMap` (state external to the object → avoids unintended serialization).

### 6) Expressions
- **`Expression\ExpressionParser`**: resolves "dynamic" arguments (property references `#prop`, expressions `expr(...)`, access `context('k')`, `source('id')`, `service('id')`, etc.).
- **`Expression\SourceFunctionProvider`**: executes and optionally caches source results `source('id')`.

### 7) Observability
- **`DataCollector\DataLineageCollector` + `LineageEvent`**: optional event collection (DataSource calls, hydrations, skips, hooks, context set…). The Loader calls the collector when enabled.

## Data Flow (Runtime)

### Typical "Lazy Load" Scenario
1. A getter on the domain object calls `loadProperty('x')` (via `LoadableTrait`).
2. `LoadableTrait` retrieves the global `Loader` via `LoaderRegistry`.
3. `Loader::loadProperty()`:
   - checks locking (`LockedPropertyRegistry` / `isPropertyLocked()`),
   - checks if the property is already loaded,
   - reads property attributes (`AttributeReader`).
4. Source resolution:
   - `DataSourceRef` → lookup in `DataSourcesStore` (class level),
   - or direct source (Single/MultiProp) on the property.
5. Source execution: the Loader uses `ServiceResolver` to instantiate/retrieve the source class then calls the method.
   - If args are present, `ExpressionParser` can evaluate `expr(...)`, `context(...)`, `source(...)`, etc.
6. Hydration: optional mapping, recursion, hooks, context update (`ContextRegistry`), then property writing (setter or direct access depending on strategy).
7. "Loaded" marking and, if enabled, event recording in `DataLineageCollector`.

### Key Design Points
- **Decoupling** via PSR and registries: the domain doesn't need to know the concrete Loader.
- **External state** (locks via WeakMap) to avoid polluting objects.
- **Two-level context** (application vs hydration) to allow environment-driven expressions.
- **Built-in observability** without impacting the critical path when disabled.
