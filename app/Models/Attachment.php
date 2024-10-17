<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attachment extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'created_by',
        'name',
    ];

    public function attachmentable (){

        return $this->morphTo();
    }

    public function user () {

        return $this->belongsTo(User::class , 'created_by');
    }
}
