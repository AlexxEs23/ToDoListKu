<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ToDo extends Model
{
    protected $fillable = [
        'user_id',
        'judul_tugas',
        'deskripsi_tugas',
        'tanggal_selesai',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',

    ]; 
    
    protected function serializeDate($date){
        if ($date){
            $carbonDate = Carbon::parse($date);
            return $carbonDate->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
        }
        return null;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_todo', 'to_do_id', 'category_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
