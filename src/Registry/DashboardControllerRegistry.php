<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Registry;

use EasyCorp\Bundle\EasyAdminBundle\Cache\CacheWarmer;
use function Symfony\Component\String\u;

final class DashboardControllerRegistry implements DashboardControllerRegistryInterface
{
    private bool $init = false;
    /** @var array<string, string> */
    private array $controllerFqcnToRouteMap = [];
    /** @var array<string, string> */
    private array $routeToControllerFqcnMap = [];

    /**
     * @param string[] $controllerFqcnToContextIdMap
     * @param string[] $contextIdToControllerFqcnMap
     */
    public function __construct(
        private string $buildDir,
        private readonly array $controllerFqcnToContextIdMap,
        private readonly array $contextIdToControllerFqcnMap,
    ) {
    }

    public function getControllerFqcnByContextId(string $contextId): ?string
    {
        return $this->contextIdToControllerFqcnMap[$contextId] ?? null;
    }

    public function getContextIdByControllerFqcn(string $controllerFqcn): ?string
    {
        return $this->controllerFqcnToContextIdMap[$controllerFqcn] ?? null;
    }

    public function getControllerFqcnByRoute(string $routeName): ?string
    {
        if (!$this->init) {
            $this->loadCache();
        }

        return $this->routeToControllerFqcnMap[$routeName] ?? null;
    }

    public function getRouteByControllerFqcn(string $controllerFqcn): ?string
    {
        if (!$this->init) {
            $this->loadCache();
        }

        return $this->controllerFqcnToRouteMap[$controllerFqcn] ?? null;
    }

    public function getNumberOfDashboards(): int
    {
        return \count($this->controllerFqcnToContextIdMap);
    }

    public function getFirstDashboardRoute(): ?string
    {
        return \count($this->controllerFqcnToRouteMap) < 1 ? null : $this->controllerFqcnToRouteMap[array_key_first($this->controllerFqcnToRouteMap)];
    }

    public function getFirstDashboardFqcn(): ?string
    {
        return \count($this->controllerFqcnToRouteMap) < 1 ? null : array_key_first($this->controllerFqcnToRouteMap);
    }

    public function getAll(): array
    {
        $dashboards = [];
        foreach ($this->controllerFqcnToContextIdMap as $controllerFqcn => $contextId) {
            $dashboards[] = [
                'controller' => $controllerFqcn,
                'route' => $this->controllerFqcnToRouteMap[$controllerFqcn] ?? null,
                'context' => $contextId,
            ];
        }

        return $dashboards;
    }

    private function loadCache(): void
    {
        $dashboardRoutesCachePath = $this->buildDir.'/'.CacheWarmer::DASHBOARD_ROUTES_CACHE;
        $dashboardControllerRoutes = !file_exists($dashboardRoutesCachePath) ? [] : require $dashboardRoutesCachePath;

        foreach ($dashboardControllerRoutes as $routeName => $controller) {
            $this->controllerFqcnToRouteMap[u($controller)->before('::')->toString()] = $routeName;
        }

        $this->routeToControllerFqcnMap = array_flip($this->controllerFqcnToRouteMap);
    }
}
