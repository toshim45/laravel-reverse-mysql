# laravel-reverse-mysql
laravel package, basically reverse field from mysql into controller + views, just like crud generator, but with your theme, just provide the stubs. 

## Why ?
- need custom theme, we just provide simple html stub, edit as you need
- need a lot of additional code after generate
- need generator which simply read your database fields
- no code bloat, no helper files, it just your model, controller and crud blades

## Do not use this
- if you need generator with migration included, because this reverse code from mysql
- if you just need crud without additional code
- if you do not have concern on provided themes

## Requirements
- Laravel 5.* 
- [Laravel Collective](https://laravelcollective.com)
- Your table must contains id & timestamp

## Installation

```
composer require toshim45/laravel-reverse-mysql
```

then publish the `stubs`

```
php artisan vendor:publish --provider="Toshim45\LaravelReverseMysql\ServiceProvider" --tag=stubs
```

your `stubs` will be placed in `resources` folder, edit as you need, simple `stubs` provided for you, for more see [example](#stub-example) stubs below.

Do all generation **before you add some codes** to your new model & controller

## How To
### Basic Usage
- generate model with `php artisan make:model {ModelName} -mcr` options, make sure your model name is **singular** [PascalCase](http://wiki.c2.com/?PascalCase)
- run `php artisan reverse:mysql {table-name} -c -r` , table name is usually plural [snake_case](https://en.wikipedia.org/wiki/Snake_case)
- run `phpfmt` or other php formatter
- add this resources to routes

### Custom Stub
- generate model with `php artisan make:model {ModelName} -mcr` options, make sure your model name is **singular** [PascalCase](http://wiki.c2.com/?PascalCase)
- update content `resources/stubs`, keep both `{{tableName}}` and `{{tableContent}}` variables, which is used by this generator, var `{{tableFilter}}` will create filter with equal query, you can change query at model class.
- run `php artisan reverse:mysql {table_name} -c -r`, table name is usually plural [snake_case](https://en.wikipedia.org/wiki/Snake_case)
- run `phpfmt` or other php formatter
- add this resources to routes

### Stub Example

pls check [github](https://github.com/toshim45/laravel-reverse-mysql/tree/master/examples) and copy paste folder content from one of these:
- [jeroennoten-adminlte](https://github.com/jeroennoten/Laravel-AdminLTE/tree/laravel5-adminlte2) stub, install jeroennoten-adminlte package before generating crud


## Notes
- Tested on OSX, need help for other OS
- Tested on laravel 5.6, need help for >5.6 & 6.* (i dont think will support for 4.*)
- I'm working on formatting generated code, but for now you can use your IDE
- only support mysql
