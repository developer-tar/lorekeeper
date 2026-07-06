<?php

/*
|--------------------------------------------------------------------------
| Homestead Routes
|--------------------------------------------------------------------------
|
| Routes for logged-in users with a linked account.
|
*/

Route::group(['prefix' => 'homestead', 'namespace' => 'Homestead'], function() {
    Route::get('rooms', 'RoomController@getIndex')->name('homestead.rooms');
    Route::get('rooms/create', 'RoomController@getCreate');
    Route::post('rooms/create', 'RoomController@postCreate');
    Route::get('rooms/{id}/editor', 'RoomEditorController@getEditor')->where('id', '[0-9]+');
    Route::post('rooms/{id}/editor', 'RoomEditorController@postSave')->where('id', '[0-9]+');
    Route::get('rooms/edit/{id}', 'RoomController@getEdit')->where('id', '[0-9]+');
    Route::post('rooms/edit/{id}', 'RoomController@postEdit')->where('id', '[0-9]+');
    Route::get('rooms/delete/{id}', 'RoomController@getDelete')->where('id', '[0-9]+');
    Route::post('rooms/delete/{id}', 'RoomController@postDelete')->where('id', '[0-9]+');

    Route::get('houses', 'HouseController@getIndex')->name('homestead.houses');
    Route::get('houses/create', 'HouseController@getCreate');
    Route::post('houses/create', 'HouseController@postCreate');
    Route::get('houses/edit/{id}', 'HouseController@getEdit')->where('id', '[0-9]+');
    Route::post('houses/edit/{id}', 'HouseController@postEdit')->where('id', '[0-9]+');
    Route::get('houses/delete/{id}', 'HouseController@getDelete')->where('id', '[0-9]+');
    Route::post('houses/delete/{id}', 'HouseController@postDelete')->where('id', '[0-9]+');
});
