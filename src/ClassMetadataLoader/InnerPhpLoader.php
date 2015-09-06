<?php

namespace Kassko\DataMapper\ClassMetadataLoader;

/**
 * Class metadata loader for php array data provided by objects.
 *
 * @author kko
 */
class InnerPhpLoader extends ArrayLoader
{
    public function supports(LoadingCriteriaInterface $loadingCriteria)
    {
        return
            'inner_php' === $loadingCriteria->getResourceType()
            &&
            method_exists($loadingCriteria->getResourceClass(), $loadingCriteria->getResourceMethod())
        ;
    }

    protected function doGetData(LoadingCriteriaInterface $loadingCriteria)
    {
        $callable = [$loadingCriteria->getResourceClass(), $loadingCriteria->getResourceMethod()];
        return $callable();
    }

    protected function normalize(array &$data)
    {
        parent::normalize($data);

        $normalizedFieldsData = [];

        $dataName = 'fields';
        if (isset($data[$dataName])) {
            foreach ($data[$dataName] as $mappedFieldName => $fieldData) {

                if (is_numeric($mappedFieldName)) {//if $mappedFieldName is a numeric index, $fieldData contains the field.
                    $mappedFieldName = $fieldData;                
                }

                $normalizedFieldsData[$mappedFieldName] = $fieldData;
            }
            $data[$dataName] = $normalizedFieldsData;
        }
    }
}
