@extends('layouts.app')

@section('title', 'Edit Quotation ' . ($quotation->quotation_number ?? $quotation->quote_no))

@section('content')
<div class="container-fluid pb-5">
    
    <!-- TOP BAR: CUSTOMER & PROJECT -->
    <div class="card shadow-sm border-0 rounded-3 mb-3" id="setup-panel">
        <div class="card-header bg-white border-bottom-0 py-2 d-flex justify-content-between align-items-center cursor-pointer" onclick="toggleSetup()">
            <h6 class="mb-0 text-primary fw-bold" style="font-family: 'Inter', sans-serif;">
                <i class="bi bi-person-workspace me-2"></i> Project Details
            </h6>
            <span id="setup-summary" class="text-muted small fw-semibold d-none"></span>
            <i class="bi bi-chevron-up text-muted small" id="setup-chevron"></i>
        </div>
        <div class="card-body px-3 pb-3 pt-0" id="setup-body">
            <form id="setupForm">
                @csrf
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-1">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-select form-select-sm shadow-sm border-0 bg-light" onchange="onCustomerSelect(this)" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $c)
                                @php
                                    $isSelected = ($quotation->customer_id == $c->id) 
                                        || (empty($quotation->customer_id) && (
                                            stripos($quotation->client_name, $c->name) !== false 
                                            || (!empty($c->company_name) && stripos($quotation->client_name, $c->company_name) !== false)
                                        ));
                                @endphp
                                <option value="{{ $c->id }}" data-phone="{{ $c->phone }}" data-email="{{ $c->email }}" data-address="{{ $c->address }}" {{ $isSelected ? 'selected' : '' }}>{{ $c->name }} ({{ $c->company_name ?? 'Individual' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-1">Project Name</label>
                        <input type="text" name="project_name" id="project_name" class="form-control form-control-sm shadow-sm border-0 bg-light" value="{{ $quotation->project_name }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-1">Client / To Name</label>
                        <input type="text" name="client_name" id="client_name" class="form-control form-control-sm shadow-sm border-0 bg-light" value="{{ $quotation->client_name }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-1">Quote No</label>
                        <input type="text" name="quote_no" id="quote_no" class="form-control form-control-sm shadow-sm border-0 bg-light text-primary fw-bold" value="{{ $quotation->quotation_number ?? $quotation->quote_no }}" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-1">Date</label>
                        <input type="date" name="date" id="date" class="form-control form-control-sm shadow-sm border-0 bg-light" value="{{ $quotation->quotation_date ? \Carbon\Carbon::parse($quotation->quotation_date)->format('Y-m-d') : date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6 mt-2">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-1">Project Site Address / Location</label>
                        <input type="text" name="project_location" id="project_location" class="form-control form-control-sm shadow-sm border-0 bg-light" placeholder="Site Location / Installation Address" value="{{ $quotation->project_location ?? $quotation->address ?? '' }}">
                    </div>
                    <div class="col-md-6 mt-2">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-1">Customer Contact Info</label>
                        <div class="small py-1" id="customer-contact-info">
                            @if($quotation->customer)
                                <span class="badge bg-light text-dark border me-1"><i class="bi bi-telephone me-1"></i>{{ $quotation->customer->phone ?? 'No Phone' }}</span>
                                <span class="badge bg-light text-dark border"><i class="bi bi-envelope me-1"></i>{{ $quotation->customer->email ?? 'No Email' }}</span>
                            @else
                                <span class="text-muted fst-italic">Select customer to view contact details</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 text-end mt-3">
                        <button type="button" class="btn btn-primary btn-sm px-4 rounded-pill shadow-sm hover-lift fw-semibold" onclick="startStudio()">Enter Studio <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN STUDIO PANEL -->
    <div class="row g-3 d-none" id="studio-panel">
        
        <!-- LEFT: PRODUCT SELECTION GRID -->
        <div class="col-lg-8 col-xl-9">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Product Catalog</h4>
                    <p class="text-muted small mb-0">Select a system to start configuring dimensions and specifications.</p>
                </div>
            </div>
            
            <div class="row g-4" id="product-list">
                @foreach($products as $p)
                    <div class="col-md-6 col-xxl-4">
                        <div class="card border-0 shadow-sm hover-lift cursor-pointer h-100 overflow-hidden product-card" 
                             id="prod-card-{{ $p->id }}" 
                             onclick="openConfigModal({{ $p->id }}, '{{ addslashes($p->name) }}')">
                            <div class="row g-0 h-100">
                                <div class="col-4 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center p-3 border-end border-white">
                                    <i class="bi bi-boxes text-primary" style="font-size: 2.5rem;"></i>
                                </div>
                                <div class="col-8">
                                    <div class="card-body d-flex flex-column h-100 p-3">
                                        <h6 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">{{ $p->name }}</h6>
                                        <p class="text-muted small mb-3" style="font-size: 0.75rem; line-height: 1.4;">Premium {{ str_contains(strtolower($p->name), 'sliding') ? 'sliding system' : 'casement system' }} customizable with multiple glass and mesh options.</p>
                                        <div class="mt-auto text-end">
                                            <span class="badge bg-primary text-white px-3 py-2 rounded-pill shadow-sm">
                                                Configure <i class="bi bi-arrow-right ms-1"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- RIGHT: CART -->
        <div class="col-lg-4 col-xl-3">
            <div class="card shadow-sm border-0 rounded-3 h-100 d-flex flex-column">
                <div class="card-header bg-white border-bottom pt-3 pb-2">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-basket me-2"></i> Current Quote</h6>
                </div>
                
                <div class="card-body p-0 overflow-auto" style="height: 65vh;" id="cart-items-container">
                    <div class="text-center text-muted p-5 mt-5">
                        <i class="bi bi-cart-x fs-1 opacity-50 mb-3 d-block"></i>
                        <p class="mb-0 small">Quote is empty.</p>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-top rounded-bottom-3 p-3 mt-auto shadow-sm position-relative z-index-1">
                    <div id="zero-cost-warning" class="alert alert-warning py-2 px-2 small fw-semibold d-none mb-2 border-warning" role="alert" style="font-size: 0.75rem;">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Missing material costs detected.
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted fw-semibold small">Basic Subtotal</span>
                        <span class="text-dark fw-bold small" id="cart-subtotal">₹ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-semibold small">Total (Inc. GST)</span>
                        <h4 class="text-primary fw-bold mb-0" id="cart-total">₹ 0.00</h4>
                    </div>
                    <button type="button" id="btn-save" class="btn btn-success btn-sm w-100 rounded-pill shadow-sm hover-lift fw-bold" onclick="saveQuotation()">
                        Update Quote & PDF <i class="bi bi-file-earmark-pdf ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- CONFIGURATION MODAL -->
<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header bg-light border-bottom-0 py-3">
        <h5 class="modal-title fw-bold text-dark" id="configModalLabel">
            Configure: <span id="modal-product-name" class="text-primary"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 bg-white">
          <div class="row g-0 h-100">
              
              <!-- Left: Design List & Visualizer -->
              <div class="col-lg-7 border-end d-flex flex-column">
                  <div class="p-3 border-bottom bg-light">
                      <h6 class="fw-bold text-muted small text-uppercase mb-2">1. Select Design</h6>
                      <div class="d-flex gap-2 overflow-auto custom-scrollbar pb-2" id="design-list">
                          <!-- Injected via JS -->
                      </div>
                  </div>
                  <div class="flex-grow-1 bg-white p-4 d-flex justify-content-center align-items-center position-relative shadow-inner" style="min-height: 400px;" id="svg-container">
                      <!-- SVG Injected Here -->
                      <div class="text-muted small"><i class="bi bi-arrow-up"></i> Select a design above</div>
                  </div>
              </div>

              <!-- Right: Specifications & Dimensions -->
              <div class="col-lg-5 d-flex flex-column bg-light">
                  <div class="p-4 flex-grow-1 overflow-auto">
                      
                      <!-- DYNAMIC SPECS -->
                      <h6 class="fw-bold text-muted small text-uppercase mb-3 border-bottom pb-2">2. Specifications</h6>
                      <div class="row g-2 mb-4">
                          <div class="col-md-6">
                              <label class="form-label small fw-semibold text-dark mb-1">Glass Type</label>
                              <select class="form-select form-select-sm shadow-sm border-0" id="cfg-glass">
                                  <option value="">Default (From BOM)</option>
                                  <option value="5mm Clear">5mm Clear</option>
                                  <option value="6mm Toughened">6mm Toughened</option>
                                  <option value="8mm Toughened">8mm Toughened</option>
                                  <option value="12mm Toughened">12mm Toughened</option>
                                  <option value="DGU 24mm">DGU 24mm</option>
                              </select>
                          </div>
                          <div class="col-md-6">
                              <label class="form-label small fw-semibold text-dark mb-1">Mesh Type</label>
                              <select class="form-select form-select-sm shadow-sm border-0" id="cfg-mesh">
                                  <option value="">Default</option>
                                  <option value="Fiber Mesh">Fiber Mesh</option>
                                  <option value="SS Mesh">SS Mesh (Stainless Steel)</option>
                              </select>
                          </div>
                          <div class="col-md-6">
                              <label class="form-label small fw-semibold text-dark mb-1">Profile Color</label>
                              <select class="form-select form-select-sm shadow-sm border-0" id="cfg-profile-color">
                                  <option value="">Standard White</option>
                                  <option value="Dark Grey">Dark Grey</option>
                                  <option value="Wooden Finish">Wooden Finish</option>
                                  <option value="Black">Black</option>
                              </select>
                          </div>
                          <div class="col-md-6">
                              <label class="form-label small fw-semibold text-dark mb-1">Hardware Color</label>
                              <select class="form-select form-select-sm shadow-sm border-0" id="cfg-hardware-color">
                                  <option value="">Default</option>
                                  <option value="White">White</option>
                                  <option value="Black">Black</option>
                                  <option value="Silver">Silver</option>
                              </select>
                          </div>
                      </div>

                      <!-- DIMENSIONS -->
                      <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                          <h6 class="fw-bold text-muted small text-uppercase mb-0">3. Dimensions</h6>
                          <div class="d-flex align-items-center gap-1">
                              <label class="small text-muted fw-bold mb-0 me-1" style="font-size: 0.72rem;">UNIT:</label>
                              <select class="form-select form-select-sm border shadow-sm fw-bold text-primary py-0 px-2" id="cfg-unit" style="width: 85px; height: 26px; font-size: 0.8rem;" onchange="onUnitChange(this.value)">
                                  <option value="mm" selected>mm</option>
                                  <option value="inch">inch</option>
                                  <option value="cm">cm</option>
                                  <option value="ft">ft</option>
                              </select>
                          </div>
                      </div>
                      <div class="row g-2 mb-3">
                          <div class="col-4">
                              <label class="form-label small fw-semibold text-dark mb-1" id="lbl-cfg-width">Width (mm)</label>
                              <input type="number" step="any" id="cfg-width" class="form-control form-control-sm border-0 shadow-sm fw-bold" value="1200" oninput="updateConfigurator()" tabindex="1">
                          </div>
                          <div class="col-4">
                              <label class="form-label small fw-semibold text-dark mb-1" id="lbl-cfg-height">Height (mm)</label>
                              <input type="number" step="any" id="cfg-height" class="form-control form-control-sm border-0 shadow-sm fw-bold" value="1500" oninput="updateConfigurator()" tabindex="2">
                          </div>
                          <div class="col-4">
                              <label class="form-label small fw-semibold text-dark mb-1">Qty</label>
                              <input type="number" min="1" id="cfg-qty" class="form-control form-control-sm border-0 shadow-sm fw-bold" value="1" tabindex="3" onkeypress="handleQtyEnter(event)">
                          </div>
                      </div>
                      
                      <button type="button" class="btn btn-secondary btn-sm w-100 shadow-sm hover-lift fw-semibold mb-3" onclick="queueSize()" tabindex="4">
                          <i class="bi bi-plus-circle me-1"></i> Add Size to Batch
                      </button>

                      <div class="card border-0 bg-white shadow-sm rounded-3">
                          <div class="card-header bg-transparent border-bottom pt-2 pb-1">
                              <span class="fw-bold text-muted small text-uppercase">Sizes in this batch</span>
                          </div>
                          <div class="card-body p-0 overflow-auto" style="max-height: 120px;">
                              <table class="table table-sm table-hover text-center align-middle mb-0" style="font-size: 0.82rem;">
                                  <thead class="text-muted table-light">
                                      <tr><th>W</th><th>H</th><th>Unit</th><th>Qty</th><th>Sq.Ft.</th><th></th></tr>
                                  </thead>
                                  <tbody id="staged-sizes-body">
                                      <tr><td colspan="6" class="text-muted py-2 small">No sizes queued.</td></tr>
                                  </tbody>
                              </table>
                          </div>
                      </div>

                      <!-- 4. CUSTOM WORK RATE / PRICE -->
                      <div class="mt-3 pt-2 border-top">
                          <div class="d-flex justify-content-between align-items-center mb-1">
                              <label class="form-label small fw-semibold text-dark mb-0">Custom Work Rate (₹ / Sq.Ft.)</label>
                              <small class="text-muted" style="font-size: 0.72rem;">Optional (auto BOM if empty)</small>
                          </div>
                          <div class="input-group input-group-sm shadow-sm">
                              <span class="input-group-text bg-white border-0 text-muted fw-bold">₹</span>
                              <input type="number" step="0.01" min="0" id="cfg-rate-sqft" class="form-control border-0 fw-bold text-primary" placeholder="Auto system rate">
                              <span class="input-group-text bg-white border-0 text-muted small">/ sq.ft</span>
                          </div>
                      </div>

                  </div>
                  
                  <div class="p-3 border-top bg-white mt-auto">
                      <button type="button" class="btn btn-primary btn-sm w-100 rounded-pill shadow-sm hover-lift fw-bold py-2" onclick="addToCart()" tabindex="5" id="btn-add-to-quote">
                          Update Item in Quote <i class="bi bi-cart-check ms-1"></i>
                      </button>
                  </div>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Premium Typography & Spacing */
body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
.hover-lift { transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,.08)!important; }
.cursor-pointer { cursor: pointer; }

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
.custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #adb5bd; }

/* Active States */
.card.active-card { border: 2px solid #0d6efd !important; background-color: #f8fbff; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(13,110,253,.1)!important; }
.active-card .prod-icon-bg { background-color: #0d6efd !important; }
.active-card .prod-icon { color: white !important; }

/* Soft inner shadow for SVG container */
.shadow-inner { box-shadow: inset 0 2px 10px rgba(0,0,0,0.03); }

/* Cart Item Styling */
.cart-item { border-bottom: 1px solid #f1f3f5; transition: background-color 0.2s; padding: 12px 15px; }
.cart-item:hover { background-color: #f8fbff; }
.cart-item-actions { opacity: 0; transition: opacity 0.2s; }
.cart-item:hover .cart-item-actions { opacity: 1; }

.z-index-1 { z-index: 1; }
#svg-container { max-height: 420px; overflow: hidden; }
#svg-container svg { max-width: 100%; max-height: 380px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto; }
</style>

<script src="{{ asset('js/visual-configurator.js') }}"></script>
<script>
    // Initial Data from Server
    const initialData = {!! json_encode($quoteData) !!};
    const productsMap = @json($products->keyBy('id'));

    // State
    let selectedProductId = null;
    let selectedProductName = null;
    let selectedDesign = null;
    let cart = [];
    let stagedSizes = [];
    let configurator = null;
    let previewDebounceTimer = null;
    let configModalInstance = null;
    let editingCartIdx = null;

    document.addEventListener("DOMContentLoaded", () => {
        configurator = new window.VisualConfigurator('#svg-container');
        configModalInstance = new bootstrap.Modal(document.getElementById('configModal'));
        
        // Auto-start studio for edit
        startStudio();
        
        // Populate Cart
        if(initialData && initialData.items) {
            cart = initialData.items.map((item, idx) => {
                const totalQty = item.sizes ? item.sizes.reduce((acc, s) => acc + (s.qty || s.quantity || 1), 0) : 1;
                const primaryUnit = item.unit || (item.sizes && item.sizes[0] ? item.sizes[0].unit : 'mm');
                const prod = productsMap[item.product_id] || {};
                return {
                    id: item.id || null,
                    is_existing: !!item.id,
                    product_id: item.product_id,
                    product_name: item.product_name,
                    profile_brand: item.profile_brand || prod.profile_brand || '',
                    profile_series: item.profile_series || prod.profile_series || '',
                    profile_system: item.profile_system || prod.profile_series || prod.category || 'Casement Series',
                    design_id: item.design_id,
                    design_name: item.design_name,
                    sizes: (item.sizes || []).map(s => ({
                        id: s.id || null,
                        is_existing: !!s.id,
                        width: s.width,
                        height: s.height,
                        quantity: s.qty || s.quantity || 1,
                        unit: s.unit || primaryUnit
                    })),
                    unit: primaryUnit,
                    total_qty: totalQty,
                    svg: '', // Will be re-rendered on edit
                    drawing_b64: item.drawing_b64 || '',
                    glass_type: item.glass_type || prod.glass_type || '',
                    glass_thickness: item.glass_thickness || prod.glass_thickness || '',
                    mesh_type: item.mesh_type || prod.mesh_type || 'No',
                    profile_color: item.profile_color || 'Standard White',
                    hardware_color: item.hardware_color || 'White',
                    hardware_brand: item.hardware_brand || prod.hardware_brand || '',
                    profile_details: item.profile_details || prod.profile_details || {},
                    accessories_details: item.accessories_details || prod.accessories_details || []
                };
            });
            renderCartUI();
        }
    });

    // ----- UI FLOW -----
    function toggleSetup() {
        const body = document.getElementById('setup-body');
        const chev = document.getElementById('setup-chevron');
        if(body.classList.contains('d-none')) {
            body.classList.remove('d-none');
            chev.classList.remove('bi-chevron-down');
            chev.classList.add('bi-chevron-up');
        } else {
            body.classList.add('d-none');
            chev.classList.remove('bi-chevron-up');
            chev.classList.add('bi-chevron-down');
        }
    }

    function onCustomerSelect(selectElem) {
        if (!selectElem.value) return;
        const opt = selectElem.options[selectElem.selectedIndex];
        const clientNameInput = document.getElementById('client_name');
        if (clientNameInput && (!clientNameInput.value || clientNameInput.value.trim() === '')) {
            clientNameInput.value = opt.text.split('(')[0].trim();
        }
        const projInput = document.getElementById('project_name');
        if (projInput && (!projInput.value || projInput.value.trim() === '')) {
            projInput.value = opt.text.split('(')[0].trim() + ' Project';
        }
        const locInput = document.getElementById('project_location');
        if (locInput && (!locInput.value || locInput.value.trim() === '') && opt.dataset.address) {
            locInput.value = opt.dataset.address;
        }
        const contactInfo = document.getElementById('customer-contact-info');
        if (contactInfo) {
            const phone = opt.dataset.phone || 'No Phone';
            const email = opt.dataset.email || 'No Email';
            contactInfo.innerHTML = `<span class="badge bg-light text-dark border me-1"><i class="bi bi-telephone me-1"></i>${phone}</span> <span class="badge bg-light text-dark border"><i class="bi bi-envelope me-1"></i>${email}</span>`;
        }
    }

    function startStudio() {
        const cust = document.getElementById('customer_id');
        const studio = document.getElementById('studio-panel');
        if (studio) studio.classList.remove('d-none');

        if (!cust.value) {
            // Keep setup visible so user can select customer without blocking studio
            return;
        }
        
        // Update summary text
        const custName = cust.options[cust.selectedIndex].text;
        const proj = document.getElementById('project_name').value;
        const summary = document.getElementById('setup-summary');
        if (summary) {
            summary.innerText = `${custName} | ${proj}`;
            summary.classList.remove('d-none');
        }
        
        toggleSetup();
    }

    // ----- PRODUCT & DESIGN MODAL -----
    function openConfigModal(id, name, isEdit = false) {
        selectedProductId = id;
        selectedProductName = name;
        document.getElementById('modal-product-name').innerText = name;
        
        const btnAddToQuote = document.getElementById('btn-add-to-quote');
        if (btnAddToQuote) {
            if (isEdit) {
                btnAddToQuote.innerHTML = 'Update Item in Quote <i class="bi bi-cart-check ms-1"></i>';
            } else {
                btnAddToQuote.innerHTML = 'Add to Quote <i class="bi bi-cart-plus ms-1"></i>';
            }
        }

        // Reset state if not editing
        if(!isEdit) {
            selectedDesign = null;
            document.getElementById('svg-container').innerHTML = '<div class="text-muted small"><i class="bi bi-hourglass-split"></i> Loading...</div>';
            stagedSizes = [];
            editingCartIdx = null;
            
            // Reset unit to mm
            const unitSelect = document.getElementById('cfg-unit');
            if (unitSelect) {
                unitSelect.value = 'mm';
                document.getElementById('lbl-cfg-width').innerText = 'Width (mm)';
                document.getElementById('lbl-cfg-height').innerText = 'Height (mm)';
                document.getElementById('cfg-width').value = 1200;
                document.getElementById('cfg-height').value = 1500;
                document.getElementById('cfg-width').dataset.prevUnit = 'mm';
            }

            const prod = productsMap[id] || {};
            // Reset Spec dropdowns to product defaults
            document.getElementById('cfg-glass').value = prod.glass_type ? (Array.from(document.getElementById('cfg-glass').options).some(o => o.value === prod.glass_type) ? prod.glass_type : "") : "";
            document.getElementById('cfg-mesh').value = (prod.mesh_type && prod.mesh_type !== 'No') ? (Array.from(document.getElementById('cfg-mesh').options).some(o => o.value === prod.mesh_type) ? prod.mesh_type : "") : "";
            document.getElementById('cfg-profile-color').value = "";
            document.getElementById('cfg-hardware-color').value = "";
            if (document.getElementById('cfg-rate-sqft')) document.getElementById('cfg-rate-sqft').value = "";
            
            renderStagedSizes();
        }
        
        configModalInstance.show();
        loadDesigns(id, isEdit);
    }

    function loadDesigns(productId, isEdit) {
        const list = document.getElementById('design-list');
        list.innerHTML = `<div class="p-2 text-muted small"><span class="spinner-border spinner-border-sm"></span> Loading...</div>`;

        fetch(`/products/${productId}/designs`)
            .then(res => res.json())
            .then(designs => {
                if(!designs.length) {
                    list.innerHTML = `<div class="p-2 text-muted small">No designs available.</div>`;
                    return;
                }
                
                let html = '';
                designs.forEach(d => {
                    const cleanName = d.name.replace(/'/g, "\\'");
                    let imgUrl = '';
                    // Prioritize vector SVG preview: instant in-memory rendering, zero network lag, 100% crisp
                    if (d.svg_template) {
                        const rawTemplate = d.svg_template
                            .replace(/\{\{VB_X\}\}/g, '-60')
                            .replace(/\{\{VB_Y\}\}/g, '-60')
                            .replace(/\{\{VB_WIDTH\}\}/g, '1120')
                            .replace(/\{\{VB_HEIGHT\}\}/g, '1120')
                            .replace(/\{\{WIDTH\}\}/g, '1000')
                            .replace(/\{\{HEIGHT\}\}/g, '1000')
                            .replace(/\{\{INNER_WIDTH\}\}/g, '920')
                            .replace(/\{\{INNER_HEIGHT\}\}/g, '920')
                            .replace(/\{\{CENTER_X\}\}/g, '500')
                            .replace(/\{\{CENTER_Y\}\}/g, '500')
                            .replace(/\{\{PANEL_WIDTH\}\}/g, '470')
                            .replace(/\{\{PANEL_2_X\}\}/g, '490')
                            .replace(/\{\{SLIDE_ARROW_END\}\}/g, '350');
                        imgUrl = 'data:image/svg+xml;utf8,' + encodeURIComponent(rawTemplate);
                    } else if (d.preview_image) {
                        imgUrl = `/${d.preview_image}`;
                    }
                    html += `
                    <div class="card border border-2 shadow-sm cursor-pointer hover-lift design-card flex-shrink-0" 
                         id="design-card-${d.id}" 
                         style="width: 100px; transition: all 0.2s ease;"
                         onclick="selectDesign(${d.id}, '${cleanName}')">
                        <img src="${imgUrl}" class="card-img-top" style="height: 80px; object-fit: contain; padding: 4px;" alt="${d.name}" loading="lazy" decoding="async">
                        <div class="card-body p-1 text-center bg-white rounded-bottom border-top">
                            <small class="fw-bold d-block text-truncate text-dark" style="font-size: 0.65rem;" title="${d.name}">${d.name}</small>
                        </div>
                    </div>`;
                });
                list.innerHTML = html;
                window.loadedDesigns = designs; // Cache
                
                // Select design
                if(!isEdit && designs.length > 0) {
                    selectDesign(designs[0].id, designs[0].name.replace(/'/g, "\\'"));
                }
            });
    }

    function selectDesign(id, name) {
        const design = window.loadedDesigns.find(d => d.id === id);
        if(!design) return;
        
        selectedDesign = design;
        
        // Update UI
        document.querySelectorAll('.design-card').forEach(el => {
            el.classList.remove('border-primary', 'bg-light-primary');
            el.classList.add('border');
        });
        const activeCard = document.getElementById(`design-card-${id}`);
        if(activeCard) {
            activeCard.classList.add('border-primary', 'bg-light-primary');
            activeCard.classList.remove('border');
        }
        
        // Render SVG
        configurator.setTemplate(design.svg_template || '<svg></svg>');
        updateConfigurator();
        
        // Focus width
        document.getElementById('cfg-width').focus();
    }

    // ----- CONFIGURATOR & UNIT HANDLING -----
    function onUnitChange(newUnit) {
        document.getElementById('lbl-cfg-width').innerText = `Width (${newUnit})`;
        document.getElementById('lbl-cfg-height').innerText = `Height (${newUnit})`;

        const wInput = document.getElementById('cfg-width');
        const hInput = document.getElementById('cfg-height');
        const curW = parseFloat(wInput.value);
        const curH = parseFloat(hInput.value);

        if (curW && curH) {
            const prevUnit = wInput.dataset.prevUnit || 'mm';
            if (prevUnit !== newUnit) {
                const toMm = { 'mm': 1, 'cm': 10, 'inch': 25.4, 'in': 25.4, 'ft': 304.8, 'm': 1000 };
                const wMm = curW * (toMm[prevUnit] || 1);
                const hMm = curH * (toMm[prevUnit] || 1);
                const fromMm = toMm[newUnit] || 1;
                wInput.value = parseFloat((wMm / fromMm).toFixed(2));
                hInput.value = parseFloat((hMm / fromMm).toFixed(2));
            }
        }
        wInput.dataset.prevUnit = newUnit;
        updateConfigurator();
    }

    function updateConfigurator() {
        const w = document.getElementById('cfg-width').value;
        const h = document.getElementById('cfg-height').value;
        const u = document.getElementById('cfg-unit') ? document.getElementById('cfg-unit').value : 'mm';
        if(w && h && configurator) {
            configurator.setDimensions(w, h, u);
        }
    }

    function handleQtyEnter(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            queueSize();
            document.getElementById('cfg-width').focus();
        }
    }

    function queueSize() {
        const w = parseFloat(document.getElementById('cfg-width').value);
        const h = parseFloat(document.getElementById('cfg-height').value);
        const u = document.getElementById('cfg-unit') ? document.getElementById('cfg-unit').value : 'mm';
        const qty = parseInt(document.getElementById('cfg-qty').value);
        
        if (!w || !h || !qty) return alert("Enter valid dimensions and quantity.");

        // Calculate approximate Sq.Ft. for instant UI feedback
        let areaSqFt = 0;
        if (u === 'inch') {
            areaSqFt = (w * h) / 144.0;
        } else if (u === 'ft') {
            areaSqFt = w * h;
        } else if (u === 'cm') {
            areaSqFt = (w * h) / 929.0304;
        } else {
            areaSqFt = (w * h) / 92903.04;
        }
        
        stagedSizes.push({ width: w, height: h, unit: u, qty: qty, areaSqFt: parseFloat(areaSqFt.toFixed(3)), id: Date.now() });
        renderStagedSizes();
        
        document.getElementById('cfg-qty').value = 1;
    }

    function removeStagedSize(id) {
        stagedSizes = stagedSizes.filter(s => s.id !== id);
        renderStagedSizes();
    }

    function renderStagedSizes() {
        const tbody = document.getElementById('staged-sizes-body');
        if (stagedSizes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-muted py-2 small">No sizes queued.</td></tr>';
            return;
        }
        let html = '';
        stagedSizes.forEach(s => {
            const unit = s.unit || 'mm';
            html += `<tr>
                <td class="fw-semibold text-dark">${s.width}</td>
                <td class="fw-semibold text-dark">${s.height}</td>
                <td><span class="badge bg-light text-primary border">${unit}</span></td>
                <td class="fw-semibold text-dark">${s.qty}</td>
                <td class="small text-muted">${s.areaSqFt ? (s.areaSqFt * s.qty).toFixed(2) : '-'}</td>
                <td><button type="button" class="btn btn-sm text-danger py-0 px-1" onclick="removeStagedSize(${s.id})"><i class="bi bi-x"></i></button></td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    // ----- CART -----
    function addToCart() {
        if (!selectedDesign) return alert("Please wait for design to load.");

        // If nothing queued, auto-queue the current inputs
        if (stagedSizes.length === 0) {
            const w = parseFloat(document.getElementById('cfg-width').value);
            const h = parseFloat(document.getElementById('cfg-height').value);
            const u = document.getElementById('cfg-unit') ? document.getElementById('cfg-unit').value : 'mm';
            const qty = parseInt(document.getElementById('cfg-qty').value);
            if (w && h && qty) {
                let areaSqFt = (u === 'inch') ? (w * h) / 144.0 : ((u === 'ft') ? (w * h) : ((u === 'cm') ? (w * h) / 929.0304 : (w * h) / 92903.04));
                stagedSizes.push({ width: w, height: h, unit: u, qty: qty, areaSqFt: parseFloat(areaSqFt.toFixed(3)), id: Date.now() });
            } else {
                return alert("Queue at least one size.");
            }
        }
        
        const rawSvg = document.getElementById('svg-container').innerHTML;
        const isExistingItem = editingCartIdx !== null && cart[editingCartIdx] && cart[editingCartIdx].is_existing;
        const existingId = isExistingItem ? cart[editingCartIdx].id : null;

        const itemSizes = stagedSizes.map(s => ({
            id: (s.is_existing && s.id) ? s.id : null,
            is_existing: !!(s.is_existing && s.id),
            width: s.width,
            height: s.height,
            quantity: s.qty,
            unit: s.unit || 'mm'
        }));
        
        const totalQty = stagedSizes.reduce((acc, s) => acc + s.qty, 0);
        const primaryUnit = itemSizes[0]?.unit || 'mm';

        const prod = productsMap[selectedProductId] || {};
        const cartItem = {
            id: existingId,
            is_existing: isExistingItem,
            product_id: selectedProductId,
            product_name: selectedProductName,
            profile_brand: prod.profile_brand || '',
            profile_series: prod.profile_series || '',
            profile_system: prod.profile_series || prod.category || 'Casement Series',
            design_id: selectedDesign.id,
            design_name: selectedDesign.name,
            sizes: itemSizes,
            unit: primaryUnit,
            total_qty: totalQty,
            svg: rawSvg,
            // Specifications
            glass_type: document.getElementById('cfg-glass').value || prod.glass_type || '',
            glass_thickness: prod.glass_thickness || '',
            mesh_type: document.getElementById('cfg-mesh').value || prod.mesh_type || 'No',
            profile_color: document.getElementById('cfg-profile-color').value || 'Standard White',
            hardware_color: document.getElementById('cfg-hardware-color').value || 'White',
            hardware_brand: prod.hardware_brand || '',
            profile_details: prod.profile_details || {},
            accessories_details: prod.accessories_details || [],
            rate_per_sqft: (document.getElementById('cfg-rate-sqft') && document.getElementById('cfg-rate-sqft').value) ? parseFloat(document.getElementById('cfg-rate-sqft').value) : (item ? (item.rate_per_sqft || item.value_per_sqft || null) : null)
        };

        if (editingCartIdx !== null) {
            cart[editingCartIdx] = cartItem; // Replace edited item
        } else {
            cart.push(cartItem); // Add new item
        }
        
        // Clean up and close modal
        stagedSizes = [];
        renderStagedSizes();
        renderCartUI();
        configModalInstance.hide();
    }

    function duplicateCartItem(idx) {
        const item = cart[idx];
        const copy = JSON.parse(JSON.stringify(item));
        copy.id = null;
        copy.is_existing = false;
        if (copy.sizes) {
            copy.sizes.forEach(s => {
                s.id = null;
                s.is_existing = false;
            });
        }
        cart.splice(idx + 1, 0, copy);
        renderCartUI();
    }

    function removeCartItem(idx) {
        if(confirm("Remove this item?")) {
            cart.splice(idx, 1);
            renderCartUI();
        }
    }

    function editCartItem(idx) {
        const item = cart[idx];
        editingCartIdx = idx;
        
        // Open Modal
        openConfigModal(item.product_id, item.product_name, true);
        
        // Restore unit
        const itemUnit = item.unit || (item.sizes && item.sizes[0] ? item.sizes[0].unit : 'mm');
        const unitSelect = document.getElementById('cfg-unit');
        if (unitSelect) {
            unitSelect.value = itemUnit;
            onUnitChange(itemUnit);
        }

        // Populate Specs
        document.getElementById('cfg-glass').value = item.glass_type || '';
        document.getElementById('cfg-mesh').value = item.mesh_type || '';
        document.getElementById('cfg-profile-color').value = item.profile_color || '';
        document.getElementById('cfg-hardware-color').value = item.hardware_color || '';
        if (document.getElementById('cfg-rate-sqft')) {
            document.getElementById('cfg-rate-sqft').value = item.rate_per_sqft || item.value_per_sqft || '';
        }

        // Wait for designs to load, then select the design
        let attempts = 0;
        const checkDesign = setInterval(() => {
            if (window.loadedDesigns && window.loadedDesigns.length > 0) {
                clearInterval(checkDesign);
                selectDesign(item.design_id, item.design_name);
                
                // Load sizes with unit and areaSqFt
                stagedSizes = item.sizes.map(s => {
                    const u = s.unit || itemUnit || 'mm';
                    let areaSqFt = 0;
                    if (u === 'inch') areaSqFt = (s.width * s.height) / 144.0;
                    else if (u === 'ft') areaSqFt = s.width * s.height;
                    else if (u === 'cm') areaSqFt = (s.width * s.height) / 929.0304;
                    else areaSqFt = (s.width * s.height) / 92903.04;
                    return {
                        id: s.id || null,
                        is_existing: !!(s.is_existing && s.id),
                        width: s.width,
                        height: s.height,
                        unit: u,
                        qty: s.quantity || s.qty || 1,
                        areaSqFt: parseFloat(areaSqFt.toFixed(3)),
                    };
                });
                renderStagedSizes();
                
                // Update the configurator with the first size
                if (stagedSizes.length > 0) {
                    document.getElementById('cfg-width').value = stagedSizes[0].width;
                    document.getElementById('cfg-height').value = stagedSizes[0].height;
                    updateConfigurator();
                }
            }
            attempts++;
            if(attempts > 20) clearInterval(checkDesign); // timeout after 2s
        }, 100);
    }

    function renderCartUI() {
        const container = document.getElementById('cart-items-container');
        if (cart.length === 0) {
            container.innerHTML = `
            <div class="text-center text-muted p-5 mt-5">
                <i class="bi bi-cart-x fs-1 opacity-50 mb-3 d-block"></i>
                <p class="mb-0 small">Quote is empty.</p>
            </div>`;
            document.getElementById('cart-total').innerText = '₹ 0.00';
            return;
        }
        
        let html = '';
        cart.forEach((item, idx) => {
            let sizesList = item.sizes.map(s => `<span class="badge bg-light text-dark border me-1 mb-1">${s.width}x${s.height} ${s.unit || 'mm'} [Qty ${s.quantity || s.qty}]</span>`).join('');
            
            // Build Specs String
            let specs = [];
            if(item.profile_brand) specs.push(item.profile_brand);
            if(item.profile_series) specs.push(item.profile_series);
            if(item.glass_type) specs.push(item.glass_type);
            if(item.mesh_type && item.mesh_type !== 'No') specs.push(item.mesh_type);
            if(item.profile_color) specs.push(item.profile_color);
            if(item.rate_per_sqft) specs.push(`Custom Rate: ₹${item.rate_per_sqft}/sqft`);
            let specsHtml = specs.length > 0 ? `<div class="small text-muted mb-2"><i class="bi bi-gear-fill me-1"></i> ${specs.join(' | ')}</div>` : '';

            html += `
            <div class="cart-item position-relative">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">Item ${idx+1}: ${item.product_name}</h6>
                        <small class="text-muted" style="font-size: 0.75rem;">${item.design_name}</small>
                    </div>
                    <div class="cart-item-actions bg-white shadow-sm rounded-pill px-1 py-0 border position-absolute top-0 end-0 mt-2 me-2">
                        <button class="btn btn-sm text-primary py-0 px-1" onclick="editCartItem(${idx})" title="Edit"><i class="bi bi-pencil" style="font-size: 0.75rem;"></i></button>
                        <button class="btn btn-sm text-secondary py-0 px-1" onclick="duplicateCartItem(${idx})" title="Duplicate"><i class="bi bi-files" style="font-size: 0.75rem;"></i></button>
                        <button class="btn btn-sm text-danger py-0 px-1" onclick="removeCartItem(${idx})" title="Remove"><i class="bi bi-trash" style="font-size: 0.75rem;"></i></button>
                    </div>
                </div>
                ${specsHtml}
                <div class="mb-2">${sizesList}</div>
                <div class="d-flex justify-content-between align-items-center bg-white rounded p-2 border shadow-sm mt-2">
                    <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Qty: ${item.total_qty}</small>
                    <span class="fw-bold font-monospace text-primary small" id="cart-item-total-${idx}">Calculating...</span>
                </div>
            </div>`;
        });
        
        container.innerHTML = html;
        debouncedPreviewRequest();
    }

    // ----- AUTHORITATIVE PREVIEW -----
    let previewAbortController = null;

    function debouncedPreviewRequest() {
        clearTimeout(previewDebounceTimer);
        previewDebounceTimer = setTimeout(fetchPreview, 300);
    }

    function fetchPreview() {
        if(cart.length === 0) return;
        
        if (previewAbortController) {
            previewAbortController.abort();
        }
        previewAbortController = new AbortController();

        const payload = {
            project_name: document.getElementById('project_name').value || 'Draft',
            client_name: document.getElementById('client_name').value || 'Draft',
            date: document.getElementById('date').value,
            quote_no: document.getElementById('quote_no').value,
            items: cart.map((c, i) => {
                const primaryUnit = c.unit || (c.sizes[0] ? c.sizes[0].unit : 'mm');
                return {
                    position: `Item ${i+1}`,
                    product_id: c.product_id,
                    product_name: c.product_name,
                    design_id: c.design_id,
                    design_name: c.design_name,
                    qty: 1, 
                    dimension_w: c.sizes[0].width,
                    dimension_h: c.sizes[0].height,
                    sizes: c.sizes,
                    unit: primaryUnit,
                    profile_brand: c.profile_brand,
                    profile_series: c.profile_series,
                    profile_system: c.profile_system,
                    glass_type: c.glass_type,
                    glass_thickness: c.glass_thickness,
                    mesh_type: c.mesh_type,
                    profile_color: c.profile_color,
                    hardware_color: c.hardware_color,
                    hardware_brand: c.hardware_brand,
                    profile_details: c.profile_details,
                    accessories_details: c.accessories_details,
                    rate_per_sqft: c.rate_per_sqft || null
                };
            })
        };

        fetch('/quotations/calculate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify(payload),
            signal: previewAbortController.signal
        })
        .then(res => {
            if(!res.ok) throw new Error("Calculation failed on server");
            return res.json();
        })
        .then(data => {
            if (data.has_zero_cost_materials) {
                document.getElementById('zero-cost-warning').classList.remove('d-none');
            } else {
                document.getElementById('zero-cost-warning').classList.add('d-none');
            }

            const subtotal = data.quotation_data ? (data.quotation_data.subtotal || 0) : 0;
            const grandTotal = data.quotation_data ? (data.quotation_data.grand_total || 0) : 0;
            const elSub = document.getElementById('cart-subtotal');
            if (elSub) elSub.innerText = '₹ ' + parseFloat(subtotal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('cart-total').innerText = '₹ ' + parseFloat(grandTotal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            data.items.forEach((calcItem, idx) => {
                const el = document.getElementById(`cart-item-total-${idx}`);
                if (el) {
                    const lineTotal = calcItem.line_total || calcItem.amount || 0;
                    el.innerText = '₹ ' + parseFloat(lineTotal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            });
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            console.error("Preview Calculation Error:", err);
            cart.forEach((_, idx) => {
                const el = document.getElementById(`cart-item-total-${idx}`);
                if (el) el.innerText = 'Error';
            });
            document.getElementById('cart-total').innerText = 'Error';
        });
    }

    function saveQuotation() {
        if(cart.length === 0) return alert("Quote is empty!");
        
        const cust = document.getElementById('customer_id');
        if(!cust.value) {
            alert("Please select a customer.");
            const body = document.getElementById('setup-body');
            if (body && body.classList.contains('d-none')) {
                toggleSetup();
            }
            cust.focus();
            return;
        }

        const locVal = document.getElementById('project_location') ? document.getElementById('project_location').value : '';
        const payload = {
            customer_id: cust.value,
            project_name: document.getElementById('project_name').value,
            client_name: document.getElementById('client_name').value,
            project_location: locVal,
            address: locVal,
            date: document.getElementById('date').value,
            quote_no: document.getElementById('quote_no').value,
            status: 'Draft',
            items: cart.map((c, i) => {
                const cleanSizes = (c.sizes && c.sizes.length > 0) ? c.sizes.map(s => ({
                    id: (s.is_existing && s.id) ? s.id : null,
                    width: parseFloat(s.width) || 1000,
                    height: parseFloat(s.height) || 1000,
                    unit: s.unit || c.unit || 'mm',
                    qty: parseInt(s.quantity || s.qty) || 1
                })) : [{
                    id: null,
                    width: 1000,
                    height: 1000,
                    unit: c.unit || 'mm',
                    qty: c.total_qty || 1
                }];
                const primaryUnit = c.unit || cleanSizes[0].unit || 'mm';
                return {
                    id: (c.is_existing && c.id) ? c.id : null,
                    position: `Item ${i+1}`,
                    product_id: c.product_id,
                    product_name: c.product_name,
                    design_id: c.design_id,
                    design_name: c.design_name,
                    qty: c.total_qty || cleanSizes.reduce((sum, s) => sum + s.qty, 0),
                    dimension_w: cleanSizes[0].width,
                    dimension_h: cleanSizes[0].height,
                    sizes: cleanSizes,
                    unit: primaryUnit,
                    drawing_b64: c.drawing_b64 || null,
                    profile_brand: c.profile_brand || '',
                    profile_series: c.profile_series || '',
                    profile_system: c.profile_system || '',
                    glass_type: c.glass_type || '',
                    glass_thickness: c.glass_thickness || '',
                    mesh_type: c.mesh_type || '',
                    profile_color: c.profile_color || '',
                    hardware_color: c.hardware_color || '',
                    hardware_brand: c.hardware_brand || '',
                    profile_details: c.profile_details || {},
                    accessories_details: c.accessories_details || [],
                    rate_per_sqft: c.rate_per_sqft || null
                };
            })
        };

        const btn = document.getElementById('btn-save');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving & Generating PDF...';
        btn.disabled = true;

        fetch(`/quotations/{{ $quotation->id }}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            if(res.redirected) {
                window.location.href = res.url;
                return;
            }
            const data = await res.json().catch(() => null);
            if(!res.ok) {
                let errorMsg = "Error saving quotation";
                if(data) {
                    if(data.message) errorMsg = data.message;
                    if(data.errors) {
                        const errLines = Object.values(data.errors).flat().join('\n');
                        if(errLines) errorMsg += ':\n' + errLines;
                    }
                }
                throw new Error(errorMsg);
            }
            return data;
        })
        .then(data => {
            window.location.href = '/quotations/{{ $quotation->id }}';
        })
        .catch(err => {
            console.error("Save error:", err);
            alert(err.message || "Error saving quotation");
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
</script>
@endsection
