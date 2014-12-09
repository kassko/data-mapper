<?php

namespace Kassko\DataMapper\Hydrator;

use Kassko\DataMapper\Configuration\ObjectKey;
use Kassko\DataMapper\Exception\ObjectMappingException;
use Kassko\DataMapper\ObjectManager;


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
    private $metadataExtensionClass;

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

        if (isset($object, $this->onBeforeExtract)) {

            if (! isset($this->metadataExtensionClass)) {
                call_user_func([$object, $this->onBeforeExtract]);
            } else {
                call_user_func_array([$this->metadataExtensionClass, $this->onBeforeExtract], [$object]);
            }
        }

        $data = parent::extract($object);

        if (isset($object, $this->onAfterExtract)) {

            if (! isset($this->metadataExtensionClass)) {
                call_user_func_array([$object, $this->onAfterExtract], [new HydrationContext($data)]);
            } else {
                call_user_func_array([$this->metadataExtensionClass, $this->onAfterExtract], [new HydrationContext($data), $object]);
            }
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

        if (isset($this->onBeforeHydrate)) {

            if (! isset($this->metadataExtensionClass)) {
                call_user_func_array([$object, $this->onBeforeHydrate], [new HydrationContext($data)]);
            } else {
                call_user_func_array([$this->metadataExtensionClass, $this->onBeforeHydrate], [new HydrationContext($data), $object]);
            }
        }

        $object = parent::hydrate($data, $object);

        if (isset($this->onAfterHydrate)) {

            if (! isset($this->metadataExtensionClass)) {
                call_user_func_array([$object, $this->onAfterHydrate], [new ImmutableHydrationContext($data)]);
            } else {
                call_user_func_array([$this->metadataExtensionClass, $this->onAfterHydrate], [new ImmutableHydrationContext($data), $object]);
            }

        }

        return $object;
    }

    protected function doPrepare($object, ObjectKey $mrck = null)
    {
        $this->onBeforeExtract = $this->metadata->getOnBeforeExtract();
        $this->onBeforeHydrate = $this->metadata->getOnBeforeHydrate();
        $this->onAfterExtract = $this->metadata->getOnAfterExtract();
        $this->onAfterHydrate = $this->metadata->getOnAfterHydrate();
        $this->metadataExtensionClass = $this->metadata->getClassMetadataExtensionClass();
    }
}
