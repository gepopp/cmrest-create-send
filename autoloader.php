<?php
/**
 * Automatically locates and loads files based on their namespaces and their
 * file names whenever they are instantiated.
 *
 * @package TODO
 */
spl_autoload_register(function( $filename ) {

    $file_path = explode( '\\', $filename );


    /**
     * - The first index will always be the namespace since it's part of the plugin.
     * - All but the last index will be the path to the file.
     */
    // Get the last index of the array. This is the class we're loading.
    $file_name = '';
    if ( isset( $file_path[ count( $file_path ) - 1 ] ) ) {
        $file_name = $file_path[ count( $file_path ) - 1 ];
    }
    /**
     * Find the fully qualified path to the class file by iterating through the $file_path array.
     * We ignore the first index since it's always the top-level package. The last index is always
     * the file so we append that at the end.
     */
    $fully_qualified_path = trailingslashit( dirname(__FILE__ ) );


    for ( $i = 1; $i < count( $file_path ) - 1; $i++ ) {
        $dir = strtolower( $file_path[ $i ] );
        $fully_qualified_path .= trailingslashit( $dir );
    }
    $fully_qualified_path .= $file_name . '.php';


    // Now include the file.
    if ( stream_resolve_include_path($fully_qualified_path) ) {
        include_once $fully_qualified_path;
    }
});