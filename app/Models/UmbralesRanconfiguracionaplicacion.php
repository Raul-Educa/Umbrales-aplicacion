<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UmbralesRanconfiguracionaplicacion
 * 
 * @property string $rca_clave
 * @property string $rca_valor
 * @property string $rca_descripcion
 *
 * @package App\Models
 */
class UmbralesRanconfiguracionaplicacion extends Model
{
	protected $table = 'umbrales_ranconfiguracionaplicacion';
	protected $primaryKey = 'rca_clave';
	public $incrementing = false;
	public $timestamps = false;

	protected $fillable = [
		'rca_valor',
		'rca_descripcion'
	];
}
