<div class="item-card">
    <!-- Top Card Metadata Table -->
    <table class="card-meta-table">
        <tr>
            <td width="15%">Code :</td>
            <td width="35%">{{ $item->itemCode ?? '' }}</td>
            <td width="15%">Size :</td>
            <td width="35%">{{ $item->formattedSize ?? '' }}</td>
        </tr>
        <tr>
            <td>Name :</td>
            <td>{{ $item->position ?? '' }}</td>
            <td>Profile System :</td>
            <td>{{ $item->profileSystem ?? '' }}</td>
        </tr>
        <tr>
            <td>Location :</td>
            <td>{{ $item->location ?? '' }}</td>
            <td>Glass :</td>
            <td>{{ $item->glass->type ?? '' }}</td>
        </tr>
    </table>

    <!-- Card Body (Left CAD Drawing, Right Computed Values & Specs) -->
    <table class="card-body-table">
        <tr>
            <!-- Left CAD Drawing Box -->
            <td class="drawing-box">
                {!! $item->drawing->svgHtml ?? '' !!}
                <div class="view-caption">{{ $item->drawing->viewCaption ?? 'View From Inside' }}</div>
            </td>

            <!-- Right Computed Values & Profile/Accessories Specs -->
            <td width="65%">
                <div class="computed-header">Computed Values</div>
                <table class="computed-table">
                    <tr>
                        <td width="70%">Sq.Ft. per window</td>
                        <td width="30%" class="val-col">{{ $item->formattedSqftArea ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Value per Sq.Ft.</td>
                        <td class="val-col">{{ $item->formattedValuePerSqft ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Unit Price</td>
                        <td class="val-col">{{ $item->formattedUnitPrice ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Quantity</td>
                        <td class="val-col">{{ $item->formattedQuantity ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Value</td>
                        <td class="val-col">{{ $item->formattedTotalValue ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Weight</td>
                        <td class="val-col">{{ number_format($item->weightKg ?? 0, 3) }} KG</td>
                    </tr>
                </table>

                <!-- Profile & Accessories Split Table -->
                <table class="specs-split-table">
                    <tr>
                        <th width="50%">Profile</th>
                        <th width="50%">Accessories</th>
                    </tr>
                    <tr>
                        <td>
                            @if(isset($item->profile->details) && is_array($item->profile->details))
                                @foreach($item->profile->details as $key => $val)
                                    @if(trim($val) !== '')
                                        {{ preg_replace('/ \d+$/', '', $key) }} : {{ $val }}<br>
                                    @endif
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if(isset($item->accessory->details) && is_array($item->accessory->details))
                                @foreach($item->accessory->details as $key => $val)
                                    @if(trim($val) !== '')
                                        {{ preg_replace('/ \d+$/', '', $key) }} : {{ $val }}<br>
                                    @endif
                                @endforeach
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border-top: 1px solid #a6b9d0; padding: 3px 5px; font-size: 8pt;">Remarks : {{ $item->notes ?? '' }}</td>
        </tr>
    </table>
</div>
