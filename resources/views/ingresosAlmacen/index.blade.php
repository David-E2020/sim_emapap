@extends('dashboard.base')
<style media="screen" type="text/css">
    .table-condensed>thead>tr>th, .table-condensed>tbody>tr>th, .table-condensed>tfoot>tr>th, .table-condensed>thead>tr>td, .table-condensed>tbody>tr>td, .table-condensed>tfoot>tr>td{
    padding: 5px;
    font-size: 12px;
}
table.dataTable tbody th, table.dataTable tbody td {
    padding: 0px 10px;
    font-size: 12px;
}
</style>

@section('breadcrums')
    {{-- {{ Breadcrumbs::render('action_medium_term') }} --}}
@endsection

@section('content')
<div class="row">
	<div class="col-md-12">
		<ingreso-comex></ingreso-comex>
	</div>
</div>
@endsection
