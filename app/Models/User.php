<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject {
	use Notifiable;
	use HasRoles;

	protected $table      = 'public.users';
	protected $primaryKey = "id";
	protected $guard_name = 'api';
	public $timestamps    = false;
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'password', 'usr_estado', 'id', 'usr_usuario', 'deleted_at', 'usr_externo_id',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	/**
	 * Get the identifier that will be stored in the subject claim of the JWT.
	 *
	 * @return mixed
	 */
	public function getJWTIdentifier() {
		return $this->getKey();
	}

	/**
	 * Return a key value array, containing any custom claims to be added to the JWT.
	 *
	 * @return array
	 */
	public function getJWTCustomClaims() {
		return [];
	}

	public function puntoventa() {
		return $this->belongsTo(PuntoventaUser::class, 'usr_id', 'id')->select(['id', 'puntoventa_id', 'usr_id']);
	}

	public function planta() {
		return $this->belongsTo(PlantaUsuario::class, 'usr_id', 'id')->select(['id', 'planta_id', 'usr_id']);
	}
	#rol user
	public function rolPersmisos() {
		return $this->hasOne(RolUser::class, 'id', 'id');
	}

	public function getSellingPoints() {
		if ($this->hasRole('Administrador')) {
			$storages = Planta::select('id', 'nombre', 'descripcion', 'codigo', 'tipo_acopio_id', 'municipio', 'telefono', 'direccion', 'capacidad', 'departamento_id', 'provincia_id', 'municipio_id', 'localidad_id', 'datos')->get();
		} else {
			$storages = $this->sellingpoints;
		}
		return $storages;
	}

	public function employee() {
		return $this->belongsTo('App\Models\RRHH\Employee', 'usr_prs_id', 'id')->with('management');
	}

	public function sellingpoints() {
		return $this->belongsToMany('App\Models\Planta', 'planta_usuarios', 'user_id', 'planta_id');
	}

	public function getSellingPoint() {
		if (!session()->exists('planta_id')) {
			if (sizeof($this->sellingpoints) > 0) {
				session()->put('planta_id', $this->sellingpoints[0]->id);
			} else {
				return null;
			}
		}
		$selling_point = Planta::where('id', session('planta_id'))->first();
		return $selling_point;
	}

	//accesos para el SEDEM

	public function getSellingPointsSEDEM() {
		$storages = $this->sellingpointsSEDEM;
		return $storages;
	}

	public function sellingpointsSEDEM() {
		return $this->belongsToMany('App\Models\Almacen', 'App\Models\UsuarioAlmacen', 'user_id', 'almacen_id')->with('sucursal','loginsubsidio');
	}

	public function getUser($id) {
		$usuario = $this->find($id);
		return $usuario;
	}

	public function getGestion() {
		$gestion = Gestion::where('state', true)->first();
		return $gestion;
	}

	public function getGestions() {
		$gestions = Gestion::all();
		return $gestions;
	}

	public function getGestionSession() {
		if (!session()->exists('gestion_id')) {
			session()->put('gestion_id', self::getGestion()->id);
		}

		$gestion = Gestion::find(session('gestion_id'));
		return $gestion;
	}
	public function user_cargos(){
        return $this->belongsTo(Cargos::class, 'usr_cargo_id', 'id');
 }
}
