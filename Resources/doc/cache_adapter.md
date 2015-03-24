Create an adapter for the cache
===========

The default cache implementation used is Kassko\DataMapper\Cache\ArrayCache. You can provide an other implementation (for example in settings ['cache' => ['metadata' => [instance => $someCacheInstance]]]) but it must be compatible with Kassko\Cache\CacheInterface. You can enforce this compatibility if you provide an adapter which wrap your cache implementation. Here is an example of adapter:

```php
use Kassko\DataMapper\Cache\CacheInterface as KasskoCacheInterface;
use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;

/**
 * A cache adapter to use the Kassko cache interface with a Doctrine cache implementation.
 */
class DoctrineCacheAdapter implements KasskoCacheInterface
{
    private $doctrineCache;

    public function __construct(DoctrineCacheInterface $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;
    }

    public function fetch($id)
    {
        return $this->doctrineCache->fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return $this->doctrineCache->contains($id);
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->doctrineCache->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->doctrineCache->delete($id);
    }
}
```

At the present time, there is no standard cache interface like the PSR-3 PSR\Logger\LoggerInterface.
PSR-6 should provide one ? That's why the data-mapper has it's own cache interface and you should provide an adapter for it.