# Main Components — C4-like View (Mermaid)

This view shows **the exact same components** as the previous view, but organizes them into "boundaries" (groups) to make it closer to a C4-style reading (Context/Containers/Components) while staying in standard Mermaid.

## Diagram (C4-like using `flowchart`)
```mermaid
flowchart LR
  %% External actor
  Caller[App / Caller]

  %% Boundary: Domain model
  subgraph B1[Domain Model]
    Domain[Domain Object]
    Loadable[LoadableTrait]
  end

  %% Boundary: DataMapper Runtime
  subgraph B2[DataMapper Runtime]
    Builder[DataMapperBuilder]
    DM[DataMapper]
    Hydrator[Hydrator]
    Loader[Loader]
  end

  %% Boundary: Metadata & Expression
  subgraph B3[Metadata & Expression]
    Attr[Metadata/AttributeReader]
    Expr[ExpressionParser]
    SourceFP[SourceFunctionProvider]
  end

  %% Boundary: Registries (global minimal state)
  subgraph B4[Registries]
    LoaderReg[Registry/LoaderRegistry]
    CtxReg[Registry/ContextRegistry]
    LockReg[Registry/LockedPropertyRegistry]
  end

  %% Boundary: Services resolution
  subgraph B5[Services]
    Resolver[ServiceResolver]
  end

  %% Boundary: Observability
  subgraph B6[Observability]
    Lineage[DataLineageCollector]
  end

  %% Main relationships
  Caller -->|build + configure| Builder
  Builder -->|func_build| Resolver
  Builder -->|func_build| DM

  DM -->|creates| Loader
  DM -->|exposes| Hydrator
  Hydrator -->|delegates| Loader
  DM -->|registers| LoaderReg
  DM -->|owns/enables| Lineage

  Caller -->|calls getters| Domain
  Caller -->|hydrates raw data| Hydrator
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

## Quick Reading (where to look)
- **Domain Model**: does not depend directly on the concrete Loader; it goes through `LoaderRegistry`.
- **DataMapper Runtime**: wires the Loader and exposes the `DataMapper` facade.
- **Hydrator**: user-facing API to hydrate objects from raw data; delegates to `Loader`.
- **Hydrator**: user-facing API to hydrate objects from raw data (obtained via `DataMapper::getHydrator()`); delegates to `Loader`.
- **Registries**: intentionally minimal global state (current Loader, context, locks).
- **Metadata & Expression**: attribute reading + expression/argument evaluation.
- **Observability**: event collection, can be enabled/disabled.
