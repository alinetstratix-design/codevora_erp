@extends('layouts.app')

@section('title', 'Quotation Builder')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="bi bi-file-earmark-diff"></i> EvA Commercial Quotation Builder</h5>
        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Quotations</a>
    </div>
    <div class="card-body">
        <form id="quotationForm" action="{{ route('quotations.store') }}" method="POST">
            @csrf
            
            <!-- Step 1: Customer Selection -->
            <div class="step-section mb-4">
                <h6 class="border-bottom pb-2 text-primary fw-bold"><i class="bi bi-person-fill"></i> Customer Details</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="customer_id" id="customer_id" class="form-select" required>
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ (request('customer_id') == $c->id) ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->company_name ?? 'Individual' }}) - {{ $c->phone }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                <i class="bi bi-person-plus"></i> New
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Quote Number</label>
                        <input type="text" name="quote_no" class="form-control fw-bold text-primary" value="{{ 'SCL-QT-' . str_pad(rand(1, 99999), 8, '0', STR_PAD_LEFT) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>

            <!-- Step 2: Project Details -->
            <div class="step-section mb-4">
                <h6 class="border-bottom pb-2 text-primary fw-bold"><i class="bi bi-building"></i> Project & Delivery Information</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Project Name <span class="text-danger">*</span></label>
                        <input type="text" name="project_name" class="form-control" value="AMBALA AIRFORCE" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Client / To Name <span class="text-danger">*</span></label>
                        <input type="text" name="client_name" class="form-control" value="AMBALA AIRFORCE" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Project Location</label>
                        <input type="text" name="project_location" class="form-control" value="AMBALA AIRFORCE">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Sales Person / Signatory</label>
                        <input type="text" name="sales_person" class="form-control" value="Authorized Signatory">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Valid Until</label>
                        <input type="date" name="valid_till" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Draft">Draft</option>
                            <option value="Sent">Sent</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 3: Window Items & Profile Specifications -->
            <div class="step-section mb-4">
                <h6 class="border-bottom pb-2 text-primary fw-bold"><i class="bi bi-grid-3x3"></i> Window & Door Profile Items</h6>
                
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm" id="itemsTable">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="20%">Code & Location</th>
                                <th width="25%">Profile System Template</th>
                                <th width="35%">Sizes (W x H mm) & Quantity</th>
                                <th width="10%">Rate / Sq.Ft. (₹)</th>
                                <th width="10%">Total Amount</th>
                                <th width="3%"></th>
                            </tr>
                        </thead>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addItemRow()"><i class="bi bi-plus-circle"></i> Add Window Component Item</button>
                </div>

                <!-- Auto Financial Summary -->
                <div class="row mt-4 border-top pt-3">
                    <div class="col-md-6">
                        <h6 class="text-primary fw-bold">Charges & Tax Configuration</h6>
                        <div class="row mb-2">
                            <div class="col-6">Discount (₹)</div>
                            <div class="col-6"><input type="number" step="0.01" name="discount" id="val_discount" class="form-control form-control-sm calc-trigger" value="0"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">Transportation / Freight (₹)</div>
                            <div class="col-6"><input type="number" step="0.01" name="transportation" id="val_transportation" class="form-control form-control-sm calc-trigger" value="0"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">Installation Cost (₹)</div>
                            <div class="col-6"><input type="number" step="0.01" name="installation" id="val_installation" class="form-control form-control-sm calc-trigger" value="0"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">GST Slab</div>
                            <div class="col-6">
                                <select name="gst_percent" id="val_gst_percent" class="form-select form-select-sm calc-trigger">
                                    <option value="0">0%</option>
                                    <option value="5">5%</option>
                                    <option value="12">12%</option>
                                    <option value="18" selected>18% (Standard GST)</option>
                                    <option value="28">28%</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-5 offset-md-1">
                        <div class="card bg-light shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="fw-bold text-primary border-bottom pb-2">EvA Auto Financial Summary</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Components:</span>
                                    <span id="txt_components" class="fw-bold">0 Pcs</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Area:</span>
                                    <span id="txt_total_area" class="fw-bold">0.00 Sq.Ft.</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Basic Subtotal:</span>
                                    <span id="txt_subtotal" class="fw-bold">₹0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span id="lbl_gst">GST (18%):</span>
                                    <span id="txt_gst" class="fw-bold text-danger">₹0.00</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between fs-5 text-primary">
                                    <strong>Grand Total:</strong>
                                    <strong id="txt_grand_total">₹0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mt-2">
                                    <span>Avg. Price / Sq.Ft. (ex-GST):</span>
                                    <span id="txt_avg_ex">₹0.00</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Avg. Price / Sq.Ft. (inc-GST):</span>
                                    <span id="txt_avg_inc">₹0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <label class="form-label text-primary fw-bold">Terms & Conditions</label>
                        <textarea name="terms_conditions[]" class="form-control form-control-sm mb-1" rows="2" placeholder="Term 1"></textarea>
                        <textarea name="terms_conditions[]" class="form-control form-control-sm mb-1" rows="2" placeholder="Term 2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-primary fw-bold">Payment & Bank Details</label>
                        <textarea name="bank_details[]" class="form-control form-control-sm mb-1" rows="2" placeholder="50% Advance with Order"></textarea>
                        <textarea name="bank_details[]" class="form-control form-control-sm mb-1" rows="2" placeholder="50% Before Delivery"></textarea>
                    </div>
                </div>

                <div id="formValidationError" class="alert alert-danger d-none mt-3 mb-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <span id="formValidationErrorText">Please complete all required fields marked with * before submitting.</span>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" id="submitBtn" class="btn btn-primary btn-lg px-5 shadow-sm"><i class="bi bi-file-earmark-pdf"></i> Save Quotation & Generate EvA PDF</button>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- Modal Quick Add Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="ajaxCustomerForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Quick Add Customer</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Customer Name *</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Mobile *</label>
            <input type="text" name="phone" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="alert alert-danger d-none" id="customerError"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="saveCustomerBtn">Save Customer</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Advanced Specs -->
<div class="modal fade" id="advancedSpecsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title text-primary"><i class="bi bi-sliders"></i> Detailed Specifications for <span id="specsItemCode" class="fw-bold"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="specsActiveRowIndex">
        
        <!-- General Item Details -->
        <h6 class="text-secondary border-bottom pb-2 mb-3"><i class="bi bi-info-circle"></i> General Item Details</h6>
        <div class="row g-2 mb-4">
            <div class="col-md-3"><label class="small fw-bold">Position / Name</label><input type="text" class="form-control form-control-sm spec-input-general" data-key="position" placeholder="e.g. W1"></div>
            <div class="col-md-3"><label class="small fw-bold">Location</label><input type="text" class="form-control form-control-sm spec-input-general" data-key="location" placeholder="e.g. Master Bedroom"></div>
            <div class="col-md-3"><label class="small fw-bold">Glass Specs</label><input type="text" class="form-control form-control-sm spec-input-general" data-key="glass_type" placeholder="e.g. (1) 5mm Clear Toughened"></div>
            <div class="col-md-3"><label class="small fw-bold">Hardware Specs</label><input type="text" class="form-control form-control-sm spec-input-general" data-key="hardware_brand" placeholder="e.g. CORA Hardware"></div>
            <div class="col-md-12 mt-2"><label class="small fw-bold">Remarks / Notes</label><input type="text" class="form-control form-control-sm spec-input-general" data-key="notes" placeholder="Any special instructions..."></div>
        </div>

        <div class="row">
            <!-- Profile Details Column -->
            <div class="col-md-6 border-end">
                <h6 class="text-secondary border-bottom pb-2 mb-3"><i class="bi bi-bounding-box-circles"></i> Profile Details</h6>
                <div class="row g-2">
                    <div class="col-6"><label class="small fw-bold">Profile Color</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Profile Color" value="WHITE"></div>
                    <div class="col-6"><label class="small fw-bold">MeshType</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="MeshType" value="No"></div>
                    <div class="col-12"><label class="small fw-bold">Casement Sash</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Casement Sash"></div>
                    <div class="col-12"><label class="small fw-bold">Casement Sash Ri</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Casement Sash Ri"></div>
                    <div class="col-6"><label class="small fw-bold">Outer</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Outer"></div>
                    <div class="col-6"><label class="small fw-bold">Outer Ri</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Outer Ri"></div>
                    <div class="col-12"><label class="small fw-bold">Door Panel</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Door Panel"></div>
                    <div class="col-6"><label class="small fw-bold">Sash Mullion</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Sash Mullion"></div>
                    <div class="col-6"><label class="small fw-bold">Mullion Ri</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Mullion Ri"></div>
                    <div class="col-12"><label class="small fw-bold">Coupler</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Coupler"></div>
                    <div class="col-6"><label class="small fw-bold">Flymesh Sash</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Flymesh Sash"></div>
                    <div class="col-6"><label class="small fw-bold">Flymesh Sash Ri</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Flymesh Sash Ri"></div>
                    <div class="col-6"><label class="small fw-bold">Guide Rail</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Guide Rail"></div>
                    <div class="col-6"><label class="small fw-bold">Interlock</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Interlock"></div>
                    <div class="col-6"><label class="small fw-bold">Sliding Sash</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Sliding Sash"></div>
                    <div class="col-6"><label class="small fw-bold">Sliding Sash Ri</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Sliding Sash Ri"></div>
                    <div class="col-6"><label class="small fw-bold">Track</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Track"></div>
                    <div class="col-6"><label class="small fw-bold">Track Ri</label><input type="text" class="form-control form-control-sm spec-input" data-group="profile" data-key="Track Ri"></div>
                </div>
            </div>
            <!-- Accessories Details Column -->
            <div class="col-md-6">
                <h6 class="text-secondary border-bottom pb-2 mb-3"><i class="bi bi-tools"></i> Accessories Details</h6>
                <div class="row g-2">
                    <div class="col-6"><label class="small fw-bold">Locking</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Locking"></div>
                    <div class="col-6"><label class="small fw-bold">Handle color</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Handle color" value="WHITE"></div>
                    <div class="col-6"><label class="small fw-bold">Arm Restrictor</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Arm Restrictor"></div>
                    <div class="col-6"><label class="small fw-bold">Cylinder</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Cylinder"></div>
                    <div class="col-12"><label class="small fw-bold">Handle Type 1</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Handle Type 1"></div>
                    <div class="col-12"><label class="small fw-bold">Handle Type 2</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Handle Type 2"></div>
                    <div class="col-6"><label class="small fw-bold">Hinge 1</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Hinge 1"></div>
                    <div class="col-6"><label class="small fw-bold">Hinge 2</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Hinge 2"></div>
                    <div class="col-6"><label class="small fw-bold">Friction</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Friction"></div>
                    <div class="col-6"><label class="small fw-bold">Roller</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Roller"></div>
                    <div class="col-12"><label class="small fw-bold">Flymesh Handle Type</label><input type="text" class="form-control form-control-sm spec-input" data-group="accessories" data-key="Flymesh Handle Type"></div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveAdvancedSpecs()"><i class="bi bi-check-circle"></i> Save & Apply to Item</button>
      </div>
    </div>
  </div>
</div>

<script>
    let itemIndex = 0;
    const products = @json($products);

    function addSizeRow(btn, iIndex) {
        const tbody = btn.closest('.item-group');
        const sizesContainer = tbody.querySelector('.sizes-container');
        const sizeIndex = tbody.querySelectorAll('.size-row').length;

        const div = document.createElement('div');
        div.className = 'd-flex align-items-center mb-1 size-row';
        div.innerHTML = `
            <input type="number" step="any" name="items[${iIndex}][sizes][${sizeIndex}][width]" class="form-control form-control-sm calc-trigger" placeholder="W (mm)" required style="width: 85px;">
            <span class="mx-1">x</span>
            <input type="number" step="any" name="items[${iIndex}][sizes][${sizeIndex}][height]" class="form-control form-control-sm calc-trigger" placeholder="H (mm)" required style="width: 85px;">
            <select name="items[${iIndex}][sizes][${sizeIndex}][unit]" class="form-select form-select-sm ms-1 calc-trigger" style="width: 70px;">
                <option value="mm">mm</option>
                <option value="cm">cm</option>
                <option value="inch">in</option>
                <option value="ft">ft</option>
            </select>
            <span class="mx-2">Qty:</span>
            <input type="number" step="any" name="items[${iIndex}][sizes][${sizeIndex}][quantity]" class="form-control form-control-sm calc-trigger" value="1" required style="width: 65px;">
            <input type="hidden" name="items[${iIndex}][sizes][${sizeIndex}][area]" class="size-area">
            <span class="ms-2 fw-bold text-primary size-area-display" style="width: 80px;">0.00 Sq.Ft.</span>
            <span class="ms-2 fw-bold text-success size-amount" style="width: 90px;">₹0.00</span>
            <button type="button" class="btn btn-sm text-danger ms-1" onclick="this.closest('.size-row').remove(); calculateTotals();"><i class="bi bi-trash"></i></button>
        `;
        sizesContainer.appendChild(div);
        div.querySelectorAll('.calc-trigger').forEach(el => el.addEventListener('input', calculateTotals));
        calculateTotals();
    }

    function addItemRow() {
        let options = '<option value="">Select Template...</option>';
        products.forEach(p => {
            options += `<option value="${p.id}" data-rate="${p.base_rate}">${p.name} - ${p.category}</option>`;
        });

        const codeVal = 'W' + (itemIndex + 1);

        const tbody = document.createElement('tbody');
        tbody.className = 'item-group border-bottom border-2';
        tbody.innerHTML = `
            <tr class="main-item-row bg-white">
                <td>
                    <input type="text" name="items[${itemIndex}][item_code]" class="form-control form-control-sm font-bold text-primary mb-1" value="${codeVal}" placeholder="Code e.g. W1">
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="openAdvancedSpecs(${itemIndex}, '${codeVal}')"><i class="bi bi-sliders"></i> Item Details & Specs</button>
                    <!-- Hidden general fields -->
                    <input type="hidden" name="items[${itemIndex}][position]" class="hidden-general" data-key="position" value="${codeVal}">
                    <input type="hidden" name="items[${itemIndex}][location]" class="hidden-general" data-key="location" value="">
                    <input type="hidden" name="items[${itemIndex}][profile_color]" class="hidden-general" data-key="profile_color" value="WHITE">
                    <input type="hidden" name="items[${itemIndex}][mesh_type]" class="hidden-general" data-key="mesh_type" value="No">
                    <input type="hidden" name="items[${itemIndex}][glass_type]" class="hidden-general" data-key="glass_type" value="(1) 5mm Clear Toughened">
                    <input type="hidden" name="items[${itemIndex}][hardware_brand]" class="hidden-general" data-key="hardware_brand" value="CORA Hardware">
                    <input type="hidden" name="items[${itemIndex}][notes]" class="hidden-general" data-key="notes" value="">
                </td>
                <td>
                    <select name="items[${itemIndex}][product_id]" class="form-select form-select-sm product-select fw-bold mb-1" required>
                        ${options}
                    </select>
                    <input type="text" name="items[${itemIndex}][profile_system]" class="form-control form-control-sm item-profile-system" value="CORA - 60MM CASEMENT SERIES" placeholder="Profile System">
                </td>
                <td class="bg-light">
                    <div class="sizes-container"></div>
                    <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="addSizeRow(this, ${itemIndex})">+ Add Dimension Size</button>
                </td>
                <td><input type="number" step="any" name="items[${itemIndex}][rate]" class="form-control form-control-sm calc-trigger item-rate" value="600" required></td>
                <td><input type="number" name="items[${itemIndex}][amount]" class="form-control form-control-sm item-amount fw-bold" value="0" readonly></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tbody').remove(); calculateTotals();"><i class="bi bi-x"></i></button></td>
            </tr>
            <tr class="d-none">
                <td colspan="6">
                    <div id="hiddenSpecs_${itemIndex}" class="d-none">
                        <!-- Profile Defaults -->
                        <input type="hidden" name="items[${itemIndex}][profile_details][Profile Color]" value="WHITE" class="spec-hidden" data-group="profile" data-key="Profile Color">
                        <input type="hidden" name="items[${itemIndex}][profile_details][MeshType]" value="No" class="spec-hidden" data-group="profile" data-key="MeshType">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Casement Sash]" value="" class="spec-hidden" data-group="profile" data-key="Casement Sash">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Casement Sash Ri]" value="" class="spec-hidden" data-group="profile" data-key="Casement Sash Ri">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Outer]" value="" class="spec-hidden" data-group="profile" data-key="Outer">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Outer Ri]" value="" class="spec-hidden" data-group="profile" data-key="Outer Ri">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Door Panel]" value="" class="spec-hidden" data-group="profile" data-key="Door Panel">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Sash Mullion]" value="" class="spec-hidden" data-group="profile" data-key="Sash Mullion">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Mullion Ri]" value="" class="spec-hidden" data-group="profile" data-key="Mullion Ri">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Coupler]" value="" class="spec-hidden" data-group="profile" data-key="Coupler">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Flymesh Sash]" value="" class="spec-hidden" data-group="profile" data-key="Flymesh Sash">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Flymesh Sash Ri]" value="" class="spec-hidden" data-group="profile" data-key="Flymesh Sash Ri">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Guide Rail]" value="" class="spec-hidden" data-group="profile" data-key="Guide Rail">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Interlock]" value="" class="spec-hidden" data-group="profile" data-key="Interlock">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Sliding Sash]" value="" class="spec-hidden" data-group="profile" data-key="Sliding Sash">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Sliding Sash Ri]" value="" class="spec-hidden" data-group="profile" data-key="Sliding Sash Ri">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Track]" value="" class="spec-hidden" data-group="profile" data-key="Track">
                        <input type="hidden" name="items[${itemIndex}][profile_details][Track Ri]" value="" class="spec-hidden" data-group="profile" data-key="Track Ri">
                        
                        <!-- Accessories Defaults -->
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Locking]" value="" class="spec-hidden" data-group="accessories" data-key="Locking">
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Handle color]" value="WHITE" class="spec-hidden" data-group="accessories" data-key="Handle color">
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Arm Restrictor]" value="" class="spec-hidden" data-group="accessories" data-key="Arm Restrictor">
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Cylinder]" value="" class="spec-hidden" data-group="accessories" data-key="Cylinder">
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Handle Type 1]" value="" class="spec-hidden" data-group="accessories" data-key="Handle Type 1">
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Handle Type 2]" value="" class="spec-hidden" data-group="accessories" data-key="Handle Type 2">
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Hinge 1]" value="" class="spec-hidden" data-group="accessories" data-key="Hinge 1">
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Hinge 2]" value="" class="spec-hidden" data-group="accessories" data-key="Hinge 2">
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Friction]" value="" class="spec-hidden" data-group="accessories" data-key="Friction">
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Roller]" value="" class="spec-hidden" data-group="accessories" data-key="Roller">
                        <input type="hidden" name="items[${itemIndex}][accessories_details][Flymesh Handle Type]" value="" class="spec-hidden" data-group="accessories" data-key="Flymesh Handle Type">
                    </div>
                </td>
            </tr>
        `;
        document.getElementById('itemsTable').appendChild(tbody);

        tbody.querySelector('.product-select').addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const pId = this.value;
            const product = products.find(p => p.id == pId);
            if(product) {
                tbody.querySelector('.item-rate').value = product.base_rate || 600;
                if(product.profile_series) tbody.querySelector('.item-profile-system').value = 'CORA - ' + product.profile_series;
                
                // General Fields Mapping
                if(product.glass_type) tbody.querySelector('.hidden-general[data-key="glass_type"]').value = product.glass_type;
                if(product.hardware_brand) tbody.querySelector('.hidden-general[data-key="hardware_brand"]').value = product.hardware_brand;
                if(product.mesh_type) tbody.querySelector('.hidden-general[data-key="mesh_type"]').value = product.mesh_type;
                
                // Exhaustive Profile Details Mapping
                if (product.profile_details) {
                    for (const [k, v] of Object.entries(product.profile_details)) {
                        const input = tbody.querySelector(`.spec-hidden[data-group="profile"][data-key="${k}"]`);
                        if (input) input.value = v || '';
                    }
                }
                
                // Exhaustive Accessories Details Mapping
                if (product.accessories_details) {
                    for (const [k, v] of Object.entries(product.accessories_details)) {
                        const input = tbody.querySelector(`.spec-hidden[data-group="accessories"][data-key="${k}"]`);
                        if (input) input.value = v || '';
                    }
                }
            }
            calculateTotals();
        });

        tbody.querySelectorAll('.calc-trigger').forEach(el => el.addEventListener('input', calculateTotals));
        addSizeRow(tbody.querySelector('.btn-link'), itemIndex);
        itemIndex++;
    }

    function calculateTotals() {
        const formData = new FormData(document.getElementById('quotationForm'));
        
        fetch("{{ route('quotations.calculate') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.items) {
                const tbodys = document.querySelectorAll('#itemsTable tbody.item-group');
                data.items.forEach((item, idx) => {
                    if (tbodys[idx]) {
                        const amountInput = tbodys[idx].querySelector('.item-amount');
                        if (amountInput) amountInput.value = item.amount.toFixed(2);

                        if(item.sizes) {
                            const sizeRows = tbodys[idx].querySelectorAll('.size-row');
                            item.sizes.forEach((s, sIdx) => {
                                if(sizeRows[sIdx]) {
                                    sizeRows[sIdx].querySelector('.size-area').value = s.area;
                                    const areaDisplay = sizeRows[sIdx].querySelector('.size-area-display');
                                    if(areaDisplay) areaDisplay.innerText = s.area.toFixed(2) + ' Sq.Ft.';
                                    sizeRows[sIdx].querySelector('.size-amount').innerText = '₹' + s.amount.toFixed(2);
                                }
                            });
                        }
                    }
                });
            }

            document.getElementById('txt_components').innerText = (data.no_of_components || 0) + ' Pcs';
            document.getElementById('txt_total_area').innerText = (data.total_area_sqft || 0).toFixed(2) + ' Sq.Ft.';
            document.getElementById('txt_subtotal').innerText = '₹' + data.subtotal.toFixed(2);
            document.getElementById('lbl_gst').innerText = 'GST (' + data.gst_percent + '%):';
            document.getElementById('txt_gst').innerText = '₹' + data.gst.toFixed(2);
            document.getElementById('txt_grand_total').innerText = '₹' + data.grand_total.toFixed(2);
            document.getElementById('txt_avg_ex').innerText = '₹' + (data.avg_price_sqft_ex_gst || 0).toFixed(2);
            document.getElementById('txt_avg_inc').innerText = '₹' + (data.avg_price_sqft_inc_gst || 0).toFixed(2);
        });
    }

    function openAdvancedSpecs(index, codeVal) {
        document.getElementById('specsItemCode').innerText = codeVal;
        document.getElementById('specsActiveRowIndex').value = index;
        
        // Load data from hidden fields into modal
        const hiddenDiv = document.getElementById('hiddenSpecs_' + index);
        if(hiddenDiv) {
            hiddenDiv.querySelectorAll('.spec-hidden').forEach(hiddenInput => {
                const group = hiddenInput.getAttribute('data-group');
                const key = hiddenInput.getAttribute('data-key');
                const modalInput = document.querySelector(`.spec-input[data-group="${group}"][data-key="${key}"]`);
                if(modalInput) {
                    modalInput.value = hiddenInput.value;
                }
            });
        }

        // Load general details
        const tbody = document.querySelector(`input[name="items[${index}][item_code]"]`).closest('tbody');
        tbody.querySelectorAll('.hidden-general').forEach(hiddenInput => {
            const key = hiddenInput.getAttribute('data-key');
            const modalInput = document.querySelector(`.spec-input-general[data-key="${key}"]`);
            if(modalInput) {
                modalInput.value = hiddenInput.value;
            }
        });
        
        var specsModal = new bootstrap.Modal(document.getElementById('advancedSpecsModal'));
        specsModal.show();
    }

    function saveAdvancedSpecs() {
        const index = document.getElementById('specsActiveRowIndex').value;
        const hiddenDiv = document.getElementById('hiddenSpecs_' + index);
        const tbody = document.querySelector(`input[name="items[${index}][item_code]"]`).closest('tbody');
        
        if(hiddenDiv) {
            // Save data from modal into hidden fields
            document.querySelectorAll('.spec-input').forEach(modalInput => {
                const group = modalInput.getAttribute('data-group');
                const key = modalInput.getAttribute('data-key');
                const hiddenInput = hiddenDiv.querySelector(`.spec-hidden[data-group="${group}"][data-key="${key}"]`);
                if(hiddenInput) {
                    hiddenInput.value = modalInput.value;
                }
            });
            
            // Sync Profile Color & Mesh Type back to general fields
            const pColor = document.querySelector(`.spec-input[data-group="profile"][data-key="Profile Color"]`).value;
            const mType = document.querySelector(`.spec-input[data-group="profile"][data-key="MeshType"]`).value;
            
            const colorInput = tbody.querySelector(`.hidden-general[data-key="profile_color"]`);
            if(colorInput) colorInput.value = pColor;
            
            const meshInput = tbody.querySelector(`.hidden-general[data-key="mesh_type"]`);
            if(meshInput) meshInput.value = mType;
        }

        // Save general details
        if(tbody) {
            document.querySelectorAll('.spec-input-general').forEach(modalInput => {
                const key = modalInput.getAttribute('data-key');
                const hiddenInput = tbody.querySelector(`.hidden-general[data-key="${key}"]`);
                if(hiddenInput) {
                    hiddenInput.value = modalInput.value;
                }
            });
        }
        
        bootstrap.Modal.getInstance(document.getElementById('advancedSpecsModal')).hide();
    }

    document.addEventListener("DOMContentLoaded", () => addItemRow());

    document.getElementById('ajaxCustomerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('saveCustomerBtn');
        const errDiv = document.getElementById('customerError');
        btn.disabled = true;
        btn.innerText = 'Saving...';
        errDiv.classList.add('d-none');

        const formData = new FormData(this);
        
        fetch("{{ route('customers.storeAjax') }}", {
            method: 'POST',
            body: formData,
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async (response) => {
            if (!response.ok) {
                const data = await response.json();
                throw data;
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                const select = document.getElementById('customer_id');
                const opt = new Option(`${data.customer.name} (${data.customer.company_name || ''})`, data.customer.id, true, true);
                select.add(opt);
                bootstrap.Modal.getInstance(document.getElementById('addCustomerModal')).hide();
                this.reset();
            }
        })
        .catch(err => {
            errDiv.innerText = err.message || 'Validation Failed. Please check phone and email uniqueness.';
            errDiv.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Save Customer';
        });
    });
    document.getElementById('quotationForm').addEventListener('submit', function(e) {
        const errorDiv = document.getElementById('formValidationError');
        const errorText = document.getElementById('formValidationErrorText');
        const btn = document.getElementById('submitBtn');
        
        errorDiv.classList.add('d-none');

        // Verify that at least one item row exists with sizes
        const itemGroups = document.querySelectorAll('#itemsTable tbody.item-group');
        if (itemGroups.length === 0) {
            e.preventDefault();
            errorText.innerText = 'Validation Error: Please add at least one window component item before saving.';
            errorDiv.classList.remove('d-none');
            return false;
        }

        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            
            const invalidInput = this.querySelector(':invalid');
            if (invalidInput) {
                invalidInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => invalidInput.focus(), 300);
                
                let labelName = 'required fields';
                const formGroup = invalidInput.closest('.mb-3, td, div');
                if (formGroup) {
                    const label = formGroup.querySelector('label, th');
                    if (label) labelName = label.innerText.replace('*', '').trim();
                }
                
                errorText.innerText = `Validation Error: Please fill in "${labelName}" properly.`;
                errorDiv.classList.remove('d-none');
            }
            this.classList.add('was-validated');
            return false;
        }

        // Disable button & show spinner during processing
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Generating EvA PDF & Saving...`;
    });
</script>
@endsection
