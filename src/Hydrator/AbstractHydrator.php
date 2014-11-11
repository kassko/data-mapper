<?php

namespace Kassko\DataAccess\Hydrator;

use ArrayObject;
use Kassko\DataAccess\Configuration\ObjectKey;
use Kassko\DataAccess\Exception\NotImplementedMethodException;
use Kassko\DataAccess\Hydrator\HydrationStrategy\HydrationStrategyInterface;
use Kassko\DataAccess\ObjectManager;
use Zend\Stdlib\Exception;
use Zend\Stdlib\Hydrator\Filter\FilterComposite;

/**
* Base for hydrator.
*
* @author kko
*/
abstract class AbstractHydrator
{

    /**
    * @var ObjectManager
    */
    protected $objectManager;

    /**
    * @var ClassMetadata
    */
    protected $metadata;

    /**
    * Liste de strategies de cet hydrateur.
    *
    * @var ArrayObject
    */
    protected $strategies;

    /**
    * Composite pour filrer les méthodes, qui permettent d'hydrater.
    * @var Filter\FilterComposite
    */
    protected $filterComposite;

    /**
    * crée une nouvelle instance de cette classe.
    */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->strategies = new ArrayObject();
        $this->filterComposite = new FilterComposite();
    }

    /**
    * Récupère la stratégie à partir du nom donné.
    *
    * @param string $name Le nom de la stratégie à récupérer.
    * @return HydrationStrategyInterface
    */
    public function getStrategy($name)
    {
        if (isset($this->strategies[$name])) {
            return $this->strategies[$name];
        }

        if (!isset($this->strategies['*'])) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: no strategy by name of "%s", and no wildcard strategy present',
                __METHOD__,
                $name
            ));
        }

        return $this->strategies['*'];
    }

    /**
    * Checks if the strategy with the given name exists.
    *
    * @param string $name The name of the strategy to check for.
    * @return bool
    */
    public function hasStrategy($name)
    {
        return array_key_exists($name, $this->strategies)
               || array_key_exists('*', $this->strategies);
    }

    /**
    * Adds the given strategy under the given name.
    *
    * @param string $name The name of the strategy to register.
    * @param HydrationStrategyInterface $strategy The strategy to register.
    * @return HydratorInterface
    */
    public function addStrategy($name, HydrationStrategyInterface $strategy)
    {
        $this->strategies[$name] = $strategy;
        return $this;
    }

    /**
    * Removes the strategy with the given name.
    *
    * @param string $name The name of the strategy to remove.
    * @return HydratorInterface
    */
    public function removeStrategy($name)
    {
        unset($this->strategies[$name]);
        return $this;
    }

    /**
    * Extrait une valeur. Si pas de stratégie, la valeur brute est retournée.
    *
    * @param string $name Le nom de la stratégie à utiliser.
    * @param mixed $value La valeur qui doit-être extraite.
    * @param array $object L'objet est fourni à titre de contexte.
    * @param array $data Tout le jeu de donnée est fournie à titre de contexte.
    * @return mixed
    */
    public function extractValue($name, $value, $object = null, $data = null)
    {
        if ($this->hasStrategy($name)) {
            $strategy = $this->getStrategy($name);
            $value = $strategy->extract($value, $object, $data);
        }
        return $value;
    }

    /**
    * Hydrate une valeur. Si pas de stratégie, la valeur brute est retournée.
    *
    * @param string $name Le nom de la stratégie à utiliser.
    * @param mixed $value La valeur qui doit-être convertie.
    * @param array $data Tout le jeu de donnée est fournie à titre de contexte.
    * @param array $object L'objet est fourni à titre de contexte.
    * @return mixed
    */
    public function hydrateValue($name, $value, $data = null, $object = null)
    {
        if ($this->hasStrategy($name)) {

            $strategy = $this->getStrategy($name);
            $value = $strategy->hydrate($value, $data, $object);
        }

        return $value;
    }

    /**
    * Récupère l'instance de filtre.
    *
    * @return Filter\FilterComposite
    */
    public function getFilter()
    {
        return $this->filterComposite;
    }

    public function addFilter($name, $filter, $condition = FilterComposite::CONDITION_OR)
    {
        return $this->filterComposite->addFilter($name, $filter, $condition);
    }

    public function hasFilter($name)
    {
        return $this->filterComposite->hasFilter($name);
    }

    public function removeFilter($name)
    {
        return $this->filterComposite->removeFilter($name);
    }

    /**
     * Une extraction d'objet ne contient des champs en relation que les id (dans la clé de base).
     * Mais aussi l'extraction de cette relation dans une autre clé.
     *
     * @param $relationfield La clé du champs en relation pour lequel on cherche l'autre clef qui contient toute l'extraction.
     *
     * Renvoi cette autre clef sous laquelle est stocké une extration de la relation.
     */
    public function getRelationFieldExtraction($relationfield)
    {
        //throw new NotImplementedMethodException($this, __FUNCTION__);
    }

    /**
    * Handle various type conversions that should be supported natively by Doctrine (like DateTime)
    *
    * @param mixed $value
    * @param string $typeOfField
    * @return DateTime
    */
    protected function handleTypeConversions($value, $typeOfField)
    {
        switch($typeOfField) {
            case 'datetimetz':
            case 'datetime':
            case 'time':
            case 'date':
                if ('' === $value) {
                    return null;
                }

                if (is_int($value)) {
                    $dateTime = new DateTime();
                    $dateTime->setTimestamp($value);
                    $value = $dateTime;
                } elseif (is_string($value)) {
                    $value = new DateTime($value);
                }

                break;
            default:
        }

        return $value;
    }

    protected function prepare($object, ObjectKey $objectKey = null)
    {
        if (isset($object)) {

            if (null === $objectKey) {
                $this->metadata = $this->objectManager->getMetadata(get_class($object));
            } else {
                $this->metadata = $this->objectManager->getMetadata($objectKey->getKey());
            }
        }

        $this->doPrepare($object);
    }

    protected function doPrepare($object, ObjectKey $objectKey = null)
    {
    }

}