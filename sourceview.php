<?php
/*
Plugin Name: Source View 
Plugin URI: http://ounziw.com/2012/04/27/source-view-plugin/
Description: This plugin outputs a source code of the function/class you specified.
Author: Fumito MIZUNO 
Version: 1.0
Author URI: http://php-web.net/
 */
define( 'SV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if( is_admin() ) {
	require_once(SV_PLUGIN_DIR . '/sourceviewclass.php');
}

function sv_plugin_admin_page() {
	// Use of $hook is explained below
	// http://justintadlock.com/archives/2011/07/12/how-to-load-javascript-in-the-wordpress-admin
	// Thanks, Justin.
	$hook = add_options_page( 'Source View Options', __('Source View','source-view'), 'manage_options', 'source-view', 'sv_plugin_options' );
	add_action('admin_print_scripts-'.$hook, 'sv_load_script',10);
	add_action('admin_footer-'.$hook, 'sv_load_script_footer',11);
	add_action('admin_print_styles-'.$hook, 'sv_load_style',10);
}
add_action( 'admin_menu', 'sv_plugin_admin_page' );

function sv_load_script() {
	wp_register_script('syntaxhighlightershcore', plugins_url('/syntaxhighlighter/js/shCore.js', __FILE__));
	wp_register_script('syntaxhighlighterphp', plugins_url('/syntaxhighlighter/js/shBrushPhp.js', __FILE__),array('syntaxhighlightershcore'));
	wp_enqueue_script('syntaxhighlighterphp');
}
function sv_load_script_footer() {
?>
	<script type="text/javascript">
	SyntaxHighlighter.all()
		</script>
<?php
}
function sv_load_style() {
	wp_register_style('syntaxhighlighterstyle', plugins_url('/syntaxhighlighter/css/shCore.css', __FILE__));
	wp_register_style('syntaxhighlighterdefault', plugins_url('/syntaxhighlighter/css/shThemeDefault.css', __FILE__),array('syntaxhighlighterstyle'));
	wp_enqueue_style('syntaxhighlighterdefault');
}

function sv_settings_api_init() {
	add_settings_section('sv_setting_section',
		__('Class/Function Source View','source-view'),
		'sv_setting_section_callback_function',
		'source-view');

	add_settings_field('sv_function_name',
		__('Class/Function Name','source-view'),
		'sv_setting_callback_function',
		'source-view',
		'sv_setting_section');

	register_setting('source-view-group','sv_function_name', 'wp_filter_nohtml_kses');
}
add_action('admin_init', 'sv_settings_api_init');

function sv_setting_section_callback_function() {
	echo '<p>'. __('Enter a classname or function name.','source-view') . '</p>';
}

function sv_setting_callback_function() {
	echo '<input name="sv_function_name" id="sv_function_name" type="text" value="'. esc_attr(get_option('sv_function_name')) .'" class="code" />';
}

function sv_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
?>
	<div class="wrap">
		<form action="options.php" method="post">
<?php settings_fields('source-view-group'); ?>
<?php do_settings_sections('source-view'); ?>
<input name="Submit" type="submit" id="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>

<?php
	if ('' != get_option('sv_function_name')) {
		$func_or_class_name = get_option('sv_function_name');
		try {
			$reflect = sv_funcname_check($func_or_class_name);
			$obj = new Source_View($reflect);
			// HTML before source code
			$before_code_format = '<p>File: %s  Line: %d - %d</p>';
			$before_code_format = apply_filters('sv_before_code_format',$before_code_format);
			printf($before_code_format, $obj->getFileName(), $obj->getStartLine(), $obj->getEndLine());
			// source code
			print '<pre class=\'brush: php; first-line: '. $obj->getStartLine() .';\'>';
			print $obj->createFileData()->outData();
			print '</pre>';
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
?></div>
<?php
}

/**
 * sv_funcname_check 
 * 
 * @param string $func_or_class_name 
 * @access public
 * @return object
 */
function sv_funcname_check($func_or_class_name){
	global $shortcode_tags;
	$reflect = NULL;
	if (function_exists($func_or_class_name)) {
		$reflect = new ReflectionFunction($func_or_class_name);
	} elseif (class_exists($func_or_class_name)) {
		$reflect = new ReflectionClass($func_or_class_name);
	} elseif (array_key_exists($func_or_class_name,$shortcode_tags)) {
		$func_name = $shortcode_tags[$func_or_class_name];
		$reflect = new ReflectionFunction($func_name);
	}

	if (is_object($reflect)) {
		return $reflect;
	} else {
		throw new Exception(__('Not Found function/class','source-view'));
	}
}
