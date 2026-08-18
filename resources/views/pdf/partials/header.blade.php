<header>
    <table class="company-header-table">
        <tr>
            <td width="38%" style="vertical-align: middle;">
                @if(!empty($company->logo))
                    <img src="{{ public_path(str_replace('storage/', 'storage/', $company->logo)) }}" style="height: 85px; max-width: 280px;" alt="Logo">
                @else
                    <table style="border-collapse: collapse; margin-bottom: 2px;">
                        <tr>
                            <td style="padding:0; vertical-align: middle;">
                                <span class="logo-scl">SCL</span>
                            </td>
                            <td style="padding: 0 4px; vertical-align: middle;">
                                <span class="logo-divider">|</span>
                            </td>
                            <td style="padding:0; vertical-align: middle;">
                                <span class="logo-cora">corā<sup>SCL</sup></span>
                            </td>
                        </tr>
                    </table>
                    <div class="logo-company-name">SHANI CORPORATION LIMITED</div>
                @endif
            </td>
            <td width="62%" class="company-address">
                <div class="company-title">{{ $company->companyName ?? 'SHANI CORPORATION LIMITED' }}</div>
                <div>Contact No. : {{ $company->phone ?: '6399969642' }} | Email : {{ $company->email }}</div>
                @if(!empty($company->gstin))
                    <div>GSTIN : {{ $company->gstin }}</div>
                @endif
            </td>
        </tr>
    </table>
    <div class="header-divider-line"></div>
    <div class="quote-meta-bar">
        {{ $header->formattedMetaBar }}
    </div>
</header>
