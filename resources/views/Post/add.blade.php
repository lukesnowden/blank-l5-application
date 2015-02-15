
{!! var_dump($errors) !!}

{!! Form::open( array( 'route' => 'posts.add.process' ) ) !!}

	<div class="form-group">
		{!! Form::label( 'name', 'name' ) !!}
		{!! Form::text( 'name', null, array( 'class' => 'form-control' ) ) !!}
	</div>

	<div class="form-group">
		{!! Form::label( 'slug', 'slug' ) !!}
		{!! Form::text( 'slug', null, array( 'class' => 'form-control' ) ) !!}
	</div>

	<div class="form-group">
		{!! Form::label( 'category_id', 'category_id' ) !!}
		{!! Form::text( 'category_id', null, array( 'class' => 'form-control' ) ) !!}
	</div>

	{!! Form::submit( 'Create', array( "class" => "btn btn-primary" )) !!}


{!! Form::close() !!}