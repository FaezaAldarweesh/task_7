<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

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
