# Param Attribute

L'attribut `#[Param]` permet d'injecter des valeurs dans les paramètres de constructeur, getter ou setter.

## Utilisation

### Dans un constructeur

Tous les paramètres du constructeur **doivent** avoir l'attribut `#[Param]`. Les références aux propriétés de l'objet (`#id`, `property('id')`, `##object`) sont **interdites** car l'objet n'existe pas encore.

```php
use Kassko\DataMapper\Attribute\Param;

class Person
{
    private string $id;
    
    public function __construct(
        #[Param(value: "expr(context('userId'))")]
        string $id
    ) {
        $this->id = $id;
    }
}
```

### Dans un getter

Les paramètres avec `#[Param]` sont injectés automatiquement. Les références aux propriétés sont autorisées.

```php
use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

class Person
{
    use LoadableTrait;
    
    private ?string $name = null;
    
    public function getName(
        #[Param(value: "expr(service('nameFormatter'))")]
        $formatter = null
    ): ?string {
        $this->loadProperty('name');
        return $formatter ? $formatter->format($this->name) : $this->name;
    }
}
```

### Dans un setter

Le **premier** paramètre du setter **ne doit pas** avoir `#[Param]` (c'est la valeur à assigner). Les paramètres supplémentaires **doivent** avoir `#[Param]`.

```php
use Kassko\DataMapper\Attribute\Param;

class Person
{
    private ?string $email = null;
    
    public function setEmail(
        $email,  // Premier paramètre : valeur normale, pas de Param
        #[Param(value: "expr(context('enforcedEmail'))")]
        $enforcedEmail = null
    ): void {
        $this->email = $enforcedEmail ?: $email;
    }
}
```

## Valeurs supportées

### Valeur statique

```php
#[Param(value: "valeur fixe")]
```

### Expression context()

Récupère une valeur du contexte (défini via `ContextRegistry` ou `DataMapper::addToContext()`).

```php
#[Param(value: "expr(context('userId'))")]
```

### Expression service()

Injecte un service depuis le ServiceResolver.

```php
#[Param(value: "expr(service('myService'))")]
```

### Expression source()

Exécute une data source et retourne son résultat.

```php
#[Param(value: "expr(source('dataSourceId'))")]
```

### Expression property() (getters/setters uniquement)

Référence une propriété de l'objet (interdit dans les constructeurs).

```php
#[Param(value: "expr(property('name'))")]
// ou syntaxe courte
#[Param(value: "#name")]
```

### Référence à l'objet courant (getters/setters uniquement)

```php
#[Param(value: "##object")]
```

## Règles de validation

| Contexte | Règles |
|----------|--------|
| Constructeur | Tous les paramètres **doivent** avoir `#[Param]`. `#id`, `property()`, `##object` interdits. |
| Getter | Paramètres avec `#[Param]` sont injectés ; paramètres sans Param doivent avoir une valeur par défaut. |
| Setter | Premier paramètre **sans** `#[Param]` ; paramètres suivants **avec** `#[Param]`. |

## Exemple complet

```php
use Kassko\DataMapper\Attribute\DataSourceRef;
use Kassko\DataMapper\Attribute\DataSourcesStore;
use Kassko\DataMapper\Attribute\MultiPropDataSource;
use Kassko\DataMapper\Attribute\Param;
use Kassko\DataMapper\ObjectExtension\LoadableTrait;

#[DataSourcesStore([
    new MultiPropDataSource(id: 'userApi', class: 'UserApiService', method: 'getUser'),
])]
class User
{
    use LoadableTrait;
    
    private string $id;
    
    public function __construct(
        #[Param(value: "expr(context('requestedUserId'))")]
        string $id
    ) {
        $this->id = $id;
    }
    
    #[DataSourceRef(id: 'userApi')]
    private ?string $name = null;
    
    public function getName(
        #[Param(value: "expr(service('nameFormatter'))")]
        $formatter = null
    ): ?string {
        $this->loadProperty('name');
        return $formatter?->format($this->name) ?? $this->name;
    }
    
    public function setName(
        $name,
        #[Param(value: "expr(context('namePrefix'))")]
        $prefix = ''
    ): void {
        $this->name = $prefix . $name;
    }
}
```
