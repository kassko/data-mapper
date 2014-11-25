<?php

namespace Kassko\DataAccess\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author kko
 */
final class PreHydrate
{
    use EventCommonTrait;
}
