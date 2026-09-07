# Database & Migration Conventions

## PostgreSQL Schemas
- Migrations must create tables with their schema prefix:
  - `Schema::create('rrhh.nombre_tabla', function (Blueprint $table) { ... });`
  - `Schema::create('correspondencia.nombre_tabla', function (Blueprint $table) { ... });`

## Standard Audit Columns
- Every table migration must include standard audit metadata columns instead of `$table->timestamps()`:
  ```php
  $table->string('_estado', 20)->default('ACTIVO');
  $table->string('_transaccion', 20)->default('CREAR');
  $table->unsignedBigInteger('_usuario_creacion')->default(1);
  $table->timestamp('_fecha_creacion')->useCurrent();
  $table->unsignedBigInteger('_usuario_modificacion')->nullable();
  $table->timestamp('_fecha_modificacion')->nullable();
  ```

## Foreign Key References
- Foreign keys must point to the fully-qualified schema table:
  ```php
  $table->foreignId('id_persona')->constrained('rrhh.personas')->cascadeOnDelete();
  ```
