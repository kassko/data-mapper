# Evolution History

This folder contains the history of all evolutions made to the data-mapper library.

## Pull Requests

| PR | Title | Date | Status |
|----|-------|------|--------|
| [#2](PR-0002.md) | Implement DataSourcesStore, expression language, and lazy loading for data mapper | 2025-12-18 | Closed |
| [#3](PR-0003.md) | Feat/add readme files devcontainer | 2025-12-18 | Closed |
| [#4](PR-0004.md) | Feat/add readme files devcontainer | 2025-12-18 | Merged |
| [#5](PR-0005.md) | Add DataSourcesStore, DataSourceRef, Field attributes and expression language | 2025-12-18 | Merged |
| [#6](PR-0006.md) | Add builder pattern with PSR-11 container and service locator support | 2025-12-18 | Merged |
| [#7](PR-0007.md) | Implement PSR-11 interface and registry pattern for LazyLoader | 2025-12-19 | Merged |
| [#8](PR-0008.md) | Implement 8 major evolutions: Property attribute, recursive hydration, eager loading, parent/trait support | 2025-12-19 | Merged |
| [#9](PR-0009.md) | Implement 6 evolutions: KeepProperty, Getter/Setter, Context, Expression enrichment, and PHP 8.0 fixes | 2025-12-19 | Merged |
| [#10](PR-0010.md) | Upgrade to PHP 8.1 minimum and remove compatibility workarounds | 2025-12-19 | Merged |
| [#11](PR-0011.md) | Add expression language functions, lifecycle hooks, and polymorphic property resolution | 2025-12-19 | Merged |
| [#14](PR-0014.md) | Fix getter chain resolution, add Needs attribute, and verify Hook external service support | 2025-12-19 | Pending |
| [#15](PR-0015.md) | Verify auto-loading behavior and clarify Needs attribute documentation | 2025-12-19 | Pending |
| [#17](PR-0017.md) | DataSource Improvements, Property Locking, and Expression Language Enhancements | 2025-12-20 | Pending |

## Overview

This repository contains a data-mapper library that has evolved through numerous pull requests. Each PR document contains:

- PR title and metadata (author, dates)
- Description of changes
- Implementation details
- Usage examples
- Test coverage information

## Key Milestones

- **PRs 2-5**: Initial foundation with DataSourcesStore, expression language, and lazy loading
- **PRs 6-7**: Builder pattern, PSR-11 integration, and registry pattern
- **PR 8**: Major evolution with recursive hydration, eager loading, and property control
- **PR 9**: Additional features including Context, Getter/Setter attributes
- **PR 10**: Upgrade to PHP 8.1
- **PR 11**: Advanced features like hooks and polymorphic resolution
- **PR 14**: Getter chain resolution, Needs attribute for property dependencies
- **PR 15**: Documentation and tests for auto-loading behavior and Needs use cases
- **PR 17**: DataSource scope fixes (breaking), property locking, expression enhancements (_this, #parent)
