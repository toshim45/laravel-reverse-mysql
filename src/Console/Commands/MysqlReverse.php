<?php

namespace Toshim45\LaravelReverseMysql\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MysqlReverse extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'reverse:mysql {table} {--c|controller} {--r|resource} {--s|stub}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reverse Mysql Table';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$table          = $this->argument('table');
		$controller     = $this->option('controller');
		$resource       = $this->option('resource');
		$rawTableStruct = DB::select(DB::raw("SHOW COLUMNS FROM `" . $table . "`"));
		$tableStruct    = $this->parseTableStruct($rawTableStruct);
		$className      = Str::studly(Str::singular($table));

		if ($controller) {
			$this->applyModel($table, $tableStruct, $className);
			$this->applyController($table, $tableStruct, $className);
			printf("Please add these line to your routes:\r\n");
			printf("\e[1;34;43mRoute::resource('%s', '%sController');\e[0m\r\n", $table, $className);
		}
		if ($resource) {
			$this->applyResource($table, $tableStruct);
		}
	}

	public function applyResource($table, $columns) {
		if (!file_exists(resource_path() . '/views/' . $table)) {
			mkdir(resource_path() . '/views/' . $table);
		}

		$this->applyIndexResource($table, $columns);
		$this->applyCreateResource($table, $columns);
		$this->applyShowResource($table, $columns);
		$this->applyEditResource($table, $columns);
	}

	public function applyEditResource($table, $columns) {
		$content = file_get_contents(resource_path() . '/stubs/edit.stub');
		if (!$content) {
			throw new Exception("Error: edit stub", 1);
		}

		$generated = [];
		foreach ($columns as $k => $v) {
			if (Str::startsWith($k, 'created') || Str::startsWith($k, 'updated')) {
				continue;
			}
			$generated[] = '<div class="form-group">';
			$generated[] = sprintf('{{ Form::label(\'%s\', \'%s\') }}', $k, Str::title($k));
			$generated[] = sprintf('{{ Form::text(\'%s\', $model->%s, array(\'class\' => \'form-control\')) }}', $k, $k);
			$generated[] = '</div>';
		}

		$content = str_replace('{{tableName}}', $table, $content);
		$content = str_replace('{{tableContent}}', implode("\r\n", $generated), $content);

		file_put_contents(resource_path() . '/views/' . $table . '/edit.blade.php', $content);
	}

	public function applyShowResource($table, $columns) {
		$content = file_get_contents(resource_path() . '/stubs/show.stub');
		if (!$content) {
			throw new Exception("Error: show stub", 1);
		}

		$generated = [];

		$generated[] = '<h2>{{$model->id}}</h2>';
		$generated[] = '<p>';
		foreach ($columns as $k => $v) {
			$generated[] = sprintf('<strong>%s</strong> : {{$model->%s}}</br>', Str::title(str_replace('_', ' ', $k)), $k);
		}
		$generated[] = '</p>';

		$content = str_replace('{{tableName}}', $table, $content);
		$content = str_replace('{{tableContent}}', implode("\r\n", $generated), $content);
		file_put_contents(resource_path() . '/views/' . $table . '/show.blade.php', $content);
	}

	public function applyCreateResource($table, $columns) {
		$content = file_get_contents(resource_path() . '/stubs/create.stub');
		if (!$content) {
			throw new Exception("Error: create stub", 1);
		}

		$generated = [];
		foreach ($columns as $k => $v) {
			if (Str::startsWith($k, 'created') || Str::startsWith($k, 'updated')) {
				continue;
			}
			$generated[] = '<div class="form-group">';
			$generated[] = sprintf('{{ Form::label(\'%s\', \'%s\') }}', $k, Str::title($k));
			$generated[] = sprintf('{{ Form::text(\'%s\', \'\', array(\'class\' => \'form-control\')) }}', $k);
			$generated[] = '</div>';
		}

		$content = str_replace('{{tableName}}', $table, $content);
		$content = str_replace('{{tableContent}}', implode("\r\n", $generated), $content);

		file_put_contents(resource_path() . '/views/' . $table . '/create.blade.php', $content);
	}

	public function applyFilterIndexResource($table, $columns) {
		$content = file_get_contents(resource_path() . '/stubs/filter.stub');
		if (!$content) {
			throw new Exception("Error: filter stub", 1);
		}

		$generated = [];
		foreach ($columns as $k => $v) {
			$generated[] = '<div class="form-group">';
			$generated[] = sprintf('{{ Form::label(\'%s\', \'%s\') }}', $k, Str::title($k));
			$generated[] = sprintf('{{ Form::text(\'%s\', \'\', array(\'class\' => \'form-control\')) }}', $k);
			$generated[] = '</div>';
		}
		$content = str_replace('{{tableName}}', $table, $content);
		$content = str_replace('{{tableContent}}', implode("\r\n", $generated), $content);

		file_put_contents(resource_path() . '/views/' . $table . '/filter.blade.php', $content);
	}

	public function applyIndexResource($table, $columns) {
		$content = file_get_contents(resource_path() . '/stubs/index.stub');
		if (!$content) {
			throw new Exception("Error: index stub", 1);
		}

		$this->applyFilterIndexResource($table, $columns);

		$generated   = [];
		$generated[] = '@if (session(\'status\'))';
		$generated[] = '<div class="alert alert-success">';
		$generated[] = '{{ session(\'status\') }}';
		$generated[] = '</div>';
		$generated[] = '@endif';
		$generated[] = '<table class="table table-striped table-bordered table-hover">';
		$generated[] = '<thead>';
		$generated[] = '<th>No</th>';
		foreach ($columns as $k => $v) {
			$generated[] = sprintf('<th>%s</th>', str_replace("_", " ", Str::title($k)));
		}
		$generated[] = '<tbody>';
		$generated[] = '@foreach ($models as $model)';
		$generated[] = '<tr>';
		$generated[] = '<td>{{ $loop->iteration + (10 * ($models->currentPage()-1)) }}</td>';
		foreach ($columns as $k => $v) {
			$generated[] = sprintf('<td>{{$model->%s}}</td>', $k);
		}
		$generated[] = sprintf('<td><a href="{{ URL::to(\'%s/\'.$model->id) }}"><i class="fa fa-search"></i>view</a>&nbsp;</td>', $table);
		$generated[] = '</tr>';
		$generated[] = '@endforeach';
		$generated[] = '</tbody>';
		$generated[] = '</thead>';
		$generated[] = '</table>';

		$filters = sprintf('@include(\'%s.filter\',[])', $table);

		$content = str_replace('{{tableFilter}}', $filters, $content);
		$content = str_replace('{{tableName}}', $table, $content);
		$content = str_replace('{{tableContent}}', implode("\r\n", $generated), $content);

		file_put_contents(resource_path() . '/views/' . $table . '/index.blade.php', $content);
	}

	public function applyModel($table, $columns, $className) {
		$modelName = Str::camel(Str::singular($table));

		$fileModel = app_path() . '/' . $className . '.php';
		$content   = file_get_contents($fileModel);
		if (!$content) {
			throw new Exception("Error: controller " . $fileModel, 1);
		}

		$generated = [];
		foreach ($columns as $k => $v) {
			$variableName = Str::camel($k);
			$generated[]  = sprintf('public function scope%s($query,$%s){', Str::studly($k), $variableName);
			$generated[]  = sprintf('if (empty($%s)) { return $query; }', $variableName);
			$generated[]  = sprintf('return $query->where(\'%s\',\'=\',$%s);', $k, $variableName);
			$generated[]  = '}';
		}

		$namedTemplate = $this->applyNamedClassTemplate(self::MDL_CLASS, $className);
		$scopes        = str_replace('//', implode("\r\n", $generated), $namedTemplate);
		$content       = str_replace($namedTemplate, $scopes, $content);

		file_put_contents($fileModel, $content);
	}

	public function applyController($table, $columns, $className) {
		$modelName = Str::camel(Str::singular($table));
		//TODO: windows OS
		$fileController = app_path() . '/Http/Controllers/' . $className . 'Controller.php';

		//editting
		$content = file_get_contents($fileController);
		if (!$content) {
			throw new Exception("Error: controller " . $fileController, 1);
		}

		$index   = $this->applyIndexController($table, $className, $columns);
		$content = str_replace(self::CTRL_FUNC_INDEX_RAW, $index, $content);

		$create  = $this->applyCreateController($table, $className, $columns);
		$content = str_replace(self::CTRL_FUNC_CREATE, $create, $content);

		$store   = $this->applyStoreController($table, $className, $columns);
		$content = str_replace(self::CTRL_FUNC_STORE, $store, $content);

		$namedTemplate = $this->applyNamedControllerFunctionTemplate(self::CTRL_FUNC_SHOW, $className, $modelName);
		$show          = $this->applyShowController($namedTemplate, $table, $className, $modelName, $columns);
		$content       = str_replace($namedTemplate, $show, $content);

		$namedTemplate = $this->applyNamedControllerFunctionTemplate(self::CTRL_FUNC_DESTROY, $className, $modelName);
		$destroy       = $this->applyDestroyController($namedTemplate, $table, $className, $modelName, $columns);
		$content       = str_replace($namedTemplate, $destroy, $content);

		$namedTemplate = $this->applyNamedControllerFunctionTemplate(self::CTRL_FUNC_EDIT, $className, $modelName);
		$edit          = $this->applyEditController($namedTemplate, $table, $className, $modelName, $columns);
		$content       = str_replace($namedTemplate, $edit, $content);

		$namedTemplate = $this->applyNamedControllerFunctionTemplate(self::CTRL_FUNC_UPDATE, $className, $modelName);
		$update        = $this->applyUpdateController($namedTemplate, $table, $className, $modelName, $columns);
		$content       = str_replace($namedTemplate, $update, $content);

		file_put_contents($fileController, $content);
	}

	public function applyUpdateController($template, $table, $className, $modelName, $columns) {
		$generated = [];
		foreach ($columns as $k => $v) {
			if (Str::startsWith($k, 'created') || Str::startsWith($k, 'updated')) {
				continue;
			}
			$generated[] = sprintf('$%s->%s = $request->input(\'%s\');', $modelName, $k, $k);
		}
		$generated[] = sprintf('$%s->save();', $modelName);
		$generated[] = sprintf('return redirect(\'%s\')->with(\'status\', \'updated\');', $table);
		return str_replace('//', implode("\r\n", $generated), $template);
	}
	public function applyEditController($template, $table, $className, $modelName, $columns) {
		$generated   = [];
		$generated[] = sprintf('return view(\'%s.edit\',[\'model\'=>$%s]);', $table, $modelName);
		return str_replace('//', implode("\r\n", $generated), $template);
	}

	public function applyDestroyController($template, $table, $className, $modelName, $columns) {
		$generated   = [];
		$generated[] = sprintf('$%s->delete();', $modelName);
		$generated[] = sprintf('return redirect(\'%s\')->with(\'status\', \'deleted\');', $table);
		return str_replace('//', implode("\r\n", $generated), $template);
	}

	public function applyShowController($template, $table, $className, $modelName, $columns) {
		$generated   = [];
		$generated[] = sprintf('return view(\'%s.show\',[\'model\'=>$%s]);', $table, $modelName);

		return str_replace('//', implode("\r\n", $generated), $template);
	}

	public function applyStoreController($table, $className, $columns) {
		$generated   = [];
		$generated[] = sprintf('$model = new %s;', $className);
		foreach ($columns as $k => $v) {
			if (Str::startsWith($k, 'created') || Str::startsWith($k, 'updated')) {
				continue;
			}
			$generated[] = sprintf('$model->%s = $request->input(\'%s\');', $k, $k);
		}
		$generated[] = '$model->save();';
		$generated[] = sprintf('return redirect(\'%s\')->with(\'status\', \'recorded\');', $table);

		return str_replace('//', implode("\r\n", $generated), self::CTRL_FUNC_STORE);
	}

	public function applyCreateController($table, $className, $columns) {
		$generated   = [];
		$generated[] = sprintf('$model = new %s;', $className);
		$generated[] = sprintf('return view(\'%s.create\',[\'model\'=>$model]);', $table);
		return str_replace('//', implode("\r\n", $generated), self::CTRL_FUNC_CREATE);
	}

	public function applyIndexController($table, $className, $columns) {
		$generated   = [];
		$generated[] = sprintf('$pageSize = $request->query(\'size\');');
		foreach ($columns as $k => $v) {
			if (Str::startsWith($k, 'created') || Str::startsWith($k, 'updated')) {
				continue;
			}
			$generated[] = sprintf('$%s = $request->query(\'%s\');', Str::camel($k), $k);
		}

		$generated[] = sprintf('$models = %s::orderBy(\'id\',\'desc\')', $className);
		foreach ($columns as $k => $v) {
			if (Str::startsWith($k, 'created') || Str::startsWith($k, 'updated')) {
				continue;
			}
			$generated[] = sprintf('->%s($%s)', Str::camel($k), Str::camel($k));
		}
		$generated[] = '->paginate($pageSize);';
		$generated[] = sprintf('return view(\'%s.index\',[\'models\'=>$models]);', $table);

		return str_replace('//', implode("\r\n", $generated), self::CTRL_FUNC_INDEX);
	}

	public function applyNamedClassTemplate($template, $className) {
		return sprintf($template, $className);
	}

	public function applyNamedControllerFunctionTemplate($template, $className, $modelName) {
		return sprintf($template, $className, $modelName);
	}

	public function parseTableStruct($columns) {
		$result = [];
		foreach ($columns as $column) {
			if ($column->Field == "id" || $column->Field == "created_at" || $column->Field == "updated_at") {
				continue;
			}
			$result[$column->Field] = $column->Type;
		}

		return $result;
	}

	const MDL_CLASS =
		'class %s extends Model
{
    //
}';

	const CTRL_FUNC_UPDATE =
		'public function update(Request $request, %s $%s)
    {
        //
    }';

	const CTRL_FUNC_EDIT =
		'public function edit(%s $%s)
    {
        //
    }';

	const CTRL_FUNC_DESTROY =
		'public function destroy(%s $%s)
    {
        //
    }';

	const CTRL_FUNC_SHOW =
		'public function show(%s $%s)
    {
        //
    }';

	const CTRL_FUNC_STORE =
		'public function store(Request $request)
    {
        //
    }';

	const CTRL_FUNC_CREATE =
		'public function create()
    {
        //
    }';

	const CTRL_FUNC_INDEX_RAW =
		'/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }';
	const CTRL_FUNC_INDEX =
		'/**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	public function index(Request $request)
    {
    	//
    }';
}
