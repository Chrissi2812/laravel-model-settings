<?php

namespace Cklmercer\ModelSettings;

use Illuminate\Database\Eloquent\Model;

trait HasSettings
{
    protected static $instance;

    /**
     * Boot the HasSettings trait.
     *
     * @return void
     */
    public static function bootHasSettings(): void
    {
        self::creating(function (Model $model) {
            if (! $model->settings) {
                $model->settings = $model->getDefaultSettings();
            }
        });

        self::saving(function (Model $model) {
            if ($model->settings && property_exists($model, 'allowedSettings') && is_array($model->allowedSettings)) {
                $model->settings = array_only($model->settings, $model->allowedSettings);
            }
        });
    }

    /**
     * Get the model's default settings.
     *
     * @return array
     */
    public function getDefaultSettings(): array
    {
        return (isset($this->defaultSettings) && is_array($this->defaultSettings))
            ? $this->defaultSettings
            : [];
    }

    /**
     * Get the settings attribute.
     *
     * @param string $settings
     *
     * @return array|null
     */
    public function getSettingsAttribute(string $settings): ?array
    {
        return json_decode($settings, true);
    }

    /**
     * Set the settings attribute.
     *
     * @param array $settings
     *
     * @return void
     */
    public function setSettingsAttribute(array $settings): void
    {
        $this->attributes['settings'] = json_encode($settings);
    }

    /**
     * The model's settings.
     *
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return Settings
     */
    public function settings(?string $key = null, $default = null): Settings
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
        if (is_null(static::$instance)) {
            static::$instance = new Settings($this);
        }

        return static::$instance;
    }
}
