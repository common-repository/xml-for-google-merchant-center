<?php defined( 'ABSPATH' ) || exit;
/**
 * Sandbox function
 * 
 * @since 0.1.0
 * @version 3.0.9 (05-10-2023)
 *
 * @return void
 */
function xfgmc_run_sandbox() {
	$x = false; // установите true, чтобы использовать песочницу
	if ( true === $x ) {
		printf( '%s<br/>',
			esc_html__( 'The sandbox is working. The result will appear below', 'xml-for-google-merchant-center' )
		);
		$time_start = microtime( true );
		/* вставьте ваш код ниже */
		// Example:
		// $product = wc_get_product(8303);
		// echo $product->get_price();

		/* дальше не редактируем */
		$time_end = microtime( true );
		$time = $time_end - $time_start;
		printf( '<br/>%s<br/>%s %d %s',
			esc_html__( 'The sandbox is working correctly', 'xml-for-google-merchant-center' ),
			esc_html__( 'The execution time of the test script was', 'xml-for-google-merchant-center' ),
			esc_html( $time ),
			esc_html__( 'seconds', 'xml-for-google-merchant-center' )
		);
	} else {
		printf( '%s sanbox.php',
			esc_html__( 'The sandbox is not active. To activate, edit the file', 'xml-for-google-merchant-center' )
		);
	}
}