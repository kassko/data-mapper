<?php

namespace Kassko\DataMapper\Hydrator;

use Kassko\DataMapper\ObjectManager;
use Kassko\DataMapper\Exception\ObjectMappingException;
use Kassko\DataMapper\Hydrator\MemberAccessStrategy;
use DateTimeInterface;
use DateTime;

/**
* An hydrator implementation which apply convenients hydration and extraction format to value object.
*
* @author kko
*/
class ValueObjectsHydrator extends HydratorWrapper
{
    const VALUE_OBJECT_MARKER = '@';

    private $propertyAccessStrategy;

    public function __construct(AbstractHydrator $hydrator, ObjectManager $objectManager, $propertyAccessStrategy)
    {
        parent::__construct($hydrator, $objectManager);

        $this->propertyAccessStrategy = $propertyAccessStrategy;
    }

    /**
    * Extrait les données d'un objet valeur selon une logique d'accès à ses membres (par les getters/setters ou directement par les propriétés).
    *
    * @param object $object
    * @return array
    */
    public function extract($object)
    {
        $data = parent::extract($object);

        /*if (! isset($object)) {//REVOIR COMMENT GERER CE CAS !!!
            return [];
        }*/

        $metadata = $this->objectManager->getMetadata(get_class($object));
        $className = $metadata->getName();
        $valueObjectsMetadata = $metadata->getValueObjectsByKey();
        $valueObjectsMetadata = $this->prepareMetadataForHydration($valueObjectsMetadata);

        $data = $this->doExtract($object, $valueObjectsMetadata, $className, $data);

        return $data;
    }

    private function doExtract($object, array $valueObjectsMetadata, $className, array $data)
    {
        if (! isset($object)) {
            $object = new $className;
            //$object = $reflClass->newInstance();

            /*
            if ($className === 'DateTime') {
                //echo 'className => '.$className;
                $object = new DateTime;
            } else {
                $object = new $className;
            }
            */
        }

        if ($object instanceof DateTimeInterface) {
            return $this->doExtractDate($object, $valueObjectsMetadata, $data);
        }

        $memberAccessStrategy = $this->createMemberAccessStrategy($object);

        foreach ($valueObjectsMetadata as $fieldName => $fieldData) {

            if (isset($fieldData['data'])) {
                $data = $this->doExtract(
                    $memberAccessStrategy->getValue($object, $fieldName),
                    $fieldData['data'],
                    $fieldData['class'],
                    $data
                );
            } else {
                $data[$fieldData] = $memberAccessStrategy->getValue($object, $fieldName);
            }
            unset($data[$fieldName]);
        }

        return $data;
    }

    private function doExtractDate(DateTimeInterface $dateObject, array $valueObjectsMetadata, array $data)
    {
        if (! isset($valueObjectsMetadata['Y'], $valueObjectsMetadata['m'], $valueObjectsMetadata['d'])) {
            throw new \LogicException("Extraction d'une date : vous devez configurer les 3 composantes 'Y', 'm', 'd'");
        }

        $data[$valueObjectsMetadata['Y']] = $dateObject->format('Y');
        $data[$valueObjectsMetadata['m']] = $dateObject->format('m');
        $data[$valueObjectsMetadata['d']] = $dateObject->format('d');

        if (isset($valueObjectsMetadata['H'])) {
            $data[$valueObjectsMetadata['H']] = $dateObject->format('H');

            if (isset($valueObjectsMetadata['i'])) {
                $data[$valueObjectsMetadata['i']] = $dateObject->format('i');
            }

            if (isset($valueObjectsMetadata['s'])) {
                $data[$valueObjectsMetadata['s']] = $dateObject->format('s');
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
        $object = parent::hydrate($data, $object);

        /*if (! isset($object)) {//REVOIR COMMENT GERER CE CAS !!!
            return null;
        }*/

        $valueObjectsMetadata = $this->objectManager->getMetadata(get_class($object))->getValueObjectsByKey();
        $valueObjectsMetadata = $this->prepareMetadataForHydration($valueObjectsMetadata);

        return $this->doHydrate($data, $valueObjectsMetadata, $object);
    }

    private function doHydrate(array $data, array $valueObjectsMetadata, $object)
    {
        if ($object instanceof DateTimeInterface) {
            return $this->doHydrateDate($data, $valueObjectsMetadata, $object);
        }

        $memberAccessStrategy = $this->createMemberAccessStrategy($object);

        foreach ($valueObjectsMetadata as $fieldName => $fieldData) {
            if (! isset($fieldData['data'])) {
                if (! isset($data[$valueObjectsMetadata[$fieldName]])) {
                    $data[$valueObjectsMetadata[$fieldName]] = null;
                }

                $memberAccessStrategy->setScalarValue($data[$valueObjectsMetadata[$fieldName]], $object, $fieldName);
            } else {
                $valueObject = $memberAccessStrategy->setObjectValue($fieldData['class'], $object, $fieldData['fieldName']);

                if ($valueObject) {
                    $this->doHydrate($data, $fieldData['data'], $valueObject);
                }
            }
        }

        return $object;
    }

    private function doHydrateDate(array $data, array $valueObjectsMetadata, DateTimeInterface $dateObject)
    {
        if (! isset($valueObjectsMetadata['Y'], $valueObjectsMetadata['m'],  $valueObjectsMetadata['d'])) {
            throw new \LogicException("Hydratation d'une date : vous devez configurer les 3 composantes 'Y', 'm', 'd'");
        }

        if (! isset($data[$valueObjectsMetadata['Y']], $data[$valueObjectsMetadata['m']], $data[$valueObjectsMetadata['d']])) {
            return $dateObject;
            //throw new \RuntimeException("Hydratation d'une date : les données sont incomplètes. Il manque le jour, le mois ou l'année.");
        }

        $dateObject->setDate($data[$valueObjectsMetadata['Y']], $data[$valueObjectsMetadata['m']], $data[$valueObjectsMetadata['d']]);


        if (isset($valueObjectsMetadata['H'])) {
            $dateObject->setTime(
                $data[$valueObjectsMetadata['H']],
                isset($valueObjectsMetadata['i']) ? $data[$valueObjectsMetadata['i']] : 0,
                isset($valueObjectsMetadata['s']) ? $data[$valueObjectsMetadata['s']] : 0
            );
        }

        return $dateObject;
    }

    private function prepareMetadataForHydration(array $valueObjectsMetadata)
    {
        $dataFormatted = [];

        foreach ($valueObjectsMetadata as $valueObjectName => $valueObjectDataList) {

            $dataFormatted[$valueObjectName] = $this->prepareItemMetadataForHydration(
                $valueObjectName,
                $valueObjectDataList[0],
                $valueObjectDataList
            );
        }

        return $dataFormatted;
    }

    private function prepareItemMetadataForHydration($valueObjectName, $rootValueObjectMetadata, $allValueObjects)
    {
        $dataFormatted = [
            'class' => $rootValueObjectMetadata['valueObjectClass'],
            'fieldName' => $rootValueObjectMetadata['name'],
        ];

        $fieldNamesDataFormatted = [];
        foreach ($rootValueObjectMetadata['fieldNames'] as $mappedFieldName => &$fieldName) {
            if (self::VALUE_OBJECT_MARKER == substr($fieldName[0], 0, strlen(self::VALUE_OBJECT_MARKER))) {
                $valueObjectId = substr($fieldName, 1);
                $valueObjectMetadata = $this->findValueObjectWithId($valueObjectId, $allValueObjects);
                if (false === $valueObjectMetadata) {
                    throw ObjectMappingException::valueObjectIdNotFound($valueObjectId);
                }

                $fieldName = $this->prepareItemMetadataForHydration($valueObjectMetadata['name'], $valueObjectMetadata, $allValueObjects);
            }

            $fieldNamesDataFormatted[$mappedFieldName] = $fieldName;
        }
        unset($fieldName);//unset par précaution !
        $dataFormatted['data'] = $fieldNamesDataFormatted;

        return $dataFormatted;
    }

    private function findValueObjectWithId($valueObjectId, $valueObjects)
    {
        foreach ($valueObjects as $valueObject) {
            if ($valueObjectId == $valueObject['name']) {
                return $valueObject;
            }
        }

        return false;
    }

    private function createMemberAccessStrategy($object)
    {
        $memberAccessStrategy = $this->propertyAccessStrategy ? new MemberAccessStrategy\PropertyAccessStrategy : new MemberAccessStrategy\GetterSetterAccessStrategy;
        $memberAccessStrategy->prepare($object);

        return $memberAccessStrategy;
    }
}
