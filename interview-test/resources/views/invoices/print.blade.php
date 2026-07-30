@extends('layouts.master')

@section('title', 'Invoice #' . ($invoice->invoice_ref_no ?? 'N/A'))

@section('content')
<div class="invoice-container">
    <!-- Header -->
    <div class="invoice-header">
        <div>
            <h1>INVOICE</h1>
            <span class="ref">Ref: {{ $invoice->invoice_ref_no ?? 'N/A' }}</span>
        </div>
        <div class="company-info">
            <h3><strong>Easy <span style="color: #87CEEB;">Pickup</span></strong></h3>
            <p>123 Main Street, Colombo</p>
            <p>Tel: +94 11 234 5678</p>
            <p>Email: info@carrental.com</p>
        </div>
    </div>

    <!-- Invoice Details -->
    <div class="invoice-details">
        <div class="detail-item">
            <strong>Invoice Date</strong>
            {{ $invoice->created_at ? date('d/m/Y H:i', strtotime($invoice->created_at)) : 'N/A' }}
        </div>
        <div class="detail-item">
            <strong>Payment Status</strong>
            <span class="status-badge status-{{ $invoice->payment_status ?? 'pending' }}">
                {{ ucfirst($invoice->payment_status ?? 'Pending') }}
            </span>
        </div>
        <div class="detail-item">
            <strong>Payment Method</strong>
            {{ ucfirst($invoice->payment_method ?? 'N/A') }}
        </div>
        <div class="detail-item">
            <strong>Paid At</strong>
            {{ $invoice->paid_at ? date('d/m/Y H:i', strtotime($invoice->paid_at)) : 'Not Paid' }}
        </div>
    </div>

    <!-- Customer & Car Details -->
    <h4 class="section-title">Customer &amp; Car Details</h4>
    <div class="customer-info">
        <div class="info-group">
            <label>Customer Name</label>
            <p><strong>{{ $invoice->user->name ?? 'N/A' }}</strong></p>
            <label>NIC</label>
            <p>{{ $invoice->user->id_num ?? 'N/A' }}</p>
            <label>Email</label>
            <p>{{ $invoice->user->email ?? 'N/A' }}</p>
        </div>
        <div class="info-group">
            <label>Car Details</label>
            <p><strong>{{ $invoice->booking->car->name ?? 'No Car Found' }}</strong></p>
            <label>Number Plate</label>
            <p>{{ $invoice->booking->car->number_plate ?? 'N/A' }}</p>
            <label>Reference No</label>
            <p>{{ $invoice->booking->car->ref_no ?? 'N/A' }}</p>
            <label>Price Per Hour</label>
            <p>Rs. {{ number_format($invoice->booking->car->rent_price_per_hour ?? 0, 2) }}</p>
        </div>
        <div class="info-group">
            <label>Rental Period</label>
            <p><strong>Start:</strong> {{ $invoice->booking->rental_start_date ? date('d/m/Y H:i', strtotime($invoice->booking->rental_start_date)) : 'N/A' }}</p>
            <p><strong>End:</strong> {{ $invoice->booking->rental_end_date ? date('d/m/Y H:i', strtotime($invoice->booking->rental_end_date)) : 'N/A' }}</p>
            <p><strong>Returned:</strong> {{ $invoice->returned_date ? date('d/m/Y H:i', strtotime($invoice->returned_date)) : 'N/A' }}</p>
        </div>
    </div>

    <!-- Cost Breakdown -->
    <h4 class="section-title">Cost Breakdown</h4>
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Get car from booking as fallback
                $car = $invoice->car ?? $invoice->booking->car ?? null;
                $pricePerHour = $car->rent_price_per_hour ?? 0;
                
                // Calculate hours
                $expectedHours = $invoice->booking ? $invoice->booking->getDurationInHours() : 0;
                $actualHours = $invoice->actual_hours ?? $expectedHours;
                $extraHours = max(0, $actualHours - $expectedHours);
                
                // Costs
                $baseCost = $expectedHours * $pricePerHour;
                $extraCost = $invoice->extra_cost ?? ($extraHours * $pricePerHour * 2);
                $discountAmount = $invoice->discount_amount ?? 0;
                $discountPercentage = $invoice->discount_percentage ?? 0;
                $fineAmount = $invoice->fine_amount ?? 0;
                $fineReason = $invoice->fine_reason ?? null;
                $totalCost = $invoice->total_cost ?? ($baseCost + $extraCost - $discountAmount + $fineAmount);
            @endphp
            
            @if($pricePerHour > 0)
            <tr>
                <td>Base Cost ({{ $expectedHours }} hrs × Rs. {{ number_format($pricePerHour, 2) }})</td>
                <td class="text-right">Rs. {{ number_format($baseCost, 2) }}</td>
            </tr>
            @if($extraHours > 0)
            <tr>
                <td>Extra Hours ({{ $extraHours }} hrs × 2 × Rs. {{ number_format($pricePerHour, 2) }})</td>
                <td class="text-right">Rs. {{ number_format($extraCost, 2) }}</td>
            </tr>
            @endif
            @if($discountPercentage > 0)
            <tr>
                <td>Discount ({{ $discountPercentage }}% - Loyal Customer)</td>
                <td class="text-right">- Rs. {{ number_format($discountAmount, 2) }}</td>
            </tr>
            @endif
            @if($fineAmount > 0)
            <tr>
                <td>Additional Charges / Fine @if($fineReason) ({{ $fineReason }}) @endif</td>
                <td class="text-right">Rs. {{ number_format($fineAmount, 2) }}</td>
            </tr>
            @endif
            @else
            <tr>
                <td colspan="2" class="text-center text-danger">No price information available for this car.</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Total Section -->
    <div class="total-section">
        @if($pricePerHour > 0)
        <div class="total-row">
            <span class="label">Sub Total:</span>
            <span class="value">Rs. {{ number_format($baseCost + $extraCost, 2) }}</span>
        </div>
        @if($discountPercentage > 0)
        <div class="total-row discount-row">
            <span class="label">Discount ({{ $discountPercentage }}%):</span>
            <span class="value">- Rs. {{ number_format($discountAmount, 2) }}</span>
        </div>
        @endif
        @if($fineAmount > 0)
        <div class="total-row fine-row">
            <span class="label">Additional Charges:</span>
            <span class="value">Rs. {{ number_format($fineAmount, 2) }}</span>
        </div>
        @endif
        @endif
        <div class="total-row grand-total">
            <span class="label">Total Cost:</span>
            <span class="value">Rs. {{ number_format($totalCost, 2) }}</span>
        </div>
    </div>

    <!-- Notes -->
    @if($invoice->notes)
    <div class="notes-section">
        <h4 class="section-title">Notes</h4>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="invoice-footer">
        <p>Thank you for choosing our service!</p>
        <p>This is a computer-generated invoice.</p>
        <div class="no-print">
            <button onclick="window.print()" class="btn-print">🖨️ Print Invoice</button>
            <br><br>
            <a href="{{ route('invoices.index') }}" class="btn-back">← Back to Invoices</a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/invoice-print.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/invoice-print.js') }}"></script>
@endpush