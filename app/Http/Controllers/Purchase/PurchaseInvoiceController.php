<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Purchase\PurchaseInvoice;
use App\Models\Purchase\PurchaseInvoiceItem;
use App\Models\Purchase\GoodsReceipt;
use App\Models\Supplier;
use App\Models\Inventory\Item as InventoryItem;
use App\Models\Assets\Asset;
use App\Models\Assets\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\BankAccount;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\GlTransaction;
use App\Models\SystemSetting;
use App\Models\ChartAccount;
use App\Helpers\HashIdHelper;
use App\Services\FxTransactionRateService;
use App\Traits\GetsCurrenciesFromFxRates;
use Yajra\DataTables\Facades\DataTables;
use App\Mail\PurchaseInvoiceMail;
use App\Jobs\ProcessPurchaseInvoiceItemsJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseInvoiceController extends Controller
{
    use GetsCurrenciesFromFxRates;
    
    private function refreshInvoiceStatus(PurchaseInvoice $invoice): void
    {
        $paid = (float) Payment::where('reference_type', 'purchase_invoice')
            ->where('reference_number', $invoice->invoice_number)
            ->sum('amount');
        $status = $paid >= (float) $invoice->total_amount ? 'closed' : 'open';
        if ($invoice->status !== $status) {
            $invoice->update(['status' => $status]);
        }
        
        // Sync linked opening balance if this invoice is linked to one
        $invoice->syncLinkedOpeningBalance();
    }

    public function index()
    {
        $user = Auth::user();
        $branchId = session('branch_id') ?? $user->branch_id;

        $query = PurchaseInvoice::with(['supplier'])
            ->where('company_id', $user->company_id)
            // Scope to current branch (session branch takes precedence over user branch)
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->when(request('supplier'), function ($q, $supplier) {
                $q->whereHas('supplier', function ($sq) use ($supplier) {
                    $sq->where('name', 'like', '%' . $supplier . '%');
                });
            })
            ->when(request('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when(request('date_from'), function ($q, $from) {
                $q->whereDate('invoice_date', '>=', $from);
            })
            ->when(request('date_to'), function ($q, $to) {
                $q->whereDate('invoice_date', '<=', $to);
            });

        // Stats
        $totalInvoices = (clone $query)->count();
        $totalAmount = (clone $query)->sum('total_amount');
        $totalPaid = Payment::where('reference_type', 'purchase_invoice')
            ->when($branchId, fn($q)=>$q->where('branch_id', $branchId))
            ->sum('amount');
        $totalOutstanding = max(0, (float) $totalAmount - (float) $totalPaid);

        if (request()->ajax()) {
            if (request('stats_only')) {
                return response()->json([
                    'stats' => [
                        'total_invoices' => $totalInvoices,
                        'total_amount' => (float) $totalAmount,
                        'total_paid' => (float) $totalPaid,
                        'total_outstanding' => (float) $totalOutstanding,
                    ]
                ]);
            }

            $invoices = $query->select(['id','invoice_number','supplier_id','invoice_date','due_date','status','total_amount']);

            return DataTables::of($invoices)
                ->addColumn('supplier_name', function ($inv) {
                    return e(optional($inv->supplier)->name ?? 'N/A');
                })
                ->addColumn('formatted_date', function ($inv) {
                    return format_date($inv->invoice_date);
                })
                ->addColumn('formatted_due_date', function ($inv) {
                    return format_date($inv->due_date);
                })
                ->addColumn('status_badge', function ($inv) {
                    $cls = ($inv->status === 'open') ? 'primary' : 'secondary';
                    return '<span class="badge bg-' . $cls . '">' . strtoupper($inv->status) . '</span>';
                })
                ->addColumn('formatted_total', function ($inv) {
                    return 'TZS ' . number_format((float) $inv->total_amount, 2);
                })
                ->addColumn('formatted_paid', function ($inv) {
                    if (method_exists($inv, 'getTotalPaidAttribute')) {
                        $paid = (float) $inv->total_paid;
                    } else {
                        $paid = (float) \App\Models\Payment::where('reference_type','purchase_invoice')
                            ->where('reference_number', $inv->invoice_number)
                            ->sum('amount');
                    }
                    return 'TZS ' . number_format($paid, 2);
                })
                ->addColumn('formatted_outstanding', function ($inv) {
                    if (method_exists($inv, 'getTotalPaidAttribute')) {
                        $paid = (float) $inv->total_paid;
                    } else {
                        $paid = (float) \App\Models\Payment::where('reference_type','purchase_invoice')
                            ->where('reference_number', $inv->invoice_number)
                            ->sum('amount');
                    }
                    $outstanding = max(0, (float) $inv->total_amount - $paid);
                    return 'TZS ' . number_format($outstanding, 2);
                })
                ->addColumn('actions', function ($inv) {
                    $encoded = method_exists($inv, 'getEncodedIdAttribute') ? $inv->encoded_id : \Vinkla\Hashids\Facades\Hashids::encode($inv->id);
                    $paid = (float) \App\Models\Payment::where('reference_type','purchase_invoice')
                        ->where('reference_number', $inv->invoice_number)
                        ->sum('amount');
                    $outstanding = max(0, (float) $inv->total_amount - $paid);
                    $disabled = $outstanding <= 0 ? ' disabled' : '';
                    $delDisabled = $outstanding <= 0 ? ' disabled' : '';
                    return '<div class="btn-group" role="group">'
                        . '<a href="' . route('purchases.purchase-invoices.show', $encoded) . '" class="btn btn-sm btn-outline-info" title="View"><i class="bx bx-show"></i></a> '
                        . '<a href="' . route('purchases.purchase-invoices.edit', $encoded) . '" class="btn btn-sm btn-outline-primary' . $disabled . '" title="Edit"><i class="bx bx-edit"></i></a> '
                        . '<button type="button" class="btn btn-sm btn-outline-danger' . $delDisabled . '" title="Delete" onclick="deleteInvoice(\'' . $encoded . '\')"><i class="bx bx-trash"></i></button>'
                        . '</div>';
                })
                ->rawColumns(['status_badge','actions'])
                ->order(function ($q) {
                    $q->orderByDesc('invoice_date');
                })
                ->toJson();
        }

        $invoices = $query->orderByDesc('invoice_date')->paginate(20);
        return view('purchases.purchase-invoices.index', compact('invoices', 'totalInvoices', 'totalAmount', 'totalPaid', 'totalOutstanding'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        // Show all suppliers for the company (no branch filter), so invoices can be raised to any supplier
        $suppliers = Supplier::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();
        $items = InventoryItem::where('company_id', $user->company_id)->orderBy('name')->get();
        
        // Load assets and asset categories for asset purchases
        // Show only assets that:
        // - belong to this company
        // - do NOT have an opening balance
        // - have NOT been purchased before
        // Note: SoftDeletes will automatically exclude deleted assets
        $openedAssetIds = \App\Models\Assets\AssetOpening::where('company_id', $user->company_id)
            ->whereNotNull('asset_id')
            ->pluck('asset_id');
        $purchasedAssetIds = \App\Models\Purchase\PurchaseInvoiceItem::whereNotNull('asset_id')
            ->whereHas('invoice', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->pluck('asset_id');

        $branchId = session('branch_id') ?? $user->branch_id ?? null;
        $assets = Asset::where('company_id', $user->company_id)
            ->when($branchId, function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->whereNotIn('id', $openedAssetIds)
            ->whereNotIn('id', $purchasedAssetIds)
            ->select('id', 'name', 'code', 'purchase_cost', 'branch_id')
            ->orderBy('name')
            ->get();
        
        // Load asset categories - show all company categories
        $assetCategories = AssetCategory::where('company_id', $user->company_id)
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();
        
        // Debug: Log asset count (remove in production if needed)
        \Log::info('Purchase Invoice Create: Assets loaded', [
            'company_id' => $user->company_id,
            'assets_count' => $assets->count(),
            'categories_count' => $assetCategories->count()
        ]);
        
        $prefill = null;
        if ($request->filled('grn_id')) {
            $grnIdParam = $request->grn_id;
            // Support both numeric ID and HashId
            if (!is_numeric($grnIdParam)) {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($grnIdParam)[0] ?? null;
                $grnLookupId = $decoded;
            } else {
                $grnLookupId = (int) $grnIdParam;
            }

            $grn = GoodsReceipt::with(['items.inventoryItem', 'purchaseOrder.supplier'])->find($grnLookupId);
            if ($grn) {
                // Check if GRN has already been converted to an invoice
                $grnItemIds = $grn->items->pluck('id')->toArray();
                $alreadyConverted = \App\Models\Purchase\PurchaseInvoiceItem::whereIn('grn_item_id', $grnItemIds)->exists();
                if ($alreadyConverted) {
                    return redirect()->back()->with('error', 'This GRN has already been converted to an invoice.');
                }
                
                $qcStatus = $grn->quality_check_status ?? 'pending';
                $canInvoice = false;
                if ($qcStatus === 'passed') {
                    $canInvoice = true;
                } elseif ($qcStatus === 'partial') {
                    // Allow if at least one item with accepted quantity is passed
                    $itemsWithAcceptedAndPassed = $grn->items->filter(function($it) {
                        return ($it->accepted_quantity ?? 0) > 0 && ($it->item_qc_status ?? 'pending') === 'passed';
                    });
                    $canInvoice = $itemsWithAcceptedAndPassed->count() > 0;
                }
                if (!$canInvoice) {
                    return redirect()->back()->with('error', 'Only GRNs with Quality Check PASSED (or at least one accepted item PASSED if partial) can be invoiced. Current QC: ' . strtoupper($qcStatus));
                }
                // For partial GRNs, filter items to only include those with accepted qty > 0 and item_qc_status = 'passed'
                if ($qcStatus === 'partial') {
                    $grn->setRelation('items', $grn->items->filter(function($it) {
                        return ($it->accepted_quantity ?? 0) > 0 && ($it->item_qc_status ?? 'pending') === 'passed';
                    })->map(function($it) {
                        // Override quantity_received with accepted_quantity for invoice
                        $it->quantity_received = $it->accepted_quantity;
                        return $it;
                    }));
                }
                $prefill = $grn;
            }
        }
        $suggestedInvoiceNumber = $this->generateInvoiceNumber();
        
        // Get currencies from FX RATES MANAGEMENT
        $currencies = $this->getCurrenciesFromFxRates();
        
        return view('purchases.purchase-invoices.create', compact('suppliers','items','assets','assetCategories','prefill','suggestedInvoiceNumber','currencies'));
    }

    public function store(Request $request)
    {
        \Log::info('Purchase Invoice Store: Starting', [
            'user_id' => auth()->id(),
            'company_id' => auth()->user()->company_id,
            'branch_id' => auth()->user()->branch_id,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:100|unique:purchase_invoices,invoice_number',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'currency' => 'nullable|string|max:3',
            'exchange_rate' => 'nullable|numeric|min:0.000001',
            'discount_amount' => 'nullable|numeric|min:0',
            'withholding_tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'nullable|in:inventory,asset',
            'items.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
            'items.*.asset_id' => 'nullable|exists:assets,id',
            'items.*.item_name' => 'nullable|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.vat_type' => 'required|in:no_vat,inclusive,exclusive',
            'items.*.vat_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.batch_number' => 'nullable|string|max:100',
        ]);

        \Log::info('Purchase Invoice Store: Validation passed', [
            'invoice_number' => $request->invoice_number,
            'supplier_id' => $request->supplier_id,
            'items_count' => count($request->items)
        ]);

        DB::beginTransaction();
        try {
            $branchId = session('branch_id') ?? (Auth::user()->branch_id ?? null);
            if (!$branchId) {
                \Log::warning('Purchase Invoice Store: Missing branch context', [
                    'user_id' => auth()->id(),
                ]);
                return back()->withInput()->withErrors(['error' => 'Please select a branch before creating a purchase invoice.']);
            }
            \Log::info('Purchase Invoice Store: Creating invoice record');
            
            // Get functional currency
            $functionalCurrency = SystemSetting::getValue('functional_currency', Auth::user()->company->functional_currency ?? 'TZS');
            $invoiceCurrency = $request->currency ?? $functionalCurrency;
            $companyId = Auth::user()->company_id;

            // Get exchange rate using FxTransactionRateService
            $fxTransactionRateService = app(FxTransactionRateService::class);
            $userProvidedRate = $request->filled('exchange_rate') ? (float) $request->exchange_rate : null;
            $rateResult = $fxTransactionRateService->getTransactionRate(
                $invoiceCurrency,
                $functionalCurrency,
                $request->invoice_date,
                $companyId,
                $userProvidedRate
            );
            $exchangeRate = $rateResult['rate'];
            $fxRateUsed = $exchangeRate; // Store the rate used for fx_rate_used field

            $invoice = PurchaseInvoice::create([
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'currency' => $invoiceCurrency,
                'exchange_rate' => $exchangeRate,
                'fx_rate_used' => $fxRateUsed,
                'notes' => $request->notes,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'created_by' => Auth::id(),
            ]);

            \Log::info('Purchase Invoice Store: Invoice created', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);

            $itemsCount = count($request->items);
            $discount = (float) ($request->input('discount_amount', 0) ?? 0);
            $withholdingTax = (float) ($request->input('withholding_tax_amount', 0) ?? 0);

            // Use job for large batches to avoid timeout and max_input_vars issues
            // Threshold can be adjusted: lower = more items processed synchronously, higher = more use async jobs
            $jobThreshold = config('queue.purchase_invoice_job_threshold', 50);
            $useJob = $itemsCount >= $jobThreshold;
            
            if ($useJob) {
                \Log::info('Purchase Invoice Store: Using job for large batch', [
                    'invoice_id' => $invoice->id,
                    'items_count' => $itemsCount
                ]);

                // Set initial status to 'processing' if you have that field, otherwise 'draft'
                $invoice->update([
                    'status' => 'draft', // Will be updated to 'open' by the job
                    'subtotal' => 0,
                    'vat_amount' => 0,
                    'discount_amount' => $discount,
                    'withholding_tax_amount' => $withholdingTax,
                    'total_amount' => 0,
                ]);

                // Dispatch job to process items asynchronously
                ProcessPurchaseInvoiceItemsJob::dispatch(
                    $invoice->id,
                    $request->items,
                    $discount,
                    $withholdingTax
                );

                \Log::info('Purchase Invoice Store: Job dispatched', [
                    'invoice_id' => $invoice->id
                ]);

                DB::commit();

                return redirect()->route('purchases.purchase-invoices.show', $invoice->encoded_id)
                    ->with('success', 'Purchase invoice created successfully. Items are being processed in the background. Please refresh the page in a few moments.');
            } else {
                // Process items synchronously for smaller batches
                \Log::info('Purchase Invoice Store: Processing items synchronously', [
                    'items_count' => $itemsCount
                ]);

                $subtotal = 0; $vatAmount = 0; $total = 0;
                foreach ($request->items as $line) {
                    $qty = (float) $line['quantity'];
                    $unit = (float) $line['unit_cost'];
                    $base = $qty * $unit;
                    $vat = 0;
                    $vatType = $line['vat_type'];
                    $rate = (float) ($line['vat_rate'] ?? 0);
                    if ($vatType === 'inclusive' && $rate > 0) {
                        $vat = $base * ($rate / (100 + $rate));
                    } elseif ($vatType === 'exclusive' && $rate > 0) {
                        $vat = $base * ($rate / 100);
                    }
                    $lineTotal = $vatType === 'exclusive' ? $base + $vat : $base;

                    // Determine item type
                    $itemType = $line['item_type'] ?? 'inventory';
                    if (!empty($line['asset_id'])) {
                        $itemType = 'asset';
                    } elseif (!empty($line['inventory_item_id'])) {
                        $itemType = 'inventory';
                    }

                    $assetId = null;
                    
                    // If this is an asset purchase, link to existing asset only
                    // Assets must be created separately before adding to purchase invoice
                    if ($itemType === 'asset') {
                        if (empty($line['asset_id'])) {
                            throw new \Exception('Asset ID is required. Assets must be created separately before adding to purchase invoice.');
                        }
                        $assetId = $line['asset_id'];
                        
                        // Verify asset exists and belongs to company
                        $asset = Asset::where('id', $assetId)
                            ->where('company_id', Auth::user()->company_id)
                            ->first();
                        
                        if (!$asset) {
                            throw new \Exception('Selected asset not found or does not belong to your company.');
                        }
                    }

                    PurchaseInvoiceItem::create([
                        'purchase_invoice_id' => $invoice->id,
                        'item_type' => $itemType,
                        'inventory_item_id' => $itemType === 'inventory' ? ($line['inventory_item_id'] ?? null) : null,
                        'asset_id' => $assetId,
                        'grn_item_id' => $line['grn_item_id'] ?? null,
                        'description' => $line['description'] ?? null,
                        'quantity' => $qty,
                        'unit_cost' => $unit,
                        'vat_type' => $vatType,
                        'vat_rate' => $rate,
                        'vat_amount' => $vat,
                        'line_total' => $lineTotal,
                        'expiry_date' => $itemType === 'inventory' ? ($line['expiry_date'] ?? null) : null,
                        'batch_number' => $itemType === 'inventory' ? ($line['batch_number'] ?? null) : null,
                    ]);

                    $subtotal += ($vatType === 'inclusive') ? ($base - $vat) : $base; // net of VAT
                    $vatAmount += $vat;
                    $total += $lineTotal;
                }

                \Log::info('Purchase Invoice Store: Calculating totals', [
                    'subtotal' => $subtotal,
                    'vat_amount' => $vatAmount,
                    'discount' => $discount,
                    'withholding_tax' => $withholdingTax
                ]);

                $invoice->update([
                    'subtotal' => $subtotal,
                    'vat_amount' => $vatAmount,
                    'discount_amount' => $discount,
                    'withholding_tax_amount' => $withholdingTax,
                    'total_amount' => max(0, $subtotal + $vatAmount - $discount - $withholdingTax),
                    'status' => 'open',
                ]);

                \Log::info('Purchase Invoice Store: Invoice totals updated', [
                    'total_amount' => $invoice->total_amount
                ]);

                \Log::info('Purchase Invoice Store: Posting GL transactions');
                // Load items relationship before posting transactions
                $invoice->load('items');
                $invoice->postGlTransactions();
                \Log::info('Purchase Invoice Store: GL transactions posted');
                
                \Log::info('Purchase Invoice Store: Posting inventory movements');
                // Ensure items are loaded (they should be from above, but reload to be safe)
                $invoice->load('items');
                $invoice->postInventoryMovements();
                \Log::info('Purchase Invoice Store: Inventory movements posted');

                // Update linked assets (purchase cost, purchase_date, capitalization_date)
                $invoice->updateAssetPurchases();
                \Log::info('Purchase Invoice Store: Linked assets updated from invoice');
            }

            // Update GRN status if invoice was created from a GRN
            if ($request->filled('grn_id')) {
                $grnIdParam = $request->grn_id;
                if (!is_numeric($grnIdParam)) {
                    $decoded = \Vinkla\Hashids\Facades\Hashids::decode($grnIdParam)[0] ?? null;
                    $grnLookupId = $decoded;
                } else {
                    $grnLookupId = (int) $grnIdParam;
                }
                if ($grnLookupId) {
                    $grn = \App\Models\Purchase\GoodsReceipt::find($grnLookupId);
                    if ($grn) {
                        $grn->update(['status' => 'completed']);
                        \Log::info('Purchase Invoice Store: GRN status updated to completed', ['grn_id' => $grn->id]);
                    }
                }
            }

            DB::commit();
            \Log::info('Purchase Invoice Store: Success', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
            return redirect()->route('purchases.purchase-invoices.show', $invoice->encoded_id)->with('success', 'Purchase invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase Invoice Store: Failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->withInput()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
    }

    public function show(string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        abort_unless($id, 404);
        $purchaseInvoice = PurchaseInvoice::with(['supplier','items.inventoryItem','items.asset','items.asset.category','glTransactions.chartAccount'])->findOrFail($id);

        // Load payments linked to this purchase invoice
        $payments = Payment::with('bankAccount')
            ->where('reference_type', 'purchase_invoice')
            ->where('reference_number', $purchaseInvoice->invoice_number)
            ->where('supplier_id', $purchaseInvoice->supplier_id)
            ->orderByDesc('date')
            ->get();

        $totalPaid = (float) ($payments->sum('amount') ?? 0);
        $balanceDue = max(0, (float) $purchaseInvoice->total_amount - $totalPaid);

        // Get unpaid purchase invoices for this supplier (excluding current invoice)
        $unpaidInvoices = PurchaseInvoice::where('supplier_id', $purchaseInvoice->supplier_id)
            ->whereColumn('total_amount', '>', DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM payments WHERE reference_type = "purchase_invoice" AND reference_number = purchase_invoices.invoice_number AND supplier_id = purchase_invoices.supplier_id)'))
            ->where('id', '!=', $purchaseInvoice->id)
            ->get();

        // Calculate total unpaid amount in functional currency (TZS)
        $functionalCurrency = \App\Models\SystemSetting::getValue('functional_currency', $purchaseInvoice->company->functional_currency ?? 'TZS');
        $totalUnpaidAmountInTZS = $unpaidInvoices->sum(function($unpaidInvoice) use ($functionalCurrency) {
            $invoiceCurrency = $unpaidInvoice->currency ?? $functionalCurrency;
            $exchangeRate = $unpaidInvoice->exchange_rate ?? 1.000000;
            
            // Calculate balance due for unpaid invoice
            $unpaidPayments = Payment::where('reference_type', 'purchase_invoice')
                ->where('reference_number', $unpaidInvoice->invoice_number)
                ->where('supplier_id', $unpaidInvoice->supplier_id)
                ->sum('amount');
            $unpaidBalanceDue = max(0, (float) $unpaidInvoice->total_amount - (float) $unpaidPayments);
            
            // If invoice is in foreign currency, convert to functional currency
            if ($invoiceCurrency !== $functionalCurrency && $exchangeRate != 1.000000) {
                return $unpaidBalanceDue * $exchangeRate;
            }
            
            // Already in functional currency, use as is
            return $unpaidBalanceDue;
        });
        
        // Calculate current invoice balance in functional currency (TZS)
        $invoiceCurrency = $purchaseInvoice->currency ?? $functionalCurrency;
        $invoiceExchangeRate = $purchaseInvoice->exchange_rate ?? 1.000000;
        $currentInvoiceBalanceInTZS = $balanceDue;
        if ($invoiceCurrency !== $functionalCurrency && $invoiceExchangeRate != 1.000000) {
            $currentInvoiceBalanceInTZS = $balanceDue * $invoiceExchangeRate;
        }
        
        // Total supplier balance in functional currency
        $totalSupplierBalanceInTZS = $currentInvoiceBalanceInTZS + $totalUnpaidAmountInTZS;

        return view('purchases.purchase-invoices.show', [
            'invoice' => $purchaseInvoice,
            'payments' => $payments,
            'totalPaid' => $totalPaid,
            'balanceDue' => $balanceDue,
            'unpaidInvoices' => $unpaidInvoices,
            'totalUnpaidAmountInTZS' => $totalUnpaidAmountInTZS,
            'currentInvoiceBalanceInTZS' => $currentInvoiceBalanceInTZS,
            'totalSupplierBalanceInTZS' => $totalSupplierBalanceInTZS,
            'functionalCurrency' => $functionalCurrency,
        ]);
    }

    public function paymentForm(string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        abort_unless($id, 404);
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        $bankAccounts = BankAccount::orderBy('name')->get(['id','name']);
        
        // Get currencies from FX RATES MANAGEMENT
        $currencies = $this->getCurrenciesFromFxRates();
        
        return view('purchases.purchase-invoices.payment', [
            'invoice' => $purchaseInvoice,
            'bankAccounts' => $bankAccounts,
            'currencies' => $currencies,
        ]);
    }

    public function recordPayment(Request $request, string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        abort_unless($id, 404);
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'method' => 'required|in:cash,bank',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $branchId = session('branch_id') ?? (Auth::user()->branch_id ?? null);
            if (!$branchId) {
                return back()->withInput()->withErrors(['error' => 'Please select a branch before recording a payment.']);
            }
            if ($request->method === 'bank' && !$request->bank_account_id) {
                return back()->withInput()->withErrors(['bank_account_id' => 'Please select a bank account for bank payments.']);
            }

            // Calculate WHT if provided (with VAT integration)
            $whtService = new \App\Services\WithholdingTaxService();
            $totalAmount = (float) $request->amount;
            $whtTreatment = $request->wht_treatment ?? 'EXCLUSIVE';
            $whtRate = (float) ($request->wht_rate ?? 0);
            
            // Get VAT mode and rate from invoice (set when invoice was created)
            $vatMode = $purchaseInvoice->getVatMode();
            $vatRate = $purchaseInvoice->getVatRate();
            
            // Check if supplier has allow_gross_up flag
            $supplier = $purchaseInvoice->supplier;
            if ($supplier && $supplier->allow_gross_up && $whtTreatment === 'EXCLUSIVE' && $whtRate > 0) {
                $whtTreatment = 'GROSS_UP';
            }
            
            $paymentWHT = 0;
            $paymentNetPayable = $totalAmount;
            $paymentTotalCost = $totalAmount;
            $paymentBaseAmount = $totalAmount;
            $paymentVatAmount = 0;
            
            if ($whtRate > 0 && $whtTreatment !== 'NONE') {
                $whtCalc = $whtService->calculateWHT($totalAmount, $whtRate, $whtTreatment, $vatMode, $vatRate);
                $paymentWHT = $whtCalc['wht_amount'];
                $paymentNetPayable = $whtCalc['net_payable'];
                $paymentTotalCost = $whtCalc['total_cost'];
                $paymentBaseAmount = $whtCalc['base_amount'];
                $paymentVatAmount = $whtCalc['vat_amount'];
            } elseif ($vatMode !== 'NONE' && $vatRate > 0) {
                // Calculate VAT even if no WHT
                if ($vatMode === 'INCLUSIVE') {
                    $paymentBaseAmount = round($totalAmount / (1 + ($vatRate / 100)), 2);
                    $paymentVatAmount = round($totalAmount - $paymentBaseAmount, 2);
                } else {
                    // EXCLUSIVE
                    $paymentBaseAmount = round($totalAmount / (1 + ($vatRate / 100)), 2);
                    $paymentVatAmount = round($totalAmount - $paymentBaseAmount, 2);
                }
            }

            // Get bank account for payment
            $bankAccountId = $request->method === 'bank' ? $request->bank_account_id : null;
            if ($request->method === 'bank' && !$bankAccountId) {
                DB::rollBack();
                return back()->withInput()->withErrors(['error' => 'Bank account is required for bank payments.']);
            }

            // For cash payments, we need to find or create a cash account
            if ($request->method === 'cash') {
                $cashAccountId = (int) (SystemSetting::where('key','inventory_default_cash_account')->value('value')
                    ?? (ChartAccount::where('account_name','Cash on Hand')->value('id') ?? 0));
                if (!$cashAccountId) {
                    DB::rollBack();
                    return back()->withInput()->withErrors(['error' => 'Cash account is not configured. Please set default cash account in Inventory Settings.']);
                }
                // Create a temporary bank account reference for cash (or use a cash bank account)
                // For now, we'll use the cash account directly in GL transactions
            }

            // Get functional currency and invoice currency
            $functionalCurrency = SystemSetting::getValue('functional_currency', $purchaseInvoice->company->functional_currency ?? 'TZS');
            $invoiceCurrency = $purchaseInvoice->currency ?? $functionalCurrency;
            
            // Get payment exchange rate using FxTransactionRateService (use payment date, not invoice date)
            $fxTransactionRateService = app(FxTransactionRateService::class);
            $userProvidedRate = $request->filled('payment_exchange_rate') ? (float) $request->payment_exchange_rate : null;
            $rateResult = $fxTransactionRateService->getTransactionRate(
                $invoiceCurrency,
                $functionalCurrency,
                $request->date, // Use payment date, not invoice date
                $purchaseInvoice->company_id,
                $userProvidedRate
            );
            $paymentExchangeRate = $rateResult['rate'];
            
            // Calculate amounts in local currency for payment record
            $needsConversion = ($invoiceCurrency !== $functionalCurrency && $paymentExchangeRate != 1.000000);
            $amountLCY = $needsConversion ? round($totalAmount * $paymentExchangeRate, 2) : $totalAmount;

            // Save payment record
            $payment = Payment::create([
                'reference' => 'PINV-PMT-' . now()->format('YmdHis'),
                'reference_type' => 'purchase_invoice',
                'reference_number' => $purchaseInvoice->invoice_number,
                'amount' => $totalAmount, // Total amount in invoice currency (may include VAT)
                'currency' => $invoiceCurrency,
                'exchange_rate' => $paymentExchangeRate, // Payment exchange rate
                'amount_fcy' => $totalAmount, // Foreign currency amount
                'amount_lcy' => $amountLCY, // Local currency amount
                'wht_treatment' => $whtTreatment,
                'wht_rate' => $whtRate,
                'wht_amount' => $paymentWHT,
                'net_payable' => $paymentNetPayable,
                'total_cost' => $paymentTotalCost,
                'vat_mode' => $vatMode,
                'vat_amount' => $paymentVatAmount,
                'base_amount' => $paymentBaseAmount,
                'date' => $request->date,
                'description' => $request->description ?? 'Payment for Purchase Invoice ' . $purchaseInvoice->invoice_number,
                'bank_account_id' => $bankAccountId,
                'supplier_id' => $purchaseInvoice->supplier_id,
                'branch_id' => $branchId,
                'user_id' => Auth::id(),
                'approved' => true,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Create payment item for the invoice payment
            $apAccountId = (int) (SystemSetting::where('key', 'inventory_default_purchase_payable_account')->value('value') ?? 30);
            PaymentItem::create([
                'payment_id' => $payment->id,
                'chart_account_id' => $apAccountId,
                'amount' => $totalAmount,
                'wht_treatment' => $whtTreatment,
                'wht_rate' => $whtRate,
                'wht_amount' => $paymentWHT,
                'base_amount' => $paymentBaseAmount,
                'net_payable' => $paymentNetPayable,
                'total_cost' => $paymentTotalCost,
                'vat_mode' => $vatMode,
                'vat_amount' => $paymentVatAmount,
                'description' => 'Payment for Purchase Invoice ' . $purchaseInvoice->invoice_number,
            ]);

            // Use Payment model's createGlTransactions method which handles WHT correctly
            $payment->createGlTransactions();

            // Refresh invoice status based on total paid
            $this->refreshInvoiceStatus($purchaseInvoice);
            DB::commit();
            return redirect()->route('purchases.purchase-invoices.show', $purchaseInvoice->encoded_id)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();
            
            // Check if it's a reconciliation error
            if (str_contains($errorMessage, 'completed reconciliation period')) {
                $errorMessage = 'Cannot post: Payment is in a completed reconciliation period';
            } else {
                $errorMessage = 'Failed to record payment: ' . $errorMessage;
            }
            
            return back()->withInput()->with('error', $errorMessage);
        }
    }

    public function exportPdf(string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        abort_unless($id, 404);
        $purchaseInvoice = PurchaseInvoice::with(['supplier','items.inventoryItem','company','branch'])->findOrFail($id);

        // Load payments linked to this purchase invoice
        $payments = Payment::where('reference_type', 'purchase_invoice')
            ->where('reference_number', $purchaseInvoice->invoice_number)
            ->where('supplier_id', $purchaseInvoice->supplier_id)
            ->orderByDesc('date')
            ->get();

        $totalPaid = (float) ($payments->sum('amount') ?? 0);
        $balanceDue = max(0, (float) $purchaseInvoice->total_amount - $totalPaid);

        $html = view('purchases.purchase-invoices.pdf', [
            'invoice' => $purchaseInvoice,
            'payments' => $payments,
            'totalPaid' => $totalPaid,
            'balanceDue' => $balanceDue,
        ])->render();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('A4');
        $filename = 'PurchaseInvoice_' . $purchaseInvoice->invoice_number . '.pdf';
        return $pdf->download($filename);
    }

    public function edit(string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        abort_unless($id, 404);
        $purchaseInvoice = PurchaseInvoice::with('items')->findOrFail($id);
        // Block editing if payments already exist against this invoice
        $hasPayments = \App\Models\Payment::where('reference_type','purchase_invoice')
            ->where('reference_number', $purchaseInvoice->invoice_number)
            ->where('supplier_id', $purchaseInvoice->supplier_id)
            ->exists();
        if ($hasPayments) {
            return redirect()->route('purchases.purchase-invoices.show', $purchaseInvoice->encoded_id)
                ->withErrors(['error' => 'This invoice has payments and cannot be edited.']);
        }
        $suppliers = Supplier::where('company_id', Auth::user()->company_id)
            ->when(Auth::user()->branch_id, function($q){ $q->where('branch_id', Auth::user()->branch_id); })
            ->orderBy('name')
            ->get();
        $items = InventoryItem::where('company_id', Auth::user()->company_id)->orderBy('name')->get();
        $user = Auth::user();
        $branchId = session('branch_id') ?? $user->branch_id ?? null;
        $assets = Asset::where('company_id', $user->company_id)
            ->when($branchId, function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->orderBy('name')
            ->get();
        
        // Get currencies from FX RATES MANAGEMENT
        $currencies = $this->getCurrenciesFromFxRates();
        
        return view('purchases.purchase-invoices.edit', [
            'invoice' => $purchaseInvoice,
            'suppliers' => $suppliers,
            'items' => $items,
            'currencies' => $currencies,
            'assets' => $assets,
        ]);
    }

    public function update(Request $request, string $encodedId)
    {
        \Log::info('Purchase Invoice Update: Starting', [
            'user_id' => auth()->id(),
            'company_id' => auth()->user()->company_id,
            'branch_id' => auth()->user()->branch_id,
            'encoded_id' => $encodedId,
            'request_data' => $request->all()
        ]);

        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        abort_unless($id, 404);
        $purchaseInvoice = PurchaseInvoice::findOrFail($id);
        // Block update if payments already exist against this invoice
        $hasPayments = \App\Models\Payment::where('reference_type','purchase_invoice')
            ->where('reference_number', $purchaseInvoice->invoice_number)
            ->where('supplier_id', $purchaseInvoice->supplier_id)
            ->exists();
        if ($hasPayments) {
            return back()->withInput()->withErrors(['error' => 'This invoice has payments and cannot be edited.']);
        }
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:100|unique:purchase_invoices,invoice_number,' . $purchaseInvoice->id,
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'currency' => 'nullable|string|max:3',
            'exchange_rate' => 'nullable|numeric|min:0.000001',
            'discount_amount' => 'nullable|numeric|min:0',
            'withholding_tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
            'items.*.item_name' => 'nullable|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.vat_type' => 'required|in:no_vat,inclusive,exclusive',
            'items.*.vat_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $purchaseInvoice->update([
                'supplier_id' => $request->supplier_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'currency' => $request->currency ?? $purchaseInvoice->currency,
                'exchange_rate' => $request->exchange_rate ?? $purchaseInvoice->exchange_rate,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            $itemsCount = count($request->items);
            $discount = (float) ($request->input('discount_amount', 0) ?? 0);
            $withholdingTax = (float) ($request->input('withholding_tax_amount', 0) ?? 0);

            // Use job for large batches to avoid timeout and max_input_vars issues
            // Threshold can be adjusted: lower = more items processed synchronously, higher = more use async jobs
            $jobThreshold = config('queue.purchase_invoice_job_threshold', 50);
            $useJob = $itemsCount >= $jobThreshold;

            if ($useJob) {
                \Log::info('Purchase Invoice Update: Using job for large batch', [
                    'invoice_id' => $purchaseInvoice->id,
                    'items_count' => $itemsCount
                ]);

                // Delete existing items (job will recreate them)
                $purchaseInvoice->items()->delete();

                // Set temporary totals (will be updated by job)
                $purchaseInvoice->update([
                    'subtotal' => 0,
                    'vat_amount' => 0,
                    'discount_amount' => $discount,
                    'withholding_tax_amount' => $withholdingTax,
                    'total_amount' => 0,
                ]);

                // Dispatch job to process items asynchronously
                ProcessPurchaseInvoiceItemsJob::dispatch(
                    $purchaseInvoice->id,
                    $request->items,
                    $discount,
                    $withholdingTax
                );

                \Log::info('Purchase Invoice Update: Job dispatched', [
                    'invoice_id' => $purchaseInvoice->id
                ]);

                DB::commit();

                return redirect()->route('purchases.purchase-invoices.show', $purchaseInvoice->encoded_id)
                    ->with('success', 'Purchase invoice updated successfully. Items are being processed in the background. Please refresh the page in a few moments.');
            } else {
                // Process items synchronously for smaller batches
                \Log::info('Purchase Invoice Update: Processing items synchronously', [
                    'items_count' => $itemsCount
                ]);

                // Replace items
                $purchaseInvoice->items()->delete();

                $subtotal = 0; $vatAmount = 0; $total = 0;
                foreach ($request->items as $line) {
                    $qty = (float) $line['quantity'];
                    $unit = (float) $line['unit_cost'];
                    $base = $qty * $unit;
                    $vat = 0;
                    $vatType = $line['vat_type'];
                    $rate = (float) ($line['vat_rate'] ?? 0);
                    if ($vatType === 'inclusive' && $rate > 0) {
                        $vat = $base * ($rate / (100 + $rate));
                    } elseif ($vatType === 'exclusive' && $rate > 0) {
                        $vat = $base * ($rate / 100);
                    }
                    $lineTotal = $vatType === 'exclusive' ? $base + $vat : $base;

                    PurchaseInvoiceItem::create([
                        'purchase_invoice_id' => $purchaseInvoice->id,
                        'inventory_item_id' => $line['inventory_item_id'] ?? null,
                        'grn_item_id' => $line['grn_item_id'] ?? null,
                        'description' => $line['description'] ?? null,
                        'quantity' => $qty,
                        'unit_cost' => $unit,
                        'vat_type' => $vatType,
                        'vat_rate' => $rate,
                        'vat_amount' => $vat,
                        'line_total' => $lineTotal,
                        'expiry_date' => $line['expiry_date'] ?? null,
                        'batch_number' => $line['batch_number'] ?? null,
                    ]);

                    $subtotal += ($vatType === 'inclusive') ? ($base - $vat) : $base; // net of VAT
                    $vatAmount += $vat;
                    $total += $lineTotal;
                }

                $purchaseInvoice->update([
                    'subtotal' => $subtotal,
                    'vat_amount' => $vatAmount,
                    'discount_amount' => $discount,
                    'withholding_tax_amount' => $withholdingTax,
                    'total_amount' => max(0, $subtotal + $vatAmount - $discount - $withholdingTax),
                ]);

                // Repost GL and inventory
                $purchaseInvoice->refresh();
                $purchaseInvoice->postGlTransactions();
                $purchaseInvoice->postInventoryMovements();

                // Update linked assets (purchase cost, purchase_date, capitalization_date)
                $purchaseInvoice->updateAssetPurchases();
            }

            DB::commit();
            return redirect()->route('purchases.purchase-invoices.show', $purchaseInvoice->encoded_id)->with('success', 'Purchase invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()]);
        }
    }

    public function destroy(string $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        abort_unless($id, 404);
        $invoice = PurchaseInvoice::findOrFail($id);
        // Block delete if payments already exist against this invoice
        $hasPayments = \App\Models\Payment::where('reference_type','purchase_invoice')
            ->where('reference_number', $invoice->invoice_number)
            ->where('supplier_id', $invoice->supplier_id)
            ->exists();
        if ($hasPayments) {
            return response()->json(['success' => false, 'message' => 'Cannot delete: payments exist for this invoice'], 422);
        }
        DB::beginTransaction();
        try {
            // Delete inventory movements for this purchase invoice (this handles stock reversal)
            \App\Models\Inventory\Movement::where('reference_type', 'purchase_invoice')
                ->where('reference_id', $invoice->id)
                ->delete();
            
            // Delete GL rows
            \App\Models\GlTransaction::where('transaction_type','purchase_invoice')->where('transaction_id',$invoice->id)->delete();
            
            // Delete items then invoice
            $invoice->items()->delete();
            $invoice->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Purchase invoice deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete: '.$e->getMessage()], 500);
        }
    }

    public function destroyPayment(string $encodedId, string $paymentEncodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        $paymentId = HashIdHelper::decode($paymentEncodedId);
        abort_unless($id && $paymentId, 404);
        $invoice = PurchaseInvoice::findOrFail($id);
        DB::beginTransaction();
        try {
            $p = Payment::where('id', $paymentId)
                ->where('reference_type', 'purchase_invoice')
                ->where('reference_number', $invoice->invoice_number)
                ->firstOrFail();

            // Delete GL rows created by this payment.
            // Payment::createGlTransactions() uses:
            //   transaction_type = 'payment'
            //   transaction_id   = payment id
            GlTransaction::where('transaction_type', 'payment')
                ->where('transaction_id', $p->id)
                ->delete();

            // Now delete the payment itself
            $p->delete();
            $this->refreshInvoiceStatus($invoice);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function editPayment(string $encodedId, string $paymentEncodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        $paymentId = HashIdHelper::decode($paymentEncodedId);
        abort_unless($id && $paymentId, 404);
        $invoice = PurchaseInvoice::findOrFail($id);
        $p = Payment::where('id', $paymentId)
            ->where('reference_type','purchase_invoice')
            ->where('reference_number', $invoice->invoice_number)
            ->firstOrFail();
        $bankAccounts = BankAccount::orderBy('name')->get(['id','name']);
        return view('purchases.purchase-invoices/payment-edit', compact('invoice','p','bankAccounts'));
    }

    public function updatePayment(Request $request, string $encodedId, string $paymentEncodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        $paymentId = HashIdHelper::decode($paymentEncodedId);
        abort_unless($id && $paymentId, 404);
        $invoice = PurchaseInvoice::findOrFail($id);
        $p = Payment::where('id', $paymentId)
            ->where('reference_type','purchase_invoice')
            ->where('reference_number', $invoice->invoice_number)
            ->firstOrFail();
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'method' => 'required|in:cash,bank',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'description' => 'nullable|string|max:255',
        ]);
        DB::beginTransaction();
        try {
            $branchId = session('branch_id') ?? (Auth::user()->branch_id ?? null);
            if (!$branchId) {
                return back()->withInput()->withErrors(['error' => 'Please select a branch before updating a payment.']);
            }
            // Delete old GL transactions
            $p->glTransactions()->delete();
            
            // Get VAT mode and rate from invoice (set when invoice was created)
            $vatMode = $invoice->getVatMode();
            $vatRate = $invoice->getVatRate();

            // Calculate WHT if applicable (only for bank payments)
            $whtService = new \App\Services\WithholdingTaxService();
            $whtTreatment = $request->method === 'bank' ? ($request->wht_treatment ?? 'EXCLUSIVE') : 'EXCLUSIVE';
            $whtRate = $request->method === 'bank' ? ((float) ($request->wht_rate ?? 0)) : 0;
            
            $whtAmount = 0;
            $whtResult = null;
            $vatAmount = 0;
            $baseAmount = $request->amount;
            $netPayable = $request->amount;
            $totalCost = $request->amount;
            
            if ($request->method === 'bank' && $whtTreatment !== 'NONE' && $whtRate > 0) {
                $whtResult = $whtService->calculateWHT(
                    (float) $request->amount,
                    $whtRate,
                    $whtTreatment,
                    $vatMode,
                    $vatRate
                );
                $whtAmount = $whtResult['wht_amount'];
                $vatAmount = $whtResult['vat_amount'] ?? 0;
                $baseAmount = $whtResult['base_amount'] ?? $request->amount;
                $netPayable = $whtResult['net_payable'] ?? $request->amount;
                $totalCost = $whtResult['total_cost'] ?? $request->amount;
            } elseif ($request->method === 'bank') {
                // Calculate VAT amount even if no WHT
                if ($vatMode === 'INCLUSIVE' && $vatRate > 0) {
                    $baseAmount = round($request->amount / (1 + ($vatRate / 100)), 2);
                    $vatAmount = round($request->amount - $baseAmount, 2);
                    $netPayable = $request->amount; // Net payable is the full amount when VAT is inclusive
                } elseif ($vatMode === 'EXCLUSIVE' && $vatRate > 0) {
                    $baseAmount = $request->amount;
                    $vatAmount = round($request->amount * ($vatRate / 100), 2);
                    $netPayable = $request->amount; // Net payable is the full amount
                } else {
                    $netPayable = $request->amount;
                }
            }

            // Get payment exchange rate (use provided rate or invoice rate)
            $paymentExchangeRate = $request->filled('payment_exchange_rate') 
                ? (float) $request->payment_exchange_rate 
                : ($invoice->exchange_rate ?? 1.000000);
            
            // Get invoice currency and functional currency
            $functionalCurrency = SystemSetting::getValue('functional_currency', $invoice->company->functional_currency ?? 'TZS');
            $invoiceCurrency = $invoice->currency ?? $functionalCurrency;
            
            // Calculate amounts in local currency for payment record
            $needsConversion = ($invoiceCurrency !== $functionalCurrency && $paymentExchangeRate != 1.000000);
            $amountLCY = $needsConversion ? round($request->amount * $paymentExchangeRate, 2) : $request->amount;

            // Update payment record
            $p->update([
                'amount' => $request->amount,
                'currency' => $invoiceCurrency,
                'exchange_rate' => $paymentExchangeRate, // Payment exchange rate
                'amount_fcy' => $request->amount, // Foreign currency amount
                'amount_lcy' => $amountLCY, // Local currency amount
                'date' => $request->date,
                'description' => $request->description,
                'bank_account_id' => $request->method === 'bank' ? $request->bank_account_id : null,
                'wht_treatment' => $request->method === 'bank' ? $whtTreatment : null,
                'wht_rate' => $request->method === 'bank' ? $whtRate : 0,
                'wht_amount' => $request->method === 'bank' ? $whtAmount : 0,
                'vat_mode' => $request->method === 'bank' ? $vatMode : null,
                'vat_amount' => $request->method === 'bank' ? $vatAmount : 0,
                'base_amount' => $request->method === 'bank' ? $baseAmount : $request->amount,
                'net_payable' => $request->method === 'bank' ? $netPayable : $request->amount,
                'total_cost' => $request->method === 'bank' ? $totalCost : $request->amount,
            ]);

            // Update payment item if it exists
            $paymentItem = PaymentItem::where('payment_id', $p->id)->first();
            if ($paymentItem) {
                $paymentItem->update([
                    'amount' => $request->amount,
                    'wht_treatment' => $request->method === 'bank' ? ($request->wht_treatment ?? 'EXCLUSIVE') : null,
                    'wht_rate' => $request->method === 'bank' ? ($request->wht_rate ?? 0) : 0,
                    'wht_amount' => $request->method === 'bank' ? $whtAmount : 0,
                    'base_amount' => $request->method === 'bank' ? $baseAmount : $request->amount,
                    'net_payable' => $request->method === 'bank' ? $netPayable : $request->amount,
                    'total_cost' => $request->method === 'bank' ? $totalCost : $request->amount,
                    'vat_mode' => $request->method === 'bank' ? $vatMode : null,
                    'vat_amount' => $request->method === 'bank' ? $vatAmount : 0,
                ]);
            }
            // Refresh payment model to ensure we have latest data before creating GL transactions
            $p->refresh();
            $p->load('paymentItems'); // Ensure payment items are loaded
            
            // Use Payment model's createGlTransactions method which handles WHT and FX gain/loss correctly
            $p->createGlTransactions();
            
            // Refresh invoice status after payment change
            $this->refreshInvoiceStatus($invoice);
            DB::commit();
            return redirect()->route('purchases.purchase-invoices.show', $invoice->encoded_id)->with('success','Payment updated');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update payment: '.$e->getMessage()]);
        }
    }

    public function printPaymentReceipt(string $encodedId, string $paymentEncodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        $paymentId = \App\Helpers\HashIdHelper::decode($paymentEncodedId);
        abort_unless($id && $paymentId, 404);
        $invoice = PurchaseInvoice::with('supplier')->findOrFail($id);
        $p = Payment::with('bankAccount')->where('id', $paymentId)
            ->where('reference_type','purchase_invoice')
            ->where('reference_number', $invoice->invoice_number)
            ->firstOrFail();
        return view('purchases.purchase-invoices/receipt', compact('invoice','p'));
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'PINV-';
        $datePart = now()->format('Ymd');
        $last = PurchaseInvoice::whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();
        $seq = 1;
        if ($last && preg_match('/^(PINV-\d{8})-(\d{4})$/', (string) $last->invoice_number, $m) && $m[1] === ($prefix . $datePart)) {
            $seq = (int) $m[2] + 1;
        }
        return $prefix . $datePart . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate asset code based on category and settings
     */
    private function generateAssetCode(AssetCategory $category, $branchId): string
    {
        $format = SystemSetting::where('key', 'asset_code_format')->value('value') ?? 'AST-{YYYY}-{SEQ}';
        $year = now()->format('Y');
        $seq = Asset::where('company_id', Auth::user()->company_id)
            ->whereYear('created_at', $year)
            ->count() + 1;
        
        $code = str_replace(['{YYYY}', '{SEQ}', '{CAT}'], [$year, str_pad((string)$seq, 4, '0', STR_PAD_LEFT), $category->code ?? ''], $format);
        
        // Ensure uniqueness
        $baseCode = $code;
        $counter = 1;
        while (Asset::where('code', $code)->exists()) {
            $code = $baseCode . '-' . $counter;
            $counter++;
        }
        
        return $code;
    }

    /**
     * Send purchase invoice via email
     */
    public function sendEmail(Request $request, string $encodedId): JsonResponse
    {
        try {
            $invoiceId = Hashids::decode($encodedId);
            if (empty($invoiceId)) {
                return response()->json(['success' => false, 'message' => 'Invalid invoice ID'], 400);
            }

            $invoice = PurchaseInvoice::with(['supplier', 'items'])->find($invoiceId[0]);
            if (!$invoice) {
                return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'subject' => 'nullable|string|max:255',
                'message' => 'nullable|string',
                'email' => 'nullable|email',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            // Use provided email or supplier email
            $email = $request->email ?? $invoice->supplier->email ?? null;
            if (!$email) {
                return response()->json(['success' => false, 'message' => 'No email address available for supplier'], 400);
            }

            // Send email
            Mail::to($email)->send(new PurchaseInvoiceMail(
                $invoice,
                $request->subject,
                $request->message
            ));

            // Update invoice status to sent if it was draft
            if ($invoice->status === 'draft') {
                $invoice->update(['status' => 'open']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase invoice email sent successfully to ' . $email
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending purchase invoice email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the email: ' . $e->getMessage()
            ], 500);
        }
    }
}
