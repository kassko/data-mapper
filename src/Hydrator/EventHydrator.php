<?php

namespace Kassko\DataAccess\Hydrator;

use Kassko\DataAccess\Configuration\ObjectKey;
use Kassko\DataAccess\Exception\ObjectMappingException;
use Kassko\DataAccess\ObjectManager;


/**
* Dispatch events after hydratation or extraction.
*
* @author kko
*/
class EventHydrator extends HydratorWrapper
{
	private $onBeforeExtract;
    private $onBeforeHydrate;
    private $onAfterExtract;
    private $onAfterHydrate;

	public function __construct(AbstractHydrator $hydrator, ObjectManager $objectManager)
	{
		parent::__construct($hydrator, $objectManager);
	}

	/**
	* Extrait les données d'un objet valeur selon une logique d'accès à ses membres (par les getters/setters ou directement par les propriétés).
	*
	* @param object $object
	* @return array
	*/
    public function extract($object)
    {
        $this->prepare($object);

    	/*if (isset($object, $this->onBeforeExtract)) {
            call_user_func([$object, $this->onBeforeExtract]);
        }*/

    	$data = parent::extract($object);

        if (isset($object, $this->onAfterExtract)) {
            call_user_func_array([$object, $this->onAfterExtract], [new HydrationContext($data)]);
            //$data = call_user_func_array([$object, $this->onAfterExtract], [new HydrationContext($data)]);
        }

        return $data;
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
        $this->prepare($object);

    	/*if (isset($this->onBeforeHydrate)) {
            call_user_func_array([$object, $this->onBeforeHydrate], [&$data]);
        }*/

    	$object = parent::hydrate($data, $object);

        if (isset($this->onAfterHydrate)) {
            call_user_func_array([$object, $this->onAfterHydrate], [new ImmutableHydrationContext($data)]);
        }

        return $object;
	}

	protected function doPrepare($object, ObjectKey $mrck = null)
    {
        $this->onBeforeExtract = $this->metadata->getOnBeforeExtract();
        $this->onBeforeHydrate = $this->metadata->getOnBeforeHydrate();
        $this->onAfterExtract = $this->metadata->getOnAfterExtract();
        $this->onAfterHydrate = $this->metadata->getOnAfterHydrate();
    }
}