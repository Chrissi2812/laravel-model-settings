<?php

namespace Cklmercer\ModelSettings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait HasSettings
{
    protected static $settingsInstance;

    /**
     * Boot the HasSettings trait.
     *
     * @return void
     */
    public static function bootHasSettings(): void
    {
        self::creating(function (Model $model) {
            if (!$model->settings) {
                $model->settings = $model->getDefaultSettings();
            }
        });

        self::saving(function (Model $model) {
            if ($model->settings && property_exists($model, 'allowedSettings') && is_array($model->allowedSettings)) {
                $model->settings = $model->settings->only($model->allowedSettings);
            }
        });
    }

    /**
     * Get the model's default settings.
     *
     * @return Collection
     */
    public function getDefaultSettings(): Collection
    {
        return collect((isset($this->defaultSettings) && is_array($this->defaultSettings))
            ? $this->defaultSettings
            : []);
    }

    /**
     * Get the settings attribute.
     *
     * @param string|null $settings
     *
     * @return Collection
     */
    public function getSettingsAttribute(?string $settings): Collection
    {
        return collect(json_decode($settings, true));
    }

    /**
     * Set the settings attribute.
     *
     * @param array|Collection $settings
     *
     * @return void
     */
    public function setSettingsAttribute($settings): void
    {
        $this->attributes['settings'] = (is_array($settings)) ? json_encode($settings) : $settings->toJson();
    }

    /**
     * The model's settings.
     *
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return Settings|mixed
     */
    public function settings(?string $key = null, $default = null)
    {
        return $key ? $this->settings()->get($key, $default) : $this->getSettingsInstance();
    }

    /**
     * Get an instance of Settings.
     *
     * @return Settings
     */
    private function getSettingsInstance(): Settings
    {
        if (is_null(self::$settingsInstance) || self::$settingsInstance->getModel() !== $this) {
            self::$settingsInstance = new Settings($this);
        }

        return self::$settingsInstance;
    }
}
