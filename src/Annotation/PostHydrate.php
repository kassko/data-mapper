<?php

namespace Kassko\DataMapper\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @deprecated 
 * @see annotation Listeners
 *
 * @author kko
 */
final class PostHydrate
{
    use EventCommonTrait;
}
