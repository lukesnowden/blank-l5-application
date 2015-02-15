
{!! var_dump($errors) !!}

{!! Form::model( $model, array( 'route' => array( 'posts.edit.process', $model->id ) ) ) !!}

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


	{!! Form::submit( 'Edit', array( "class" => "btn btn-primary" ) ) !!}


{!! Form::close() !!}