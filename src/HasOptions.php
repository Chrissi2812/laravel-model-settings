<?php

namespace Cklmercer\ModelSettings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait HasOptions
{
    protected static $optionsInstance;

    /**
     * Boot the HasOptions trait.
     *
     * @return void
     */
    public static function bootHasOptions(): void
    {
        self::creating(function (Model $model) {
            if (!$model->options) {
                $model->options = $model->getDefaultOptions();
            }
        });

        self::saving(function (Model $model) {
            if ($model->options && property_exists($model, 'allowedOptions') && is_array($model->allowedOptions)) {
                $model->options = $model->options->only($model->allowedOptions);
            }
        });
    }

    /**
     * Get the model's default options.
     *
     * @return Collection
     */
    public function getDefaultOptions(): Collection
    {
        return collect((isset($this->defaultOptions) && is_array($this->defaultOptions))
            ? $this->defaultOptions
            : []);
    }

    /**
     * Get the options attribute.
     *
     * @param string|null $options
     *
     * @return Collection
     */
    public function getOptionsAttribute(?string $options): Collection
    {
        return collect(json_decode($options, true));
    }

    /**
     * Set the options attribute.
     *
     * @param array|Collection $options
     *
     * @return void
     */
    public function setOptionsAttribute($options): void
    {
        $this->attributes['options'] = (is_array($options)) ? json_encode($options) : $options->toJson();
    }

    /**
     * The model's options.
     *
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return Options|mixed
     */
    public function options(?string $key = null, $default = null)
    {
        return $key ? $this->options()->get($key, $default) : $this->getOptionsInstance();
    }

    /**
     * Get an instance of Options.
     *
     * @return Options
     */
    private function getOptionsInstance(): Options
    {
        if (is_null(self::$optionsInstance) || self::$optionsInstance->getModel() !== $this) {
            self::$optionsInstance = new Options($this);
        }

        return self::$optionsInstance;
    }
}
