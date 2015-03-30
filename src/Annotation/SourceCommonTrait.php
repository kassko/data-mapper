<?php

namespace Kassko\DataMapper\Annotation;

/**
* Property annotations to be used in Column annotation or Entity annotation.
*
* A property used in Entity annotation works for all properties.
* A property used in Column annotation only works for the annotated property.
* If a property used in both, the Column annotation is priority.
*
* @author kko
*/
trait SourceCommonTrait
{
    use MethodTrait;

    /**
     * @var string
     */
    public $id;

    /**
     * Loading strategy to use for this provider.
     *
     * @var bool
     */
    public $lazyLoading = false;

    /**
     * How so data contains data for one or severals fields.
     *
     * @var bool
     */
    public $supplySeveralFields = false;

    /**
     * A source involved.
     *
     * @var array
     */
    public $involvedSourceId = null;

    /**
     * @var string
     *
     * @Enum({"checkReturnValue", "checkException"})
     */
    public $onFail = 'checkReturnValue';

    /**
     * @var string
     */
    public $exceptionClass = '\Exception';

    /**
     * @var string
     *
     * @Enum({"null", "false", "emptyString", "emptyArray"})
     */
    public $badReturnValue = 'null';

    /**
     * @var string
     */
    public $fallbackSourceId;

    /**
     * @var \Kassko\DataMapper\Annotation\Method
     */
    public $preprocessor = null;

    /**
     * @var \Kassko\DataMapper\Annotation\Method
     */
    public $processor = null;

    /**
     * @var \Kassko\DataMapper\Annotation\Methods
     */
    public $preprocessors = [];

    /**
     * @var \Kassko\DataMapper\Annotation\Methods
     */
    public $processors = [];
}
