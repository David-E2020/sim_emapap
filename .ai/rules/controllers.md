# Controller Conventions

## Validation
- Use inline `Validator::make($request->all(), [...])` inside controller methods instead of separate Form Request classes.
- Use pipe-separated validation rules (`'required|string|max:100'`).
- On validation failure, return standard unprocessable entity JSON:
  ```php
  if ($validator->fails()) {
      return response()->json([
          'success' => false,
          'message' => $validator->errors()->first(),
      ], Response::HTTP_UNPROCESSABLE_ENTITY);
  }
  ```

## Response Format
- Return standard JSON responses with HTTP status codes from `Symfony\Component\HttpFoundation\Response` or `Illuminate\Http\Response`:
  - **Success**:
    ```php
    return response()->json([
        'success' => true,
        'data' => $data,
        'message' => 'Operación completada exitosamente',
    ], Response::HTTP_OK);
    ```
  - **Paginated Listing**:
    ```php
    return response()->json([
        'success' => true,
        'data' => $paginator->items(),
        'total' => $paginator->total(),
        'current_page' => $paginator->currentPage(),
        'last_page' => $paginator->lastPage(),
    ], Response::HTTP_OK);
    ```

## Database Transactions & Audit
- Wrap multi-table modifications in `DB::transaction(function () use (...) { ... })`.
- Record user audit trails using `AuditService::log()` for critical entity mutations.
