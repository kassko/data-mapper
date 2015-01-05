<?php

namespace Kassko\DataMapper\Annotation;

/**
* @Annotation
* @Target({"PROPERTY","ANNOTATION"})
*
* @author kko
*/
final class Getter
{
    use PropertyWrapperTrait;
}
