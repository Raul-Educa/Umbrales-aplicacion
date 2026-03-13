<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UmbralesProvincia
 * 
 * @property int $p_id
 * @property string $p_provincia
 *
 * @package App\Models
 */
class UmbralesProvincia extends Model
{
	protected $table = 'umbrales_provincias';
	protected $primaryKey = 'p_id';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'p_id' => 'int'
	];

	protected $fillable = [
		'p_provincia'
	];
}
