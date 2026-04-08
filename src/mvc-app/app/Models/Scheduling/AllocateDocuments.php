<?php

namespace App\Models\Scheduling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllocateDocuments extends Model
{
    use HasFactory;


     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'AllocateDocuments';

     /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ID';
}
