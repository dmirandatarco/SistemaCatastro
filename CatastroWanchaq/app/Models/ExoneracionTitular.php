<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ExoneracionTitular extends Authenticatable implements AuditableContract
{
    use HasFactory, Notifiable, Auditable;
    public $timestamps = false;
    protected $table = 'tf_exoneraciones_titular';

    protected $fillable = [
        'id_persona',
        'condicion',
        'nume_resolucion',
        'nume_boleta_pension',
        'fecha_inicio',
        'fecha_vencimiento',
    ];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id_ficha';

    public function getRouteKeyName()
    {
        return 'id_ficha';
    }

    public function getKeyName()
    {
        return 'id_ficha';
    }

    public function persona()
    {
        return $this->belongsTo('App\Models\Persona');
    }
    public function ficha()
    {
        return $this->belongsTo('App\Models\Ficha');
    }
}