<?php

namespace Toshim45\LaravelReverseMysql;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Toshim45\LaravelReverseMysql\Console\Commands\MysqlReverse;

class ServiceProvider extends BaseServiceProvider {

	public function register() {}

	public function boot() {
		$this->commands([MysqlReverse::class]);

		$stubPath = __DIR__ . '/../resources/stubs';
		$this->publishes([
			$stubPath => resource_path('stubs'),
		], 'stubs');
		$stubBootstrap5Path = __DIR__. '/../resources/stubs-bootstrap5';
		$this->publishes([
			$stubBootstrap5Path => resource_path('stubs'),
			$stubBootstrap5Path.'/layout.blade.php' => resource_path('views/layout.blade.php'),
		], 'stubs-bootstrap5');
	}
}

?>