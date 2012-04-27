<?php

/**
 * Source_View 
 * 
 * @author Fumito MIZUNO <mizuno@php-web.net> 
 * @license GNU GPL v.2 or later {@link http://www.gnu.org/licenses/gpl-2.0.html}
 */
class Source_View {
	protected $reflect;
	protected $filename;
	protected $startline;
	protected $endline;
	protected $data;
	/**
	 * __construct 
	 * 
	 * @param Reflector $reflect 
	 * @access public
	 * @return void
	 */
	function __construct(Reflector $reflect) {
		$this->reflect = $reflect;
		$this->filename = $this->reflect->getFileName();
		$this->startline = $this->reflect->getStartLine();
		$this->endline = $this->reflect->getEndLine();
	}
	/**
	 * getFileName 
	 * 
	 * @access public
	 * @return string
	 */
	function getFileName() {
		return $this->filename;
	}
	/**
	 * getStartLine 
	 * 
	 * @access public
	 * @return int
	 */
	function getStartLine() {
		return $this->startline;
	}
	/**
	 * getEndLine 
	 * 
	 * @access public
	 * @return int
	 */
	function getEndLine() {
		return $this->endline;
	}
	/**
	 * createFileData 
	 * 
	 * @access public
	 * @return object
	 */
	function createFileData() {
		if(!file_exists($this->filename)) {
			throw new Exception(__('File not found: ' .$this->filename,'source-view'));
		}
		if(false === $this->data = file($this->filename)) {
			throw new Exception(__('Failed to open file: ' .$this->filename,'source-view'));
		}
		return $this;
	}
	/**
	 * outdata 
	 * 
	 * @param bool $escape 
	 * @access public
	 * @return string
	 */
	function outdata($escape=TRUE) {
		$out ='';
		for($i=$this->startline-1;$i<$this->endline;$i++) {
			if ($escape) {
				// esc_html is WordPress only
				// if not WordPress, use htmlspecialchars
				$out .= esc_html($this->data[$i]);
			} else {
				$out .= $this->data[$i];
			}
		}
		return $out;
	}
}
