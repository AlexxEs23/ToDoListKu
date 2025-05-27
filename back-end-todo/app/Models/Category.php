<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
    ];

    public function ToDo(){
        return $this->belongsToMany(ToDo::class, 'category_todo', 'category_id', 'to_do_id');
    }
}
