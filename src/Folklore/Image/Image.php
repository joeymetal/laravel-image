<?php namespace Folklore\Image;

use Closure;
use Illuminate\Foundation\Application;

class Image
{
    protected $app;

    protected $urlGenerator;

    /**
     * All sources
     *
     * @var array
     */
    protected $factories = [];

    /**
     * All registered filters.
     *
     * @var array
     */
    protected $filters = [];

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->filters = $this->app['config']->get('image.filters', []);
    }

    /**
     * Get an ImageManipulator for a specific source
     *
     * @param  string|null  $name
     * @return Folklore\Image\ImageManipulator
     */
    public function source($name = null)
    {
        $key = $name ? $name:'default';

        if (isset($this->factories[$key])) {
            return $this->factories[$key];
        }

        $source = $this->app['image.source']->driver($name);
        $factory =  $this->app->make('image.manipulator');
        $factory->setSource($source);

        return $this->factories[$key] = $factory;
    }

    /**
     * Register a custom source creator Closure.
     *
     * @param  string    $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->app['image.source']->extend($driver, $callback);

        return $this;
    }

    /**
     * Register routes on the router
     *
     * @return void
     */
    public function routes()
    {
        return $this->app['image.router']->registerRoutesOnRouter();
    }

    /**
     * Return an URL to process the image
     *
     * @param  string  $src
     * @param  int     $width
     * @param  int     $height
     * @param  array   $options
     * @return string
     */
    public function url($src, $width = null, $height = null, $options = [])
    {
        return $this->app['image.url']->make($src, $width, $height, $options);
    }

    /**
     * Return an URL to process the image
     *
     * @param  string  $path
     * @return array
     */
    public function pattern($config = [])
    {
        return $this->app['image.url']->pattern($config);
    }

    /**
     * Return an URL to process the image
     *
     * @param  string  $path
     * @return array
     */
    public function parse($path, $config = [])
    {
        return $this->app['image.url']->parse($path, $config);
    }

    /**
     * Register a filter
     *
     * @param  string    $name
     * @param  \Closure|array|string|object  $filter
     * @return $this
     */
    public function filter($name, $filter)
    {
        $this->filters[$name] = $filter;

        return $this;
    }

    /**
     * Get all filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get a filter
     *
     * @param  string    $name
     * @return array|Closure|string|object
     */
    public function getFilter($name)
    {
        return array_get($this->filters, $name, null);
    }

    /**
     * Check if a filter exists
     *
     * @param  string    $name
     * @return boolean
     */
    public function hasFilter($name)
    {
        return $this->getFilter($name) !== null ? true:false;
    }

    /**
     * Get the imagine manager
     *
     * @return \Folklore\Image\ImageManager
     */
    public function getImagineManager()
    {
        return $this->app['image.imagine'];
    }

    /**
     * Get the imagine instance from the manager
     *
     * @return \Imagine\Image\ImagineInterface
     */
    public function getImagine()
    {
        $manager = $this->getImagineManager();
        return $manager->driver();
    }

    /**
     * Get the source manager
     *
     * @return \Folklore\Image\SourceManager
     */
    public function getSourceManager()
    {
        return $this->app['image.source'];
    }

    /**
     * Get the router
     *
     * @return \Folklore\Image\Router
     */
    public function getRouter()
    {
        return $this->app['image.router'];
    }

    /**
     * Get the url generator
     *
     * @return \Folklore\Image\UrlGenerator
     */
    public function getUrlGenerator()
    {
        return $this->app['image.url'];
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->source(), $method], $parameters);
    }
}
