<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "bold");
        $size = 8.5;
        $color = array(0, 0, 0);
        $y = $pdf->get_height() - 25;
        
        $pdf->page_text(25, $y, "{PAGE_NUM} of {PAGE_COUNT}", $font, $size, $color);
        
        $contactText = "Contact No: 6399969642";
        $contactWidth = $fontMetrics->get_text_width($contactText, $font, $size);
        $xCenter = ($pdf->get_width() - $contactWidth) / 2;
        $pdf->page_text($xCenter, $y, $contactText, $font, $size, $color);
        
        $poweredText = "{{ $footer->poweredBy ?? 'powered by Codevora Tech' }}";
        $poweredWidth = $fontMetrics->get_text_width($poweredText, $font, $size);
        $xRight = $pdf->get_width() - $poweredWidth - 25;
        $pdf->page_text($xRight, $y, $poweredText, $font, $size, $color);
    }
</script>
