# Model Conventions

## Table Names & PostgreSQL Schemas
- Explicitly declare the schema prefix in the model's `$table` property:
  - `protected $table = 'rrhh.personas';`
  - `protected $table = 'rrhh.fichas_personales';`
  - `protected $table = 'correspondencia.documentos';`
  - `protected $table = 'correspondencia.hojas_ruta';`

## Timestamps & Lifecycle Fields
- Always disable default Laravel timestamps:
  ```php
  public $timestamps = false;
  ```
- All models must include custom audit and transaction tracking columns in `$fillable`:
  - `_estado` (default `'ACTIVO'`)
  - `_transaccion` (default `'CREAR'`)
  - `_usuario_creacion`
  - `_fecha_creacion`
  - `_usuario_modificacion`
  - `_fecha_modificacion`

## Relationships & Accessors
- Type-hint all Eloquent relationship return types: `: BelongsTo`, `: HasMany`, `: HasOne`, `: BelongsToMany`.
- Use the classic magic accessor style when defining computed attributes:
  ```php
  public function getNombreCompletoAttribute(): string
  {
      return trim("{$this->nombres} {$this->primer_apellido} {$this->segundo_apellido}");
  }
  ```
