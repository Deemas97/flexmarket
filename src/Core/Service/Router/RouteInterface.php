<?php
namespace App\Core\Service\Router;

interface RouteInterface
{
    public function getControllerName(): string;
    public function getMethodName(): string;
}
