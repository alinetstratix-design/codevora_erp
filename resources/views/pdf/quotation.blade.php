@extends('pdf.layout')

@section('title', 'Quotation - ' . $report->header->quoteNo)

@section('content')

    <!-- PAGE 1: COVER LETTER -->
    @include('pdf.partials.cover-letter', [
        'company' => $report->company,
        'customer' => $report->customer,
        'signature' => $report->signature,
        'enclosures' => $report->enclosures
    ])

    <div class="page-break"></div>

    <!-- PAGES 2 TO 27: ITEM CARDS (EXACTLY 2 CARDS PER PAGE) -->
    @if(!empty($report->items))
        @foreach($report->items as $index => $item)
            
            @include('pdf.partials.item-card', ['item' => $item])

            <!-- Exactly 2 items per A4 page layout rule -->
            @if(($index + 1) % 2 == 0 && ($index + 1) < count($report->items))
                <div class="page-break"></div>
            @endif

        @endforeach
    @endif

    <div class="page-break"></div>

    <!-- PAGE 28: QUOTE FINANCIAL SUMMARY TABLE -->
    @include('pdf.partials.financial-summary', [
        'summary' => $report->summary,
        'financials' => $report->financials
    ])

    <div class="page-break"></div>

    <!-- PAGE 29: TERMS & CONDITIONS AND PRE-REQUISITES FOR INSTALLATION -->
    @include('pdf.partials.terms', [
        'terms' => $report->terms,
        'signature' => $report->signature
    ])

@endsection
