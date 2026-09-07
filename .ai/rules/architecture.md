# Architecture Rules

## Domain & Modular Grouping
- All classes must be grouped under domain sub-namespaces:
  - `Administracion`: User management, roles, permissions, menus, audit.
  - `Rrhh`: Human resources, personnel, attendance, biometric clocks, organizational chart.
  - `Correspondencia`: Document generation, approval flows, digital signatures, routing sheets, tracking.
- Do not introduce repository interfaces or abstraction layers; query Eloquent directly in controllers and domain service classes.
- Put complex business logic, PDF generation, digital signing, and biometric device integration into dedicated `app/Services/{Domain}/` service classes.

## PHP 8 & Coding Standards
- Every PHP file must start with `declare(strict_types=1);`.
- Use PHP 8 constructor property promotion for dependency injection:
  ```php
  public function __construct(
      private readonly AuditService $auditService
  ) {}
  ```
- Always declare explicit return types on methods and functions (`: JsonResponse`, `: void`, `: bool`, etc.).
