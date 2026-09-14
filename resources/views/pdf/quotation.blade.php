@extends('pdf.layout')

@section('title', 'Quotation - ' . $report->header->quoteNo)

@section('content')

    <!-- PAGE 1: COVER LETTER -->
    @include('pdf.partials.cover-letter', [
        'company' => $report->company,
        'customer' => $report->customer,
        'project' => $report->project,
        'signature' => $report->signature,
        'enclosures' => $report->enclosures
    ])

    <div class="page-break"></div>

    <!-- PAGES 2+: ITEM CARDS (EXACTLY 1 WORK QUOTE PER PAGE) -->
    @if(!empty($report->items))
        @foreach($report->items as $index => $item)
            
            @include('pdf.partials.item-card', ['item' => $item])

            <!-- Exactly 1 work quote per page -->
            @if(($index + 1) < count($report->items))
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
