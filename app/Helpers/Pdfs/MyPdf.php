<?php
/*
 * Copyright (c) 2022. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace App\Helpers\Pdfs;

use TCPDF;

class MyPdf extends TCPDF
{
    public function Header()
    {
        // Logo
        $this->Image(public_path('business/header.png'), '', '', '', '', 'PNG', '', 'T', true, 300, 'C', false, false, 0, false, false, true);
    }

    // Page footer
    public function Footer()
    {
        // $this->Image(file:public_path('business/footer.png'), type: 'PNG',  resize: true,  fitonpage: true);
    }
}
