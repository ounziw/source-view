<?php 
// dummyclass & dummyfunc are used for testcase.
class dummyclass {
	protected $var;
}
function dummyfunc() {
	return false;
}
// esc_html is a WordPress function
if (!function_exists(esc_html)){
	function esc_html($data) {
		return htmlspecialchars($data,ENT_QUOTES,'UTF-8');
	}
}
require_once('sourceviewclass.php');
class SourceViewtest extends PHPUnit_Framework_TestCase {

function testclass(){	
		$reflect = new Source_view(new ReflectionClass('dummyclass'));
        $this->assertEquals(3, $reflect->getStartLine());
        $this->assertEquals(5, $reflect->getEndLine());
		$data = 'class dummyclass {
	protected $var;
}
';
        $this->assertEquals($data, $reflect->createFileData()->outdata());
}
function testfunction(){	
		$reflect = new Source_view(new ReflectionFunction('dummyfunc'));
        $this->assertEquals(6, $reflect->getStartLine());
        $this->assertEquals(8, $reflect->getEndLine());
		$data = 'function dummyfunc() {
	return false;
}
';
        $this->assertEquals($data, $reflect->createFileData()->outdata());
}
}
?>
