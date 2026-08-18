<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Quotation')</title>
    <style>
        @page {
            margin: 95px 25px 35px 25px;
        }

        header {
            position: fixed;
            top: -85px;
            left: 0px;
            right: 0px;
            height: 75px;
            font-family: Arial, Helvetica, sans-serif;
        }

        footer {
            position: fixed;
            bottom: -22px;
            left: 0px;
            right: 0px;
            height: 18px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000000;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #000000;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
        }

        /* Header Styling */
        .company-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .company-header-table td {
            vertical-align: top;
        }
        .logo-scl {
            font-weight: bold;
            font-size: 20pt;
            color: #1b305b;
            letter-spacing: 0.5px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .logo-divider {
            font-size: 20pt;
            color: #666666;
            font-weight: normal;
        }
        .logo-cora {
            font-weight: bold;
            font-size: 20pt;
            color: #469344;
            letter-spacing: 0.5px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .logo-cora sup {
            font-size: 7.5pt;
            color: #1b305b;
            font-weight: bold;
        }
        .logo-company-name {
            font-size: 7.5pt;
            font-weight: bold;
            color: #333333;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .company-title {
            font-weight: bold;
            font-size: 9.5pt;
            color: #000000;
            margin-bottom: 1px;
        }
        .company-address {
            text-align: right;
            font-size: 8.2pt;
            line-height: 1.25;
            color: #000000;
        }
        .header-divider-line {
            width: 100%;
            border-bottom: 2px solid #a89476;
            margin-top: 4px;
            margin-bottom: 4px;
        }
        .quote-meta-bar {
            width: 100%;
            font-size: 8.5pt;
            font-weight: bold;
            text-align: right;
            color: #000000;
            padding-top: 1px;
        }

        /* Cover Letter */
        .cover-letter {
            padding: 10px 0px;
            font-size: 9.5pt;
            line-height: 1.55;
        }
        .cover-recipient {
            font-weight: bold;
            font-size: 10.5pt;
            margin-bottom: 20px;
        }
        .cover-para {
            margin-bottom: 12px;
            text-align: justify;
        }
        .enclosure-list {
            margin: 8px 0 16px 20px;
            font-weight: normal;
        }

        /* Item Cards */
        .item-card {
            width: 100%;
            border: 1px solid #a6b9d0;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .card-meta-table {
            width: 100%;
            background-color: #d9e2ec;
            border-bottom: 1px solid #a6b9d0;
            font-size: 8.5pt;
        }
        .card-meta-table td {
            padding: 3px 5px;
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
            padding: 4px;
        }
        .view-caption {
            font-size: 8pt;
            color: #333333;
            margin-top: 2px;
        }

        /* Computed Table */
        .computed-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .computed-header {
            background-color: #b4c6e7;
            font-weight: bold;
            padding: 3px 5px;
            border-bottom: 1px solid #a6b9d0;
        }
        .computed-table td {
            padding: 2.5px 5px;
            border-bottom: 1px solid #e0e6ed;
            border-right: 1px solid #e0e6ed;
        }
        .computed-table td.val-col {
            text-align: right;
            font-weight: bold;
        }

        /* Specs Split Table */
        .specs-split-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #a6b9d0;
            font-size: 7.5pt;
        }
        .specs-split-table th {
            background-color: #b4c6e7;
            text-align: left;
            padding: 2.5px 5px;
            font-weight: bold;
            border-bottom: 1px solid #a6b9d0;
            border-right: 1px solid #a6b9d0;
        }
        .specs-split-table td {
            padding: 2.5px 5px;
            vertical-align: top;
            border-right: 1px solid #e0e6ed;
            line-height: 1.3;
        }

        /* Summary Table Page 28 */
        .summary-title {
            font-size: 10.5pt;
            font-weight: bold;
            background-color: #b4c6e7;
            padding: 4px 6px;
            border: 1px solid #a6b9d0;
            margin-top: 5px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            border: 1px solid #a6b9d0;
        }
        .summary-table td {
            padding: 4.5px 6px;
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
            font-size: 9.5pt;
            text-decoration: underline;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        .terms-ol, .pre-ol {
            margin: 0 0 10px 16px;
            padding: 0;
            font-size: 8pt;
            line-height: 1.4;
        }
        .terms-ol li, .pre-ol li {
            margin-bottom: 3px;
        }
        .acceptance-block {
            margin-top: 20px;
            font-size: 8.5pt;
            line-height: 1.45;
        }
        .signature-table {
            width: 100%;
            margin-top: 35px;
            font-weight: bold;
            font-size: 9pt;
        }
    </style>
</head>
<body>

    @include('pdf.partials.footer', ['footer' => $report->footer])

    @include('pdf.partials.header', [
        'company' => $report->company,
        'header' => $report->header
    ])

    <main>
        @yield('content')
    </main>

</body>
</html>
