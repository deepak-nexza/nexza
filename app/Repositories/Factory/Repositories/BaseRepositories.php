<?php

namespace App\B2c\Repositories\Factory\Repositories;

use BadMethodCallException;
use App\B2c\Repositories\Models\Master\Country;
use App\B2c\Repositories\Models\Master\State;
use App\B2c\Repositories\Models\Master\Disclosure;

/**
 * Base class for all repository classes
 */
abstract class BaseRepositories
{

    /**
     * Laravel container object
     *
     * @var object
     */
    protected $app;

    /**
     * Create method
     *
     * @param array $attributes
     */
    abstract protected function create(array $attributes);

    /**
     * Update method
     *
     * @param array $attributes
     */
    abstract protected function update(array $attributes, $id);

    /**
     * Delete method
     *
     * @param mixed $ids
     */
    abstract protected function destroy($ids);

    /**
     * Class constructor
     *
     * @param void
     * @return void
     * @since 0.1
     */
    public function __construct()
    {
        $this->app = app();
        $this->boot();
    }

    /**
     * Boot all required methods from here
     *
     * @param void
     * @return void
     * @since 0.1
     */
    protected function boot()
    {
        $this->runOnce();
    }

    /**
     * Call all methods from here those would be executed only once
     * while two child classes extends this base class at one in case
     * of dependency injection
     *
     * @param void
     * @return void
     * @since 0.1
     */
    protected function runOnce()
    {
        static $executed = false;

        if ($executed === false) {
            // Call all methods from here those would be executed only once
            $this->setConfig();

            $executed = true;
        }
    }

    /**
     * Set all configs at the run time
     *
     * @param void
     * @return void
     * @since 0.1
     */
    protected function setConfig()
    {
        $configs = glob(__DIR__ . '/../../config/*.php');

        foreach ($configs as $config) {
            // Take out the basename from the filename with path
            $value = pathinfo($config, PATHINFO_FILENAME);

            // Merge the config items
            $this->mergeConfigFrom($config, 'b2c_' . $value);
        }
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app['config']->get($key, []);

        $this->app['config']->set($key, array_merge(require $path, $config));
    }

    /**
     * List all countries
     *
     * @param void
     * @return mixed Illuminate\Database\Eloquent\Model | boolean
     * @since 0.1
     */
    public function getCountries()
    {
        $result = Country::all()->where(
            'status',
            config('b2c_common.ACTIVE')
        )->orderBy('country_name', 'ASC');

        return $result ? : false;
    }

    /**
     * List all states for a country
     *
     * @param interger $country_id
     * @return mixed Illuminate\Database\Eloquent\Model | boolean
     */
    public function getStatesByCountry($country_id)
    {
        return Country::getAllStates($country_id);
    }

    /**
     * List all cities for a state
     *
     * @param interger $state_id
     * @return mixed Illuminate\Database\Eloquent\Model | boolean
     */
    public function getCitiesByState($state_id)
    {
        return State::getAllCities($state_id);
    }

    /**
     * List all disclousre
     *
     * @return mixed Illuminate\Database\Eloquent\Model | boolean
     */
    public function getDisclousre()
    {
        return Disclosure::getAllDisclosure();
    }

    /**
     * Handle calls to missing methods on the repository.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException("Method [$method] does not exist.");
    }
}
