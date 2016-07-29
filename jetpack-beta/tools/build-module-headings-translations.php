<?php

$file_contents = "<?php
// Do not edit this file. It's generated by jetpack/tools/build-module-headings-translations.php

/**
 * For a given module, return an array with translated name, description and recommended description.
 *
 * @param string \$key Module file name without .php
 *
 * @return array
 */
function jetpack_get_module_i18n( \$key ) {
\tstatic \$modules;
\tif ( ! isset( \$modules ) ) {
\t\t\$modules = array(";

$jp_dir = dirname( dirname( __FILE__ ) ) . '/';
$files  = glob( "{$jp_dir}modules/*.php" );
$tags   = array(
	'Other' => array(),
);
foreach ( $files as $file ) {
	$absolute_path  = $file;
	$relative_path  = str_replace( $jp_dir, '', $file );
	$_file_contents = '';

	$file      = fopen( $absolute_path, 'r' );
	$file_data = fread( $file, 8192 );
	fclose( $file );

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );

	$all_headers = array(
		'name'                      => 'Module Name',
		'description'               => 'Module Description',
		'recommended description'   => 'Jumpstart Description',
		'tags'                      => 'Module Tags',
	);

	foreach ( $all_headers as $field => $regex ) {
		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] ) {
			$string = trim( preg_replace( "/\s*(?:\*\/|\?>).*/", '', $match[1] ) );
			if ( 'Module Tags' === $regex ) {
				$module_tags = array_map( 'trim', explode( ',', $string ) );
				foreach ( $module_tags as $tag ) {
					$tags[ $tag ][] = $relative_path;
				}
			} else {
				$_file_contents .= "\t\t\t\t'{$field}' => _x( '{$string}', '{$regex}', 'jetpack' ),\r\n";
			}
		}
	}

	if ( $_file_contents ) {
		$file_contents .= "\r\n\t\t\t'" . str_replace( '.php', '', basename( $absolute_path ) ) . "' => array(\r\n$_file_contents\t\t\t),\r\n";
	}

}
$file_contents .= "\t\t);
\t}";
$file_contents .= "\r\n\treturn \$modules[ \$key ];
}";

$file_contents .= "
/**
 * For a given module tag, return its translated version.
 *
 * @param string \$key Module tag as is in each module heading.
 *
 * @return string
 */";
$file_contents .= "\r\nfunction jetpack_get_module_i18n_tag( \$key ) {
\tstatic \$module_tags;
\tif ( ! isset( \$module_tags ) ) {";
$file_contents .= "\r\n\t\t\$module_tags = array(";
foreach ( $tags as $tag => $files ) {
	$file_contents .= "\r\n\t\t\t// Modules with `{$tag}` tag:\r\n";
	foreach ( $files as $file ) {
		$file_contents .= "\t\t\t//  - {$file}\r\n";
	}
	$file_contents .= "\t\t\t'{$tag}' =>_x( '{$tag}', 'Module Tag', 'jetpack' ),\r\n";
}
$file_contents .= "\t\t);
\t}";
$file_contents .= "\r\n\treturn \$module_tags[ \$key ];
}";

file_put_contents( "{$jp_dir}modules/module-headings.php", $file_contents );
