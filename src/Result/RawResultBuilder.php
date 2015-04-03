<?php

namespace Kassko\DataMapper\Result;

use Kassko\DataMapper\ObjectManager;
use LogicException;

/**
 * Transform an objet or an object collection into raw results.
 *
 * @author kko
 */
class RawResultBuilder implements RawResultBuilderInterface
{
	protected $objectManager;
	protected $data;
	protected $objectClass;    

    public function __construct(ObjectManager $objectManager, $data)
    {
        $this->objectManager = $objectManager;
        $this->data = $data;
        $this->objectClass = self::detectObjectClass($data);
    }

	/**
    * {@inheritdoc}
    */
    public function raw()
    {
        $rh = new ResultExtractor($this->objectManager);

        return $rh->extract($this->objectClass, $this->data);
    }

    private static function detectObjectClass($data)
    {
    	if (is_object($data)) {
    		return get_class($data);
    	} 

    	if (is_array($data)) {
    		if(count($data) > 0) {
    			return get_class(current($data));	
    		}

    		throw new LogicException(
    				'Cannot detect class from given datas. An "array" with at least one item was expected and got an empty one.'
				);	
    	} 

		throw new LogicException(
			sprintf('Cannot detect class from given datas. "array" or "object" was expected and got "%s".', gettype($data))
		);
    }
}
