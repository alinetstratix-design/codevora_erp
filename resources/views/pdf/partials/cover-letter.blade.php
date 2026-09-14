<div class="cover-letter">
    <div class="cover-recipient" style="line-height: 1.5; margin-bottom: 20px;">
        <strong style="font-size: 11pt; color: #1b305b;">To,</strong><br>
        <strong style="font-size: 12pt; color: #0f172a;">{{ $customer->name }}</strong><br>
        @if(!empty($customer->companyName))
            <span>{{ $customer->companyName }}</span><br>
        @endif
        @if(!empty($customer->address))
            <span>{{ $customer->address }}</span><br>
        @endif
        @if(!empty($customer->phone))
            <span><strong>Phone:</strong> {{ $customer->phone }}</span>@if(!empty($customer->email)) | @endif
        @endif
        @if(!empty($customer->email))
            <span><strong>Email:</strong> {{ $customer->email }}</span><br>
        @elseif(!empty($customer->phone))
            <br>
        @endif
        @if(!empty($customer->gstNumber))
            <span><strong>GSTIN:</strong> {{ $customer->gstNumber }}</span><br>
        @endif
        @if(!empty($project->name) && $project->name !== 'Project' && $project->name !== 'Quotation Project')
            <span><strong>Project Site:</strong> {{ $project->name }} @if(!empty($project->location))({{ $project->location }})@endif</span><br>
        @endif
    </div>

    <p class="cover-para">Dear Customer,</p>
    <p class="cover-para">We are delighted that you are considering our range of Windows and Doors for your premises.</p>
    <p class="cover-para">It has gained rapid acceptance across all cities of India for the overwhelming advantages of better protection from noise, heat, rain, dust and pollution.</p>
    <p class="cover-para">In drawing this proposal, it has been our endeavor to suggest designs which would enhance your comfort and aesthetics from inside and improve the facade of the building.</p>
    <p class="cover-para">It has a well-established service network to deliver seamless service at your doorstep. Our offer comprises of the following in enclosure for your kind perusal:</p>

    <div class="enclosure-list">
        @foreach($enclosures as $enclosure)
            {{ $enclosure }}<br>
        @endforeach
    </div>

    <p class="cover-para">We now look forward to be of service to you.</p>

    <div style="margin-top: 30px;">
        <strong>For {{ $company->companyName }},</strong>
        <br><br><br><br>
        {{ $signature->leftLabel }}
    </div>
</div>
