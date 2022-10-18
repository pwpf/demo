<?php

namespace Plugin_Name\App\Shortcode;


use Plugin_NameVendor\Dframe\Loader\Loader;

class MyFirstShortcode extends Loader {

    public function registerHookCallbacks(){
        //add_shortcode('MyFirstShortcode', [$this, 'build']);
    }

    public function build(){
        // return 'Hello World!';
    }
}