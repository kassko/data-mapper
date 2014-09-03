<?php

namespace Kassko\DataAccess\Hydrator;

use Kassko\DataAccess\Hydrator\AbstractHydrator;
use Kassko\DataAccess\ObjectManager;

/**
* Wrapper to simplify hydrator implementation witch decorates an other hydrator.
*
* @author kko
*/
abstract class HydratorWrapper extends AbstractHydrator
{
	protected $wrappedHydrator;

	public function __construct(AbstractHydrator $wrappedHydrator, ObjectManager $objectManager)
	{
		parent::__construct($objectManager);

		$this->wrappedHydrator = $wrappedHydrator;
	}

	public function extract($object)
    {
    	return $this->wrappedHydrator->extract($object);
    }

    /**
	 * Hydrate $object with the provided $data.
	 *
	 * @param array $data
	 * @param object $object
	 * @return object
	 */
    public function hydrate(array $data, $object)
    {
    	return $this->wrappedHydrator->hydrate($data, $object);
	}

    /**
     * @inheritdoc
     */
    public function getRelationFieldExtraction($relationfield)
    {
        return $this->wrappedHydrator->getRelationFieldExtraction($relationfield);
    }

}