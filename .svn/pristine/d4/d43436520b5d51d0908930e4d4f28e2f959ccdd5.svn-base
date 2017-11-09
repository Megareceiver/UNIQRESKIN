<?php
require_once('fpdi.php');
class PDF2 extends FPDI {
	/**
	 * "Remembers" the template id of the imported page
	 */
	var $_tplIdx;

	/**
	 * include a background template for every page
	 */
	function Header() {
		if (is_null($this->_tplIdx)) {
			$this->setSourceFile('gst3.pdf');
			$this->_tplIdx = $this->importPage(1);
		}
		$this->useTemplate($this->_tplIdx);

		$this->SetFont('freesans', 'B', 9);
		$this->SetTextColor(255);
		$this->SetXY(60.5, 24.8);
		$this->Cell(0, 8.6, "TCPDF and FPDI");
	}

	function Footer() {}
}