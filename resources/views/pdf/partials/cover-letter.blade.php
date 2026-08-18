<div class="cover-letter">
    <div class="cover-recipient">
        <strong>To</strong><br>
        <strong>{{ $customer->name }}</strong>
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
