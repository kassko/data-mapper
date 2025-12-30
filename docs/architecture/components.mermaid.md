# Main Components — DataMapper (Mermaid)

## Roles
- **DataMapperBuilder**: assembles service resolution and builds the runtime.
- **DataMapper**: facade; instantiates the `Loader`, manages application context + lineage, and exposes a `Hydrator`.
- **Hydrator**: user-facing hydration API (obtained via `DataMapper::getHydrator()`); instantiates objects and hydrates them from raw data.
- **Loader**: executes hydration (lazy/eager), applies rules, hooks, and context.
- **AttributeReader**: reads PHP attributes (metadata) via Reflection.
- **ServiceResolver**: resolves a DataSource/service (PSR-11, locators, factories…).
- **ExpressionParser**: evaluates `expr(...)`, `context(...)`, `source('id')`, `service('id')`.
- **SourceFunctionProvider**: executes (and optionally caches) `source('id')`.
- **Registries**: minimal global state (Loader, Context, Locked properties).
- **LoadableTrait**: domain-side; triggers lazy-load by calling the global Loader.
- **DataLineageCollector**: event collection (when enabled).

## Link Explanations
- **DataMapperBuilder → ServiceResolver**: builds the resolution strategy (container/locators/factories).
- **DataMapperBuilder → DataMapper**: creates the runtime that orchestrates the rest.
- **DataMapper → Loader**: instantiates the execution core (with logger/cache/collector).
- **DataMapper → Hydrator**: exposes a dedicated hydration API for turning raw data into objects.
- **Hydrator → Loader**: delegates the actual hydration work to the Loader (instantiation, hooks, property setting).
- **DataMapper → LoaderRegistry**: registers the global Loader so domain objects can trigger loads without a direct dependency.
- **LoadableTrait → LoaderRegistry**: retrieves the active Loader to call `loadProperty(...)`.
- **LoadableTrait → LockedPropertyRegistry**: locks/unlocks properties to prevent being overwritten by hydration.
- **Loader → AttributeReader**: reads `DataSourceRef`, `Context`, `Needs`, hooks… to decide how to hydrate.
- **Loader → ServiceResolver**: obtains the DataSource/service instance to execute.
- **Loader → ExpressionParser**: resolves dynamic arguments (property references, context, sources, services).
- **ExpressionParser → ContextRegistry**: reads context (application + hydration) used in expressions.
- **ExpressionParser → SourceFunctionProvider**: executes/returns the result of `source('id')`.
- **ExpressionParser → ServiceResolver**: resolves `service('id')` when requested by an expression.
- **Loader → ContextRegistry**: writes hydration context coming from `#[Context]` attributes.
- **Loader → LockedPropertyRegistry**: checks locking before any hydration (skip if locked).
- **Loader → DataLineageCollector**: records events (DataSource calls, hydrations, skips…) when collection is enabled.

## Mermaid Diagram
```mermaid
flowchart LR
  %% Main facade
  Builder[DataMapperBuilder]
  DM[DataMapper]

  %% User-facing hydration API
  Hydrator[Hydrator]

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
  DM -->|exposes| Hydrator
  Hydrator -->|delegates| Loader
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
