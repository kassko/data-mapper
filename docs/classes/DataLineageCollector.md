# Data Lineage Collector

Track how data flows and transforms during hydration for debugging and profiling purposes.

## Overview

The Data Lineage Collector is a debugging tool that records all data flow events during hydration:

- DataSource calls and their results
- Property hydrations and transformations
- Decision points (where data is skipped due to priority, scope, lock, etc.)
- Hook executions and their effects
- Custom hydrator invocations
- Context changes

This is an **beta feature** intended for debugging and can be used by tools like Symfony Profiler.

## Usage

### Enabling Collection

```php
use Kassko\DataMapper\DataMapperBuilder;

$builder = new DataMapperBuilder();
$dataMapper = $builder->build();

// Enable lineage collection
$dataMapper->enableLineageCollection();

// Your hydration code here...
$object->loadProperties();

// Get the collector
$collector = $dataMapper->getLineageCollector();

// Get all recorded events
$events = $collector->getEvents();

// Get a summary
$summary = $collector->getSummary();
```

### Event Types

| Type | Description |
|------|-------------|
| `datasource_call` | A DataSource method was called |
| `property_hydration` | A property was hydrated with a value |
| `property_skipped` | A property was skipped (locked, priority, scope, etc.) |
| `property_transformed` | A property value was transformed (via hook) |
| `hook_executed` | A lifecycle hook was executed |
| `custom_hydrator` | A custom hydrator was invoked |
| `context_set` | A context value was set |
| `decision_point` | A decision affecting data flow was made |

### Querying Events

```php
$collector = $dataMapper->getLineageCollector();

// Get all events
$allEvents = $collector->getEvents();

// Get events by type
$hydrations = $collector->getEventsByType(LineageEvent::TYPE_PROPERTY_HYDRATION);
$skipped = $collector->getEventsByType(LineageEvent::TYPE_PROPERTY_SKIPPED);

// Get events for a specific property
$propertyEvents = $collector->getEventsForProperty('App\\Entity\\User', 'email');

// Get summary statistics
$summary = $collector->getSummary();
// Returns:
// [
//     'totalEvents' => 42,
//     'eventsByType' => ['property_hydration' => 30, 'property_skipped' => 5, ...],
//     'skippedReasons' => ['locked' => 2, 'priority' => 3],
//     'maxDepth' => 3,
//     'totalDuration' => 0.0234,
// ]
```

### Event Structure

Each `LineageEvent` contains:

```php
class LineageEvent
{
    public readonly string $type;           // Event type
    public readonly string $objectClass;    // Class being hydrated
    public readonly ?string $propertyName;  // Property involved
    public readonly ?string $source;        // DataSource or hook that produced the value
    public readonly mixed $originalValue;   // Value before transformation
    public readonly mixed $finalValue;      // Value after transformation
    public readonly ?string $reason;        // Why (for skipped events)
    public readonly array $metadata;        // Additional metadata
    public readonly float $timestamp;       // Time since collection started
    public readonly int $depth;             // Hydration depth level
}
```

### Skipped Reasons

When a property is skipped, the `reason` field indicates why:

| Reason | Description |
|--------|-------------|
| `locked` | Property is locked via `lockProperty()` |
| `priority` | A higher-priority source already hydrated the property |
| `already_loaded` | Property was already loaded |
| `scope` | Property excluded by loading scope configuration |

## Integration with Symfony Profiler

The Data Lineage Collector can be integrated with Symfony Profiler to visualize data flow:

```php
// In a Symfony data collector
class DataMapperDataCollector extends DataCollector
{
    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $collector = $this->dataMapper->getLineageCollector();
        
        $this->data = [
            'events' => $collector->getEventsAsArrays(),
            'summary' => $collector->getSummary(),
        ];
    }
}
```

## Performance Considerations

- Collection is **disabled by default**
- Only enable for debugging/profiling
- Call `$collector->clear()` between requests
- Events use memory proportional to hydration complexity

## See Also

- [DataMapper](../classes/DataMapper.md)
- [Context](Context.md)
