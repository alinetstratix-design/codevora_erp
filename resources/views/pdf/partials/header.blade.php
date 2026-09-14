<header>
    <table class="company-header-table">
        <tr>
            <td width="38%" style="vertical-align: middle;">
                @if(!empty($company->logo) && file_exists(public_path($company->logo)))
                    <img src="{{ public_path($company->logo) }}" style="height: 75px; max-width: 260px; object-fit: contain;" alt="Logo">
                @else
                    <div style="font-size: 16pt; font-weight: bold; color: #1b305b; letter-spacing: 0.5px;">
                        {{ $company->companyName ?? config('app.name', 'Codevora ERP') }}
                    </div>
                @endif
            </td>
            <td width="62%" class="company-address">
                <div class="company-title">{{ $company->companyName ?? config('app.name', 'Codevora ERP') }}</div>
                @if(!empty($company->address))
                    <div>{{ $company->address }}</div>
                @endif
                @if(!empty($company->formattedContactLine))
                    <div>{{ $company->formattedContactLine }}</div>
                @endif
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
