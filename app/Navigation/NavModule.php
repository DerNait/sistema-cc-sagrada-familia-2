<?php
namespace App\Navigation;

class NavModule
{
    public const CRUD  = ['create', 'read', 'update', 'delete'];
    public const READ  = ['read'];
    public const EXPORT  = ['read', 'export'];
    public const NONE  = [];

    public string  $label;
    public string  $key;
    public int     $order;
    public ?string $icon;
    public ?string $parentKey;
    public array   $actions;
    public string  $routeName;
    public string  $menuAction;

    public function __construct(
        string  $label,
        string  $key,
        int     $order,
        ?string $icon       = null,
        ?string $parentKey  = null,
        array   $actions    = self::CRUD,
        string  $menuAction = 'read',
        ?string $routeName  = null
    ) {
        $this->label      = $label;
        $this->key        = $key;
        $this->order      = $order;
        $this->icon       = $icon;
        $this->parentKey  = $parentKey;
        $this->actions    = $actions;
        $this->menuAction = $menuAction;

        if ($routeName) {
            // si explícitamente me pasaron uno, lo uso
            $this->routeName = $routeName;
        } else {
            $this->routeName = "{$key}.index";
        }
    }
}