<?php

namespace Toshim45\ReverseMysql;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Toshim45\ReverseMysql\Console\Commands\MysqlReverse;

class ServiceProvider extends BaseServiceProvider {

	public function register() {}

	public function boot() {
		$this->commands([MysqlReverse::class]);

		$stubPath = $this->packagePath('resources/stubs');
		$this->publishes([
			$stubPath => resource_path('stubs'),
		], 'stubs');
	}
}

?>