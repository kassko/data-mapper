<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target("CLASS")
*
* @author kko
*/
final class Listeners
{
    /**
     * @var \Kassko\DataMapper\Annotation\Methods
     */
    public $preHydrate;

    /**
     * @var \Kassko\DataMapper\Annotation\Methods
     */
    public $postHydrate;

    /**
     * @var \Kassko\DataMapper\Annotation\Methods
     */
    public $preExtract;

    /**
     * @var \Kassko\DataMapper\Annotation\Methods
     */
    public $postExtract;
}
