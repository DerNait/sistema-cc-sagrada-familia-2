<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modulos';
    
    public function permissions() { 
        return $this->hasMany(ModulePermission::class, 'modulo_id'); 
    }
}
