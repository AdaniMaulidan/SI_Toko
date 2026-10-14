<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Tampilkan riwayat transaksi penjualan dengan filter lengkap (FASE 4.5)
     */
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'customer', 'items.product', 'debt']);

        // 1. Filter Kata Kunci (No. Invoice, Catatan, Nama Pelanggan)
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('invoice_number', 'like', "%{$keyword}%")
                  ->orWhere('notes', 'like', "%{$keyword}%")
                  ->orWhereHas('customer', function ($cq) use ($keyword) {
                      $cq->where('name', 'like', "%{$keyword}%")
                         ->orWhere('phone', 'like', "%{$keyword}%");
                  });
            });
        }

        // 2. Filter Rentang Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 3. Filter Status Pembayaran (paid / debt)
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 4. Filter Metode Pembayaran (cash, qris, transfer, debt)
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // 5. Filter Kasir
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Hitung Ringkasan Statistik Berdasarkan Filter Aktif
        $stats = [
            'total_sales'        => (float) (clone $query)->sum('total_amount'),
            'total_transactions' => (clone $query)->count(),
            'total_paid'         => (float) (clone $query)->sum('paid_amount'),
            'total_debt'         => (float) ((clone $query)->where('payment_status', 'debt')->sum('total_amount') - (clone $query)->where('payment_status', 'debt')->sum('paid_amount')),
        ];

        // Pagination
        $sales = $query->latest('id')->paginate(15)->withQueryString();

        // Data Kasir untuk Dropdown Filter
        $cashiers = User::orderBy('name')->get();

        return view('transactions.index', compact('sales', 'cashiers', 'stats'));
    }

    /**
     * Tampilkan halaman detail lengkap transaksi
     */
    public function show($id)
    {
        $sale = Sale::with(['user', 'customer', 'items.product', 'debt.payments'])->findOrFail($id);

        return view('transactions.show', compact('sale'));
    }

    /**
     * Endpoint API untuk memuat data modal detail transaksi (FASE 4.5)
     */
    public function detail($id)
    {
        $sale = Sale::with(['user', 'customer', 'items.product.unit', 'debt'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'sale'    => [
                'id'             => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'created_at'     => $sale->created_at->format('d/m/Y H:i'),
                'cashier_name'   => $sale->user?->name ?? 'Kasir',
                'customer_name'  => $sale->customer?->name ?? 'Pelanggan Umum',
                'customer_phone' => $sale->customer?->phone ?? '-',
                'payment_method' => $sale->payment_method,
                'payment_status' => $sale->payment_status,
                'subtotal'       => (float) $sale->subtotal,
                'discount'       => (float) $sale->discount,
                'total_amount'   => (float) $sale->total_amount,
                'paid_amount'    => (float) $sale->paid_amount,
                'change_amount'  => (float) $sale->change_amount,
                'notes'          => $sale->notes,
                'debt'           => $sale->debt ? [
                    'remaining_debt' => (float) $sale->debt->remaining_debt,
                    'due_date'       => $sale->debt->due_date ? $sale->debt->due_date->format('d/m/Y') : '-',
                    'status'         => $sale->debt->status,
                ] : null,
                'items'          => $sale->items->map(function ($it) {
                    $itemGross = (float) ($it->selling_price * $it->quantity);
                    $disc = max(0, $itemGross - (float) $it->subtotal);

                    return [
                        'product_name'  => $it->product?->name ?? 'Barang',
                        'barcode'       => $it->product?->barcode ?? '-',
                        'unit'          => $it->product?->unit?->symbol ?? 'Pcs',
                        'selling_price' => (float) $it->selling_price,
                        'quantity'      => (int) $it->quantity,
                        'subtotal'      => (float) $it->subtotal,
                        'item_discount' => $disc,
                    ];
                }),
            ]
        ]);
    }
}
