<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Comment extends Model
{
    use HasFactory,SoftDeletes;

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
