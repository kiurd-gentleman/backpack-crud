<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('brand', 'BrandCrudController');
    Route::crud('category', 'CategoryCrudController');

    Route::crud('sub-category', 'SubCategoryCrudController');
    Route::crud('product', 'ProductCrudController');

    Route::get('sub_category/{id}', 'CategoryCrudController@category_by_sub_category')->name('filter_sub_category');


    Route::get('charts/weekly-users', 'Charts\WeeklyUsersChartController@response')->name('charts.weekly-users.index');
}); // this should be the absolute last line of this file
