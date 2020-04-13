<?php

namespace Mistersaal\Mongodb\Embed;

/**
 * Interface HasEmbeddedModelsInterface
 * @package App
 * @property array $embedOne
 * @property array $embedMany
 */
interface HasEmbeddedModelsInterface
{
    public function setEmbeddedAttributes();

    public function setSerializedEmbeddedAttributes();
}
