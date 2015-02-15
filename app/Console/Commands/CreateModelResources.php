<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\AppNamespaceDetectorTrait;

class tempTrait {

	use AppNamespaceDetectorTrait;

	/**
	 * [appNamespace description]
	 * @return [type] [description]
	 */
	public function appNamespace() {
		return $this->getAppNamespace();
	}

}

class CreateModelResources extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'lukesnowden:model';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Creates a model with Controller, Interface and Repository.';

	/**
	 * [$templatesPath description]
	 * @var string
	 */
	protected $templatesPath = './';

	/**
	 * [__construct description]
	 */
	public function __construct()
	{
		parent::__construct();
		$this->templatesPath = base_path('resources/templates/');
	}

	/**
	 * [fire description]
	 * @return [type] [description]
	 */
	public function fire()
	{
		$options = $this->option();
		$name = $this->argument('name');
		$this->build( $options, $name );
	}

	/**
	 * [build description]
	 * @param  [type] $options [description]
	 * @param  [type] $name    [description]
	 * @return [type]          [description]
	 */
	private function build( $options, $name ) {
		$name = ucfirst( $name );
		$controllerName = "{$name}Controller";
		$interfaceName = "{$name}Interface";
		$repositoryName = "{$name}Repository";
		$requestName = "{$name}Request";
		$migrationName = "Create" . str_plural( $name ) . "Table";
		$tableName = str_plural( strtolower( $name ) );
		$modelName = $name;
		$fields = array_filter( explode( ',', $options['fields'] ?: '' ) );

		$controllersFolder = app_path('Http/Controllers/');
		$tempTrait = new tempTrait();
		$namespace = $tempTrait->appNamespace() . 'Http';
		$viewsFolder = base_path('resources/views/');

		// Create Folders
		$controllersFolder = $this->createIfDoesntExist( $controllersFolder );
		$interfacesFolder = $this->createIfDoesntExist( $controllersFolder . '../Interfaces/' );
		$repositoriesFolder = $this->createIfDoesntExist( $controllersFolder . '../Repositories/' );
		$modelsFolder = $this->createIfDoesntExist( $controllersFolder . '../Models/' );
		// Create Resources
		$this->createController( $controllersFolder, $controllerName, $interfaceName, $namespace, $requestName );
		$this->createAbstractRepository( $repositoriesFolder, $namespace );
		$this->createInterface( $interfacesFolder, $interfaceName, $namespace );
		$this->createRepository( $repositoriesFolder, $repositoryName, $interfaceName, $modelName, $namespace, $fields );
		$this->createModel( $modelsFolder, $modelName, $tableName, $namespace );
		$this->createRequest( $controllersFolder, $requestName, $namespace, $fields );
		$this->createMigration( $migrationName, $tableName, $fields );
		$this->createViews( $name, $viewsFolder, $tableName, $fields );
		$this->displayRouteSuggesstions( $tableName, $controllerName, $namespace );
		$this->displayBindingSuggestion( $interfaceName, $repositoryName, $namespace );

		if ( $this->confirm( 'Do you want to create a new one with the same arguments? [yes|no]' ) ) {
		    $name = $this->ask('What is the name? (i.e user)');
		    $options['fields'] = $this->ask('Fields? (blank for no fields):');
		    $this->build( $options, $name );
		}
	}

	/**
	 * [displayBindingSuggestion description]
	 * @param  [type] $interfaceName  [description]
	 * @param  [type] $repositoryName [description]
	 * @return [type]                 [description]
	 */
	private function displayBindingSuggestion( $interfaceName, $repositoryName, $namespace ) {
		$this->info("\nBinding suggestion for " . app_path('Providers/AppServiceProvider.php') );
		$this->comment('$this->app->bind("' . "{$namespace}\Interfaces\\{$interfaceName}" . '", "' . "{$namespace}\Repositories\\{$repositoryName}" . '");' . "\n");
	}

	/**
	 * [createRequest description]
	 * @param  [type] $controllersFolder [description]
	 * @param  [type] $namespace         [description]
	 * @return [type]                    [description]
	 */
	private function createRequest( $folder, $name, $namespace, $fields ) {

		$request = $this->createIfDoesntExist( $folder . "../Requests/{$name}.php", true );
		$contents = file_get_contents( $this->templatesPath . 'request.php.txt' );

		$rules = '';
		foreach( $fields as $field ) {
			if( $field == '' ) continue;
			$rules .= "\n\t\t\t'{$field}' => 'required',";
		}

		$contents = str_replace( array(
			'{%NAMESPACE%}',
			'{%REQUEST%}',
			'{%RULES%}'
		), array(
			$namespace,
			$name,
			rtrim( $rules ) . "\n"
		), $contents );
		file_put_contents( $request, $contents );
	}

	/**
	 * [displayRouteSuggesstions description]
	 * @param  [type] $tableName      [description]
	 * @param  [type] $controllerName [description]
	 * @param  [type] $namespace      [description]
	 * @return [type]                 [description]
	 */
	private function displayRouteSuggesstions( $tableName, $controllerName, $namespace ) {
		$this->info("\nRoute suggestion:");
		$this->comment("Route::group( ['prefix' => '{$tableName}'], function() {");
		$this->comment("	Route::get( '/', 				['as' => '{$tableName}', 				'uses' => '{$controllerName}@lists' ]);");
		$this->comment("	Route::get( '/add', 			['as' => '{$tableName}.add', 			'uses' => '{$controllerName}@showAdd' ]);");
		$this->comment("	Route::get( '/edit/{ID}', 		['as' => '{$tableName}.edit', 			'uses' => '{$controllerName}@showEdit' ]);");
		$this->comment("	Route::get( '/delete/{ID}', 	['as' => '{$tableName}.delete', 		'uses' => '{$controllerName}@delete' ]);");
		$this->comment("	Route::post('/edit/{ID}', 		['as' => '{$tableName}.edit.process', 	'uses' => '{$controllerName}@processEdit' ]);");
		$this->comment("	Route::post('/add', 			['as' => '{$tableName}.add.process', 	'uses' => '{$controllerName}@processAdd' ]);");
		$this->comment("});\n");
	}

	/**
	 * [createViews description]
	 * @param  [type] $name   [description]
	 * @param  [type] $folder [description]
	 * @return [type]         [description]
	 */
	private function createViews( $name, $folder, $tableName, $fields ) {
		$views = array('lists','add','edit');
		$path = $this->createIfDoesntExist( $folder . $name . '/' );
		foreach( $views as $view ) {
			$this->createIfDoesntExist( "{$path}{$view}.blade.php", true );
		}
		$_fields = '';
		foreach( $fields as $field ) {
			$_fields .= "<div class=\"form-group\">\n\t\t";
			$_fields .= "{!! Form::label( '{$field}', '{$field}' ) !!}\n\t\t";
			$_fields .= "{!! Form::text( '{$field}', null, array( 'class' => 'form-control' ) ) !!}\n\t";
			$_fields .= "</div>\n\n\t";
		}
		// Add
		$template = str_replace( array('{%TABLENAME%}','{%FIELDS%}'), array( $tableName, rtrim( $_fields ) . "\n" ), file_get_contents( $this->templatesPath . 'add.blade.php.txt' ) );
		file_put_contents( $path . "add.blade.php", $template );
		// Edit
		$template = str_replace( array('{%TABLENAME%}','{%FIELDS%}'), array( $tableName, rtrim( $_fields ) . "\n" ), file_get_contents( $this->templatesPath . 'edit.blade.php.txt' ) );
		file_put_contents( $path . "edit.blade.php", $template );
		// List
		$template = str_replace( array('{%TABLENAME%}'), array( $tableName ), file_get_contents( $this->templatesPath . 'lists.blade.php.txt' ) );
		file_put_contents( $path . "lists.blade.php", $template );
	}

	/**
	 * [createMigration description]
	 * @param  [type] $folder    [description]
	 * @param  [type] $tableName [description]
	 * @return [type]            [description]
	 */
	private function createMigration( $migrationName, $tableName, $fields ) {
		$folder = base_path('database/migrations/');
		$name = str_slug( date( "Y m d His" ), '_' ) . "_create_{$tableName}_table.php";
		$migration = $this->createIfDoesntExist( $folder . $name, true );
		$contents = file_get_contents( $this->templatesPath . 'migration.php.txt' );

		$_fields = '';
		foreach( $fields as $field ) {
			if( $field == '' ) continue;
			$_fields .= "\t\t\t" . '$table->string(\'' . $field . '\');' . "\n";
		}

		$contents = str_replace( array(
			'{%TABLENAME%}',
			'{%MIGRATION%}',
			'{%FIELDS%}'
		), array(
			$tableName,
			$migrationName,
			ltrim( $_fields )
		), $contents );
		file_put_contents( $migration, $contents );
	}

	/**
	 * [createController description]
	 * @param  [type] $controllersFolder [description]
	 * @param  [type] $controllerName    [description]
	 * @param  [type] $interfaceName     [description]
	 * @param  [type] $namespace         [description]
	 * @return [type]                    [description]
	 */
	private function createController( $folder, $controllerName, $interfaceName, $namespace, $request ) {
		$controller = $this->createIfDoesntExist( $folder . "{$controllerName}.php", true );
		$contents = file_get_contents( $this->templatesPath . 'controller.php.txt' );
		$contents = str_replace( array(
			'{%NAMESPACE%}',
			'{%INTERFACE%}',
			'{%CONTROLLER%}',
			'{%REQUEST%}'
		), array(
			$namespace,
			$interfaceName,
			$controllerName,
			$request
		), $contents );
		file_put_contents( $controller, $contents );
	}

	/**
	 * [createModel description]
	 * @param  [type] $folder    [description]
	 * @param  [type] $modelName [description]
	 * @param  [type] $tableName [description]
	 * @param  [type] $namespace [description]
	 * @return [type]            [description]
	 */
	private function createModel( $folder, $modelName, $tableName, $namespace ) {
		$model = $this->createIfDoesntExist( $folder . "{$modelName}.php", true );
		$contents = file_get_contents( $this->templatesPath . 'model.php.txt' );
		$contents = str_replace( array(
			'{%NAMESPACE%}',
			'{%TABLENAME%}',
			'{%MODEL%}'
		), array(
			$namespace,
			$tableName,
			$modelName
		), $contents );
		file_put_contents( $model, $contents );
	}

	/**
	 * [createRepository description]
	 * @param  [type] $folder         [description]
	 * @param  [type] $repositoryName [description]
	 * @param  [type] $namespace      [description]
	 * @return [type]                 [description]
	 */
	private function createRepository( $folder, $repositoryName, $interfaceName, $modelName, $namespace,  $fields ) {
		$repository = $this->createIfDoesntExist( $folder . "{$repositoryName}.php", true );
		$contents = file_get_contents( $this->templatesPath . 'repository.php.txt' );

		$contents = str_replace( array(
			'{%NAMESPACE%}',
			'{%REPOSITORY%}',
			'{%INTERFACE%}',
			'{%MODEL%}'
		), array(
			$namespace,
			$repositoryName,
			$interfaceName,
			$modelName,
		), $contents );
		file_put_contents( $repository, $contents );
	}

	/**
	 * [createInterface description]
	 * @param  [type] $folder        [description]
	 * @param  [type] $interfaceName [description]
	 * @param  [type] $namespace     [description]
	 * @return [type]                [description]
	 */
	private function createInterface( $folder, $interfaceName, $namespace ) {
		$interface = $this->createIfDoesntExist( $folder . "{$interfaceName}.php", true );
		$contents = file_get_contents( $this->templatesPath . 'interface.php.txt' );
		$contents = str_replace( array(
			'{%NAMESPACE%}',
			'{%INTERFACE%}'
		), array(
			$namespace,
			$interfaceName
		), $contents );
		file_put_contents( $interface, $contents );
	}

	/**
	 * [createAbstractRepository description]
	 * @param  [type] $folder    [description]
	 * @param  [type] $namespace [description]
	 * @return [type]            [description]
	 */
	private function createAbstractRepository( $folder, $namespace ) {
		$abstractRepository = $this->createIfDoesntExist( $folder . "Repository.php", true );
		$contents = file_get_contents( $this->templatesPath . 'abstract-repository.php.txt' );
		$contents = str_replace( '{%NAMESPACE%}', $namespace, $contents );
		file_put_contents( $abstractRepository, $contents );
	}

	/**
	 * [createIfDoesntExist description]
	 * @param  [type] $path [description]
	 * @return [type]       [description]
	 */
	private function createIfDoesntExist( $path, $file = false ) {
		if( ! file_exists( $path ) ) {
			if( $file ) {
				file_put_contents( $path, '' );
			} else {
				mkdir( $path, 0755 );
			}
		}
		return $path;
	}

	/**
	 * [getArguments description]
	 * @return [type] [description]
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'name must be supplied --name="Users".'],
		];
	}

	/**
	 * [getOptions description]
	 * @return [type] [description]
	 */
	protected function getOptions()
	{
		return [
			['fields', null, InputOption::VALUE_OPTIONAL, 'Addes the fields to the migration (default string) and rules array', null]
		];
	}

}
