<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UmbralesCcaa
 * 
 * @property int $c_id
 * @property string $c_comunidad_autonoma
 * @property string|null $c_nombre_corto
 * 
 * @property Collection|UmbralesEmbalsesran[] $umbrales_embalsesrans
 * @property Collection|UmbralesRanepisodio[] $umbrales_ranepisodios
 * @property Collection|UmbralesUmbralesran[] $umbrales_umbralesrans
 *
 * @package App\Models
 */
class UmbralesCcaa extends Model
{
	protected $table = 'umbrales_ccaa';
	protected $primaryKey = 'c_id';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'c_id' => 'int'
	];

	protected $fillable = [
		'c_comunidad_autonoma',
		'c_nombre_corto'
	];

	public function umbrales_embalsesrans()
	{
		return $this->hasMany(UmbralesEmbalsesran::class, 'er_comunidad_autonoma_id');
	}

	public function umbrales_ranepisodios()
	{
		return $this->hasMany(UmbralesRanepisodio::class, 're_ccaa_id');
	}

	public function umbrales_umbralesrans()
	{
		return $this->hasMany(UmbralesUmbralesran::class, 'ur_comunidad_autonoma_id');
	}
}
