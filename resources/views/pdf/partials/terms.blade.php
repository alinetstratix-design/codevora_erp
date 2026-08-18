<div class="terms-heading">Terms and Conditions:-</div>
<ol class="terms-ol">
    @foreach($terms->terms as $term)
        <li>{!! nl2br(e($term)) !!}</li>
    @endforeach
</ol>

<div class="terms-heading">Pre-Requisites for Installation of Windows:-</div>
<ol class="pre-ol">
    @foreach($terms->installationPrerequisites as $prereq)
        <li>{!! nl2br(e($prereq)) !!}</li>
    @endforeach
</ol>



<div class="acceptance-block">
    {{ $terms->acceptanceText }}
</div>

<table class="signature-table">
    <tr>
        <td width="50%" align="left">{{ $signature->leftLabel }}</td>
        <td width="50%" align="right">{{ $signature->rightLabel }}</td>
    </tr>
</table>
