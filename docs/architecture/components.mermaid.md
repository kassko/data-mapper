# Composants principaux — DataMapper (Mermaid)

## Rôles
- **DataMapperBuilder** : assemble la résolution de services et construit le runtime.
- **DataMapper** : façade; instancie le `Loader`, gère contexte applicatif + lineage.
- **Loader** : exécute l’hydratation (lazy/eager), applique règles, hooks, contexte.
- **AttributeReader** : lit les attributs PHP (métadonnées) via Reflection.
- **ServiceResolver** : résout une DataSource/service (PSR-11, locators, factories…).
- **ExpressionParser** : évalue `expr(...)`, `context(...)`, `source('id')`, `service('id')`.
- **SourceFunctionProvider** : exécute (et optionnellement cache) `source('id')`.
- **Registries** : état global minimal (Loader, Context, Locked properties).
- **LoadableTrait** : côté domaine; déclenche le lazy-load en appelant le Loader global.
- **DataLineageCollector** : collecte d’événements (si activée).

## Explication des liens
- **DataMapperBuilder → ServiceResolver** : construit la stratégie de résolution (container/locators/factories).
- **DataMapperBuilder → DataMapper** : crée le runtime qui orchestre le reste.
- **DataMapper → Loader** : instancie le cœur d’exécution (avec logger/cache/collector).
- **DataMapper → LoaderRegistry** : enregistre le Loader global pour que les objets métiers puissent déclencher des chargements sans dépendance directe.
- **LoadableTrait → LoaderRegistry** : récupère le Loader actif pour appeler `loadProperty(...)`.
- **LoadableTrait → LockedPropertyRegistry** : verrouille/déverrouille des propriétés pour empêcher l’écrasement par hydratation.
- **Loader → AttributeReader** : lit `DataSourceRef`, `Context`, `Needs`, hooks… pour décider comment hydrater.
- **Loader → ServiceResolver** : obtient l’instance de DataSource/service à exécuter.
- **Loader → ExpressionParser** : résout les arguments dynamiques (références propriétés, contexte, sources, services).
- **ExpressionParser → ContextRegistry** : lit le contexte (applicatif + hydratation) utilisé dans les expressions.
- **ExpressionParser → SourceFunctionProvider** : exécute/récupère le résultat `source('id')`.
- **ExpressionParser → ServiceResolver** : résout `service('id')` lorsqu’une expression le demande.
- **Loader → ContextRegistry** : écrit le contexte d’hydratation issu des attributs `#[Context]`.
- **Loader → LockedPropertyRegistry** : vérifie le verrouillage avant toute hydratation (skip si verrouillé).
- **Loader → DataLineageCollector** : enregistre les événements (calls DataSource, hydrations, skips…) si la collecte est activée.

## Diagramme Mermaid
```mermaid
flowchart LR
  %% Main facade
  Builder[DataMapperBuilder]
  DM[DataMapper]

  %% Core execution
  Loader[Loader]
  Attr[Metadata/AttributeReader]
  Resolver[ServiceResolver]

  %% Domain integration
  Domain[Domain Object]
  Loadable[LoadableTrait]

  %% Registries
  LoaderReg[Registry/LoaderRegistry]
  CtxReg[Registry/ContextRegistry]
  LockReg[Registry/LockedPropertyRegistry]

  %% Expressions
  Expr[ExpressionParser]
  SourceFP[SourceFunctionProvider]

  %% Observability
  Lineage[DataLineageCollector]

  %% Wiring
  Builder -->|func_build| Resolver
  Builder -->|func_build| DM
  DM -->|creates| Loader
  DM -->|registers| LoaderReg
  DM -->|owns/enables| Lineage

  Domain -->|uses| Loadable
  Loadable -->|func_get| LoaderReg
  Loadable -->|func_loadProperty| Loader
  Loadable -->|lock/unlock/isLocked| LockReg

  Loader -->|read attributes| Attr
  Loader -->|resolve DS/services| Resolver
  Loader -->|evaluate args| Expr
  Loader -->|set/get hydration context| CtxReg
  Loader -->|check locks| LockReg
  Loader -->|record events| Lineage

  Expr -->|func_context| CtxReg
  Expr -->|func_source_param_id| SourceFP
  Expr -->|func_service_param_id| Resolver
```
