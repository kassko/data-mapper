Use the ResultBuilder
===================

There are other ways to get results. You can find below all the ways to get results with the ResultBuilder:

```php
    /*
    Return an array of objects.
    So return an array with only one object, if only one fullfill the request.
    */
    $resultBuilder->all();
```

```php
    /*
    Return the object found.

    If more than one result are found, throw an exception
    Kassko\DataMapper\Result\Exception\NonUniqueResultException.

    If no result found, throw an exception
    Kassko\DataMapper\Result\Exception\NoResultException.
    */
    $resultBuilder->single();
```

```php
    /*
    Return the object found or null.
    */
    $resultBuilder->one();

    /*
    Return the object found or a default result (like false).
    */
    $resultBuilder->one(false);

    /*
    If more than one result are found, throw an exception
    Kassko\DataMapper\Result\Exception\NonUniqueResultException.
    */
```

```php
    /*
    Return the first object found or null.
    */
    $resultBuilder->first();

    /*
    Return the first object found or a default result (like value false).
    */
    $resultBuilder->first(false);

    /*
    If no result found, throw an exception
    Kassko\DataMapper\Result\Exception\NoResultException.
    */
```

```php
    /*
    Return an array indexed by a property value.

    If the index does not exists (allIndexedByUnknown()), throw an exception Kassko\DataMapper\Result\Exception\NotFoundIndexException.

    If the same index is found twice, throw an exception
    Kassko\DataMapper\Result\Exception\DuplicatedIndexException.

    Examples:

    allIndexedByBrand() will index by brand value:
    [
        'BrandA' => $theWatchInstanceWithBrandA,
        'BrandB' => $theWatchInstanceWithBrandB,
    ]

    allIndexedByColor() will index by color value:
    [
        'Blue' => $theWatchInstanceWithColorBlue,
        'Red' => $theWatchInstanceWithColorRed,
    ]

    allIndexedByUnknown() will throw a Kassko\DataMapper\Result\Exception\NotFoundIndexException.
    */
    $resultBuilder->allIndexedByBrand();//Indexed by brand value
    //or
    $resultBuilder->allIndexedByColor();//Indexed by color value
```

```php
    /*
    Return an iterator.

    Result will not be hydrated immediately but only when you will iterate the results (with "foreach" for example).
    */
    $result = $resultBuilder->iterable();
    foreach ($result as $object) {//$object is hydrated

        if ($object->getColor() === 'blue') {
            break;

            //We found the good object then we stop the loop and others objects in results will not be hydrated.
        }
    }
```

```php
    /*
    Return an iterator indexed by a property value.

    If the index does not exists, throw an exception Kassko\DataMapper\Result\Exception\NotFoundIndexException.

    If the same index is found twice, throw an exception Kassko\DataMapper\Result\Exception\DuplicatedIndexException.
    */
    $resultBuilder->iterableIndexedByBrand();
    //or
    $resultBuilder->iterableIndexedByColor();
```