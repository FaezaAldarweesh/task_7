<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'comment',
    ];

    public function commentable (){

        return $this->morphTo();
    }

    public function user () {

        return $this->belongsTo(User::class , 'created_by');
    }
}
