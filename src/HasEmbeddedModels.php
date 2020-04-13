<?php


namespace Mistersaal\Mongodb\Embed;


/**
 * Trait HasEmbeddedModels
 * @package App
 * @property array $embedOne
 * @property array $embedMany
 */
trait HasEmbeddedModels
{
    /**
     * @param $key
     * @param $value
     * @return HasEmbeddedModels
     */
    protected function setEmbeddedAttribute($key, $value)
    {
        if (is_array($value)) {
            if (array_key_exists($key, $this->embedOne ?? [])) {
                return $this->setEmbedOneAttribute($key, $value);
            }
            if (array_key_exists($key, $this->embedMany ?? [])) {
                return $this->setEmbedManyAttribute($key, $value);
            }
        }
        return $this;
    }

    public function setEmbeddedAttributes()
    {
        foreach ($this->getAttributes() as $key => $value) {
            $this->setEmbeddedAttribute($key, $value);
        }
        return $this;
    }

    protected function setEmbedOneAttribute($key, $value)
    {
        $this->{$key} = new $this->embedOne[$key]($value);
        return $this;
    }

    protected function setEmbedManyAttribute($key, $value)
    {
        $this->{$key} = collect($value)->map(function ($item) use ($key) {
            return new $this->embedMany[$key]($item);
        });
        return $this;
    }

    public function setSerializedEmbeddedAttributes()
    {
        foreach ($this->getAttributes() as $key => $value) {
            $this->{$key} = $this->getSerializedEmbeddedAttribute($key, $value);
        }
        return $this;
    }

    protected function getSerializedEmbeddedAttribute($key, $value)
    {
        if (is_object($value)) {
            if (array_key_exists($key, $this->embedOne ?? [])) {
                return $this->getSerializedEmbedOneAttribute($value);
            }
            if (array_key_exists($key, $this->embedMany ?? [])) {
                return $this->getSerializedEmbedManyAttribute($value);
            }
        }
        return $value;
    }

    protected function getSerializedEmbedOneAttribute($value)
    {
        if ($value instanceof HasEmbeddedModelsInterface) {
            $value->setSerializedEmbeddedAttributes();
        }
        return $value->getAttributes();
    }

    protected function getSerializedEmbedManyAttribute($value)
    {
        return $value->map(function ($item) {
            if ($item instanceof HasEmbeddedModelsInterface) {
                $item->setSerializedEmbeddedAttributes();
            }
            return $item->getAttributes();
        })->toArray();
    }

    protected static function boot()
    {
        parent::boot();
        static::retrieved(function ($model) {
            $model->setEmbeddedAttributes();
        });
        static::saving(function ($model) {
            $model->setSerializedEmbeddedAttributes();
        });
    }

}
