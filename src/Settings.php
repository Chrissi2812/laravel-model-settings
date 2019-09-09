<?php

namespace Cklmercer\ModelSettings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Settings
{
    /**
     * Stores a reference to the Model.
     *
     * @var Model
     */
    protected $model;

    /**
     * Unique key for the settings cache.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * Settings constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->cacheKey = "{$model->getTable()}.{$model->getKey()}.settings";
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Get the model's settings.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return collect(Cache::rememberForever($this->cacheKey, function (): Collection {
            return $this->model->settings;
        }));
    }

    /**
     * Apply the model's settings.
     *
     * @param array|Collection $settings
     *
     * @return $this
     */
    public function apply($settings = []): self
    {
        $this->model->settings = $settings;
        $this->model->save();
        Cache::forget($this->cacheKey);

        return $this;
    }

    /**
     * Delete the setting at the given path.
     *
     * @param string|null $path
     *
     * @return $this
     */
    public function delete(?string $path = null): self
    {
        if (!$path) {
            return $this->apply([]);
        }

        $settings = $this->all()->toArray();
        Arr::forget($settings, $path);

        return $this->apply($settings);
    }

    /**
     * Forget the setting at the given path.
     *
     * @alias delete()
     *
     * @param string|null $path
     *
     * @return $this
     */
    public function forget(?string $path = null): self
    {
        return $this->delete($path);
    }

    /**
     * Return the value of the setting at the given path.
     *
     * @param string|null $path
     * @param mixed       $default
     *
     * @return mixed
     */
    public function get(?string $path = null, $default = null)
    {
        return $path ? Arr::get($this->all(), $path, $default) : $this->all();
    }

    /**
     * Determine if the model has the given setting.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has(string $path): bool
    {
        return Arr::has($this->all(), $path);
    }

    /**
     * Update the setting at given path to the given value.
     *
     * @param string|null $path
     * @param mixed       $value
     *
     * @return $this
     */
    public function set(?string $path = null, $value = []): self
    {
        if (func_num_args() < 2) {
            $value = $path;
            $path = null;
        }

        $settings = $this->all()->toArray();
        Arr::set($settings, $path, $value);

        return $this->apply($settings);
    }

    /**
     * Update the setting at the given path if it exists.
     *
     * @alias  set()
     *
     * @param string $path
     * @param mixed  $value
     *
     * @return $this
     */
    public function update(string $path, $value): self
    {
        return $this->set($path, $value);
    }

    /**
     * Resets either all or the given setting.
     *
     * @param null|string $path
     *
     * @return Settings
     */
    public function reset(?string $path = null): self
    {
        /** @var Collection $default */
        $default = $this->model->getDefaultSettings();
        if ($path) {
            return $this->set($path, $default->get($path, null));
        }

        return $this->apply($default);
    }
}
