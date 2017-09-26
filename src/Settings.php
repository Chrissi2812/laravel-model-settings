<?php

namespace Cklmercer\ModelSettings;

use Illuminate\Database\Eloquent\Model;
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
        $this->cacheKey = "user.$model->id.settings";
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
    public function apply($settings = []): Settings
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
    public function delete(?string $path = null): Settings
    {
        if (! $path) {
            return $this->set([]);
        }

        $settings = $this->all()->forget($path);

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
    public function forget(?string $path = null): Settings
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
        return $path ? $this->all()->get($path, $default) : $this->all();
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
        return $this->all()->has($path);
    }

    /**
     * Update the setting at given path to the given value.
     *
     * @param string|null $path
     * @param mixed       $value
     *
     * @return $this
     */
    public function set(?string $path = null, $value = []): Settings
    {
        if (func_num_args() < 2) {
            $value = $path;
            $path = null;
        }

        $settings = $this->all()->put($path, $value);

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
    public function update(string $path, $value): Settings
    {
        return $this->set($path, $value);
    }
}
