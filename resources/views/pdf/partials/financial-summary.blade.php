<div class="summary-title">Quote Total</div>
<table class="summary-table">
    <tr>
        <td width="70%" class="label-col">No. of Components</td>
        <td width="30%" class="val-col">{{ $summary->formattedNoOfComponents }}</td>
    </tr>
    <tr>
        <td class="label-col">Total Area</td>
        <td class="val-col">{{ $summary->formattedTotalAreaSqft }}</td>
    </tr>
    <tr>
        <td class="label-col">Basic Value</td>
        <td class="val-col">{{ $summary->formattedBasicValue }}</td>
    </tr>
    <tr>
        <td class="label-col">Total Project Cost</td>
        <td class="val-col">{{ $summary->formattedTotalProjectCost }}</td>
    </tr>
    <tr>
        <td class="label-col">Gst @ {{ (int)$financials->gstPercent }}%</td>
        <td class="val-col">{{ $financials->formattedGstAmount }}</td>
    </tr>
    <tr>
        <td class="label-col">Grand Total</td>
        <td class="val-col">{{ $financials->formattedGrandTotal }}</td>
    </tr>
    <tr>
        <td class="label-col">Average Price per Sq.Ft. without GST</td>
        <td class="val-col">{{ $summary->formattedAvgPriceSqftExGst }}</td>
    </tr>
    <tr>
        <td class="label-col">Average Price per Sq.Ft.</td>
        <td class="val-col">{{ $summary->formattedAvgPriceSqftIncGst }}</td>
    </tr>
</table>
