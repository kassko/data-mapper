# Composants principaux — Vue C4-like (Mermaid)

Cette vue reprend **exactement les mêmes composants** que la vue précédente, mais les organise en "boundaries" (groupes) pour se rapprocher d’une lecture C4 (Contexte/Conteneurs/Composants) tout en restant en Mermaid standard.

## Diagramme (C4-like en `flowchart`)
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
  DM -->|registers| LoaderReg
  DM -->|owns/enables| Lineage

  Caller -->|calls getters| Domain
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

## Lecture rapide (où regarder)
- **Domain Model** : ne dépend pas directement du Loader concret; passe par `LoaderRegistry`.
- **DataMapper Runtime** : construit/branche le Loader et expose la façade `DataMapper`.
- **Registries** : état global volontairement minimal (Loader courant, context, locks).
- **Metadata & Expression** : lecture des attributs + évaluation des expressions/arguments.
- **Observability** : collecte d’événements, activable/désactivable.
