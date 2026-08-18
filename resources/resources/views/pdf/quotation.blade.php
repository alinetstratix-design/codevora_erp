<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation - {{ $quotation->quotation_number ?? $quotation->quote_no ?? 'QT-0001' }}</title>
    <style>
        @page {
            margin: 110px 25px 40px 25px;
        }

        header {
            position: fixed;
            top: -95px;
            left: 0px;
            right: 0px;
            height: 90px;
            font-family: Arial, Helvetica, sans-serif;
        }

        footer {
            position: fixed;
            bottom: -30px;
            left: 0px;
            right: 0px;
            height: 25px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #333;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            color: #000;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
        }

        /* Header Layout */
        .company-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .company-header-table td {
            vertical-align: top;
        }
        .logo-text {
            font-weight: bold;
            font-size: 20px;
            color: #1f618d;
        }
        .logo-subtext {
            font-size: 9px;
            font-weight: bold;
            color: #333;
        }
        .company-address {
            text-align: right;
            font-size: 9px;
            line-height: 1.3;
        }

        .quote-meta-bar {
            width: 100%;
            border-top: 1.5px solid #1f618d;
            padding-top: 4px;
            font-size: 9.5px;
            font-weight: bold;
            text-align: right;
        }

        /* Cover Letter Styling */
        .cover-letter {
            padding: 15px 5px;
            font-size: 10px;
            line-height: 1.6;
        }
        .cover-recipient {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 25px;
        }
        .cover-para {
            margin-bottom: 14px;
            text-align: justify;
        }
        .enclosure-list {
            margin: 10px 0 20px 25px;
            font-weight: normal;
        }

        /* Item Card Styling */
        .item-card {
            width: 100%;
            border: 1px solid #a6b9d0;
            border-collapse: collapse;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .card-meta-table {
            width: 100%;
            background-color: #d9e2ec;
            border-bottom: 1px solid #a6b9d0;
            font-size: 9px;
        }
        .card-meta-table td {
            padding: 4px 6px;
            font-weight: bold;
            border: 1px solid #c4d3e4;
        }

        .card-body-table {
            width: 100%;
            border-collapse: collapse;
        }
        .card-body-table td {
            vertical-align: top;
            padding: 0;
        }

        .drawing-box {
            width: 35%;
            border-right: 1px solid #a6b9d0;
            text-align: center;
            padding: 6px;
        }
        .view-caption {
            font-size: 8.5px;
            color: #333;
            margin-top: 4px;
        }

        .computed-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        .computed-header {
            background-color: #b4c6e7;
            font-weight: bold;
            padding: 4px 6px;
            border-bottom: 1px solid #a6b9d0;
        }
        .computed-table td {
            padding: 3px 6px;
            border-bottom: 1px solid #e0e6ed;
            border-right: 1px solid #e0e6ed;
        }
        .computed-table td.val-col {
            text-align: right;
            font-weight: bold;
        }

        .specs-split-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #a6b9d0;
            font-size: 8px;
        }
        .specs-split-table th {
            background-color: #b4c6e7;
            text-align: left;
            padding: 3px 6px;
            font-weight: bold;
            border-bottom: 1px solid #a6b9d0;
            border-right: 1px solid #a6b9d0;
        }
        .specs-split-table td {
            padding: 3px 6px;
            vertical-align: top;
            border-right: 1px solid #e0e6ed;
            line-height: 1.35;
        }

        /* Financial Summary Page 28 */
        .summary-title {
            font-size: 11px;
            font-weight: bold;
            background-color: #b4c6e7;
            padding: 5px 8px;
            border: 1px solid #a6b9d0;
            margin-top: 10px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            border: 1px solid #a6b9d0;
        }
        .summary-table td {
            padding: 5px 8px;
            border: 1px solid #a6b9d0;
        }
        .summary-table td.label-col {
            font-weight: bold;
        }
        .summary-table td.val-col {
            text-align: right;
            font-weight: bold;
        }

        /* Terms & Pre-Requisites Page 29 */
        .terms-heading {
            font-weight: bold;
            font-size: 10px;
            text-decoration: underline;
            margin-top: 10px;
            margin-bottom: 6px;
        }
        .terms-ol, .pre-ol {
            margin: 0 0 12px 18px;
            padding: 0;
            font-size: 8.5px;
            line-height: 1.45;
        }
        .terms-ol li, .pre-ol li {
            margin-bottom: 4px;
        }
        .acceptance-block {
            margin-top: 25px;
            font-size: 9px;
            line-height: 1.5;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
            font-weight: bold;
            font-size: 9.5px;
        }
    </style>
</head>
<body>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "{PAGE_NUM} of {PAGE_COUNT} powered by EvA WinOptimize Software";
            $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
            $size = 8.5;
            $color = array(0, 0, 0);
            $y = $pdf->get_height() - 22;
            $x = $pdf->get_width() - 250;
            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script>

    <!-- GLOBAL PAGE HEADER -->
    <header>
        <table class="company-header-table">
            <tr>
                <td width="40%">
                    @if(isset($company->logo) && !empty($company->logo))
                        <img src="{{ public_path(str_replace('storage/', 'storage/', $company->logo)) }}" style="max-height: 45px;" alt="Logo">
                    @else
                        <div class="logo-text">SCL | cora<sup>SCL</sup></div>
                        <div class="logo-subtext">SHANI CORPORATION LIMITED</div>
                    @endif
                </td>
                <td width="60%" class="company-address">
                    <strong>{{ $company->company_name ?? 'SHANI CORPORATION LIMITED' }}</strong><br>
                    {{ $company->address ?? 'D-42, E-42 & E-43 , Gopalpur Industrial Area , Sikandrabad , Bulandshar , Uttar Pradesh -203205' }}<br>
                    Contact No. : {{ $company->phone ?? '+91 9599543500' }}<br>
                    Email : {{ $company->email ?? 'query@sclgroup.co' }}<br>
                    Website : {{ $company->website ?? '' }} | GSTIN : {{ $company->gstin ?? '' }}
                </td>
            </tr>
        </table>
        <div class="quote-meta-bar">
            Quote No. : {{ $quotation->quotation_number ?? $quotation->quote_no ?? 'SCL-QT-00001831' }} / Project : {{ $quotation->project_name ?? $quotation->client_name ?? 'AMBALA AIRFORCE' }} / Date : {{ isset($quotation->quotation_date) ? \Carbon\Carbon::parse($quotation->quotation_date)->format('d-m-Y') : date('d-m-Y') }}
        </div>
    </header>

    <!-- PAGE 1: COVER LETTER -->
    <div class="cover-letter">
        <div class="cover-recipient">
            To<br>
            {{ $quotation->client_name ?? 'AMBALA AIRFORCE' }}
        </div>

        <p class="cover-para">Dear Customer,</p>
        <p class="cover-para">We are delighted that you are considering our range of Windows and Doors for your premises.</p>
        <p class="cover-para">It has gained rapid acceptance across all cities of India for the overwhelming advantages of better protection from noise, heat, rain, dust and pollution.</p>
        <p class="cover-para">In drawing this proposal, it has been our endeavor to suggest designs which would enhance your comfort and aesthetics from inside and improve the facade of the building.</p>
        <p class="cover-para">It has a well-established service network to deliver seamless service at your doorstep. Our offer comprises of the following in enclosure for your kind perusal:</p>

        <div class="enclosure-list">
            a. Window design, specification and value<br>
            b. Terms and Conditions
        </div>

        <p class="cover-para">We now look forward to be of service to you.</p>

        <div style="margin-top: 30px;">
            <strong>For SHANI CORPORATION LIMITED,</strong>
            <br><br><br><br>
            Authorized Signatory
        </div>
    </div>

    <div class="page-break"></div>

    <!-- PAGES 2 TO 27: ITEM CARDS (EXACTLY 2 CARDS PER PAGE) -->
    @if(isset($quotation->items) && count($quotation->items) > 0)
        @foreach($quotation->items as $index => $item)
            
            <div class="item-card">
                <!-- Top Card Metadata Table -->
                <table class="card-meta-table">
                    <tr>
                        <td width="40%">Code : {{ $item->item_code ?? ('W' . ($index + 1)) }}</td>
                        <td width="60%">Size : W = {{ number_format($item->width ?? $item->dimension_w ?? 0, 2, '.', '') }}; H = {{ number_format($item->height ?? $item->dimension_h ?? 0, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <td>Name : {{ $item->position ?? $item->item_code ?? ('W' . ($index + 1)) }}</td>
                        <td>Profile System : {{ $item->profile_system ?? 'CORA - 60MM CASEMENT SERIES' }}</td>
                    </tr>
                    <tr>
                        <td>Location : {{ $item->location ?? '' }}</td>
                        <td>Glass : {{ $item->glass_type ?? '(1) 5mm Clear Toughened' }}</td>
                    </tr>
                </table>

                <!-- Card Body (Left CAD Drawing, Right Computed Values & Specs) -->
                <table class="card-body-table">
                    <tr>
                        <!-- Left CAD Drawing Box -->
                        <td class="drawing-box">
                            {!! \App\Helpers\SvgGenerator::generateWindowDrawing(
                                $item->width ?? $item->dimension_w ?? 1000,
                                $item->height ?? $item->dimension_h ?? 1000,
                                $item->unit ?? 'mm',
                                $item->profile_system ?? 'Casement Series',
                                $item->drawing_metadata ?? []
                            ) !!}
                            <div class="view-caption">View From Inside</div>
                        </td>

                        <!-- Right Computed Values & Profile/Accessories Specs -->
                        <td width="65%">
                            <div class="computed-header">Computed Values</div>
                            <table class="computed-table">
                                <tr>
                                    <td>Sq.Ft. per window</td>
                                    <td class="val-col">{{ number_format($item->area ?? 0, 3) }} Sq.Ft.</td>
                                </tr>
                                <tr>
                                    <td>Value per Sq.Ft.</td>
                                    <td class="val-col">{{ number_format($item->value_per_sqft ?? 0, 2) }} INR</td>
                                </tr>
                                <tr>
                                    <td>Unit Price</td>
                                    <td class="val-col">{{ number_format($item->unit_price ?? 0, 2) }} INR</td>
                                </tr>
                                <tr>
                                    <td>Quantity</td>
                                    <td class="val-col">{{ $item->qty ?? $item->quantity ?? 1 }} Pcs</td>
                                </tr>
                                <tr>
                                    <td>Value</td>
                                    <td class="val-col">{{ number_format($item->amount ?? 0, 2) }} INR</td>
                                </tr>
                                <tr>
                                    <td>Weight</td>
                                    <td class="val-col">{{ number_format($item->weight_kg ?? 0, 3) }} KG</td>
                                </tr>
                            </table>

                            <!-- Specs Split Table -->
                            <table class="specs-split-table">
                                <tr>
                                    <th width="55%">Profile</th>
                                    <th width="45%">Accessories</th>
                                </tr>
                                <tr>
                                    <td>
                                        @if(isset($item->profile_details) && is_array($item->profile_details))
                                            @foreach($item->profile_details as $key => $val)
                                                <strong>{{ $key }} :</strong> {{ $val }}<br>
                                            @endforeach
                                        @else
                                            Profile Color : {{ $item->profile_color ?? 'WHITE' }}<br>
                                            MeshType : {{ $item->mesh_type ?? 'No' }}<br>
                                            Casement Sash : Cmt Outward Sash 60Mm X 104Mm<br>
                                            Outer : Cmt Outer 60Mm X 60Mm<br>
                                        @endif
                                        @if(!empty($item->notes))
                                            Remarks : {{ $item->notes }}
                                        @else
                                            Remarks : 
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($item->accessories_details) && is_array($item->accessories_details))
                                            @foreach($item->accessories_details as $key => $val)
                                                <strong>{{ $key }} :</strong> {{ $val }}<br>
                                            @endforeach
                                        @else
                                            Locking : Multi-point<br>
                                            Handle color : WHITE<br>
                                            Arm Restrictor : Restrictor Arm 10"<br>
                                            Cylinder : Cylinder<br>
                                            Handle Type : S1-Espag Handle<br>
                                            Hinge : S1-3D Hinges<br>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Exactly 2 items per A4 page layout rule -->
            @if(($index + 1) % 2 == 0 && ($index + 1) < count($quotation->items))
                <div class="page-break"></div>
            @endif

        @endforeach
    @endif

    <div class="page-break"></div>

    <!-- PAGE 28: QUOTE FINANCIAL SUMMARY TABLE -->
    <div class="summary-title">Quote Total</div>
    <table class="summary-table">
        <tr>
            <td width="70%" class="label-col">No. of Components</td>
            <td width="30%" class="val-col">{{ $quotation->no_of_components ?? count($quotation->items) }} Pcs</td>
        </tr>
        <tr>
            <td class="label-col">Total Area</td>
            <td class="val-col">{{ number_format($quotation->total_area_sqft ?? $quotation->items->sum('area'), 2) }} Sq.Ft.</td>
        </tr>
        <tr>
            <td class="label-col">Basic Value</td>
            <td class="val-col">{{ number_format($quotation->basic_value ?? $quotation->subtotal ?? 0, 2) }} INR</td>
        </tr>
        <tr>
            <td class="label-col">Total Project Cost</td>
            <td class="val-col">{{ number_format($quotation->total_project_cost ?? $quotation->subtotal ?? 0, 2) }} INR</td>
        </tr>
        <tr>
            <td class="label-col">Gst @ {{ (int)($quotation->tax_percent ?? 18) }}%</td>
            <td class="val-col">{{ number_format($quotation->tax_amount ?? $quotation->gst ?? 0, 2) }} INR</td>
        </tr>
        <tr>
            <td class="label-col">Grand Total</td>
            <td class="val-col">{{ number_format($quotation->grand_total ?? 0, 2) }} INR</td>
        </tr>
        <tr>
            <td class="label-col">Average Price per Sq.Ft. without GST</td>
            <td class="val-col">{{ number_format($quotation->avg_price_sqft_ex_gst ?? 0, 2) }} INR</td>
        </tr>
        <tr>
            <td class="label-col">Average Price per Sq.Ft.</td>
            <td class="val-col">{{ number_format($quotation->avg_price_sqft_inc_gst ?? 0, 2) }} INR</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- PAGE 29: TERMS & CONDITIONS AND PRE-REQUISITES FOR INSTALLATION -->
    <div class="terms-heading">Terms and Conditions:-</div>
    <ol class="terms-ol">
        <li>Payments terms: -
            <br>&nbsp;&nbsp;&nbsp;&nbsp;a. 100% Advance along with order, if it is less than INR 100000.
            <br>&nbsp;&nbsp;&nbsp;&nbsp;b. 50% advance along with order, 50% before delivery, if it is more than INR 100000.
        </li>
        <li>Validation of quote 30 days, total execution of project should be completed latest by 3-months.</li>
        <li>P.O & Payments should made in the name of <strong>SHANI CORPORATION LIMITED</strong>.</li>
        <li>The prices are based on the sizes provided by the customer. The prices are valid for variation in sizes up to +/- 30mm per window provided the design and style of product remains unchanged. The customer will be charged on pro-rate basis for difference between the actual sizes and given sizes, if any, beyond the above variation.</li>
        <li>After handovering the windows, cleaning not our scope.</li>
        <li>Windows security tape should be remove while installing windows freely, After installation security tape will be removed by us that should be chargeable per window INR 100.</li>
        <li>If any other commitments given by our sales team, before placing order please call us . Cell : +91 9599543500</li>
        <li>After handovering windows, If any service require related to windows & doors , that should be chargeable. Per visit - INR 350.</li>
        <li>Material unloading & storage should be your scope.</li>
        <li>All disputes shall be subject jurisdiction only.</li>
    </ol>

    <div class="terms-heading">Pre-Requisites for installation of Windows:-</div>
    <ol class="pre-ol">
        <li>Walls should be plastered from inside and outside, with inside POP complete.</li>
        <li>All jams, sills and soffits should be plastered.</li>
        <li>Flooring (where doors have to be installed) should be complete.</li>
        <li>Aperture should be smooth.</li>
        <li>Base and top of window should be water leveled and sides should be in vertical plump.</li>
        <li>Sill width should be more than the window width.</li>
        <li>Opening should be accessible from inside for installation.</li>
        <li>Grills: Adequate care should be taken if grills have to be installed.
            <br>&nbsp;&nbsp;&nbsp;&nbsp;a. For Horizontal slider Window: Grill should be provided on the outer face of slider before the installation of the window.
            <br>&nbsp;&nbsp;&nbsp;&nbsp;b. For Casement windows: Screw type grill is recommended after installation of casement window.
        </li>
        <li>Installation should happen before the last coat of paint. At least one coat of paint should be done before installation begins.</li>
        <li>Scaffoldings/ bracing should not interrupt the window openings where openings where windows are supposed to be installed.</li>
    </ol>

    <div class="acceptance-block">
        I hereby accept the estimate as per above mentioned price and specifications. I have read and understood the terms & conditions and agree to them.
    </div>

    <table class="signature-table">
        <tr>
            <td width="50%" align="left">Authorized Signatory</td>
            <td width="50%" align="right">Signature of Customer</td>
        </tr>
    </table>

</body>
</html>
