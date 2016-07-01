<?php



 function setActive($path, $active = 'active') {
    return  \Illuminate\Support\Str::contains(\Illuminate\Http\Request::path(), $path) ? $active : '';
}