<?php

namespace Admingate\Sitemap\Providers;

use Admingate\Base\Events\CreatedContentEvent;
use Admingate\Base\Events\DeletedContentEvent;
use Admingate\Base\Events\UpdatedContentEvent;
use Admingate\Base\Traits\LoadAndPublishDataTrait;
use Admingate\Sitemap\Sitemap;
use Illuminate\Support\ServiceProvider;

class SitemapServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    protected bool $defer = true;

    public function boot(): void
    {
        $this->setNamespace('packages/sitemap')
            ->loadAndPublishConfigurations(['config'])
            ->loadAndPublishViews()
            ->publishAssets();

        $this->app['events']->listen(CreatedContentEvent::class, function () {
            cache()->forget('cache_site_map_key');
        });

        $this->app['events']->listen(UpdatedContentEvent::class, function () {
            cache()->forget('cache_site_map_key');
        });

        $this->app['events']->listen(DeletedContentEvent::class, function () {
            cache()->forget('cache_site_map_key');
        });
    }

    public function register(): void
    {
        $this->app->bind('sitemap', function ($app) {
            $config = config('packages.sitemap.config');

            return new Sitemap(
                $config,
                $app['Illuminate\Cache\Repository'],
                $app['config'],
                $app['files'],
                $app['Illuminate\Contracts\Routing\ResponseFactory'],
                $app['view']
            );
        });

        $this->app->alias('sitemap', Sitemap::class);
    }

    public function provides(): array
    {
        return ['sitemap', Sitemap::class];
    }
}
